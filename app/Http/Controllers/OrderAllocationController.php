<?php

namespace App\Http\Controllers;

use App\Models\OrderAllocation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\OrderFinal;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use App\Models\EmailBody as EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\AllocationConfirmation;
use Illuminate\Support\Facades\Auth;

class OrderAllocationController extends Controller
{


    public function index()
    {


        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        $orderList = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->orderBy('allocation_ID', 'DESC')
            ->get();

        $totOrderQuant = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->sum('order_quantity') ?? 0;

        $totOrderQuant_Simple = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->where('given_by_invntry', 0)
            ->where('given_by_onway', 0)
            ->count() ?? 0;

        $totOrderQuant_Inventory = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->where('given_by_invntry', '>', 0)
            ->count() ?? 0;

        $totOrderQuant_OnWay = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->where('given_by_onway', '>', 0)
            ->count() ?? 0;

        $totOrderQuant_OnWay_Monsini = OrderAllocation::where('order_status', '!=', 'Allocated')
            ->where('order_customer_name', $ownerComp)
            ->sum('order_quantity') ?? 0;
            // ->count();

        return view('order-allocations.index', compact(
            'orderList',
            'ownerComp',
            'totOrderQuant',
            'totOrderQuant_Simple',
            'totOrderQuant_Inventory',
            'totOrderQuant_OnWay',
            'totOrderQuant_OnWay_Monsini'
        ));
    }

    public function confirmToCustomer(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:dt_order_allocation,order_ID'
        ]);

        $orderIDs = $request->input('orders');
        $orderIDsChecked = implode(',', $orderIDs);

        DB::transaction(function () use ($orderIDs, $orderIDsChecked) {
            // Get pending orders from allocation table
            $pendingOrders = OrderAllocation::selectRaw('GROUP_CONCAT(order_ID) as order_ids')
                ->where('order_status', 'Pending')
                ->whereIn('order_ID', $orderIDs)
                ->first();

            if (!$pendingOrders || empty($pendingOrders->order_ids)) {
                return redirect()->route('order-allocations.index')
                    ->with('error', 'No pending orders found for allocation');
            }

            // Group orders by customer
            $customerOrders = Order::selectRaw('order_customer_ID, GROUP_CONCAT(order_ID) as orders')
                ->whereIn('order_ID', explode(',', $pendingOrders->order_ids))
                ->groupBy('order_customer_ID')
                ->get();

            // Get email template
            $emailTemplate = EmailTemplate::where('email_role', 'customer')->first();

            foreach ($customerOrders as $customerOrder) {
                $customer = Customer::find($customerOrder->order_customer_ID);
                $orders = Order::whereIn('order_ID', explode(',', $customerOrder->orders))->get();

                // Get vendor messages
                $vendorIds = $orders->pluck('order_vendor_ID')->unique();
                $vendors = Vendor::whereIn('vendor_ID', $vendorIds)->get()->keyBy('vendor_ID');
                $vendorMessages = $vendors->map(function ($vendor) {
                    return $vendor->message;
                });

                $totalCost = $orders->sum('order_purchase_price');

                // Send email
                Mail::to($customer->cust_email)
                    ->cc(config('mail.from.address'))
                    ->send(new AllocationConfirmation(
                        $emailTemplate->email_body,
                        $orders,
                        $vendorMessages,
                        $totalCost
                    ));

                // Update order statuses
                Order::whereIn('order_ID', explode(',', $customerOrder->orders))
                    ->update(['order_status' => 'Confirmed']);

                OrderAllocation::whereIn('order_ID', explode(',', $customerOrder->orders))
                    ->update(['order_status' => 'Confirmed to Customer']);
            }
        });

        return redirect()->route('order-allocations.index')
            ->with('success', 'Orders confirmed and customers notified');
    }


    public function bulkAllocate(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:dt_order_allocation,order_ID'
        ]);


        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $ownerCount = OrderAllocation::whereIn('order_ID', $request->orders)
            ->whereRaw('LOWER(order_customer_name) = ?', [strtolower($ownerComp)])
            ->count();

        try {
            // $ordersToAllocate = OrderAllocation::whereIn('order_ID', $request->orders)->get();
            
            $ordersToAllocate = OrderAllocation::whereIn('order_ID', $request->orders)
                ->where('order_status', '!=', 'Allocated')  // ADD THIS
                ->get();
            
            foreach ($ordersToAllocate as $order) {
                // $order->created_at = now();
                // OrderHistory::create($order->toArray());
                
                $historyData = $order->toArray();
                $historyData['created_at'] = now();
            
                $historyData['created_at_final'] = 
                    (empty($historyData['created_at_final']) || $historyData['created_at_final'] === '0000-00-00 00:00:00') 
                    ? null : $historyData['created_at_final'];
            
                $historyData['created_at_allocation'] = 
                    (empty($historyData['created_at_allocation']) || $historyData['created_at_allocation'] === '0000-00-00 00:00:00') 
                    ? null : $historyData['created_at_allocation'];
            
                OrderHistory::create($historyData);
            }




            if ($ownerCount > 0) {
                $ownerIDs = OrderAllocation::whereIn('order_ID', $request->orders)
                    ->whereRaw('LOWER(order_customer_name) = ?', [strtolower($ownerComp)])
                    ->pluck('order_ID');




                foreach ($ownerIDs as $ownerID) {
                    $dataInfo = OrderAllocation::where("order_ID", $ownerID)->first();


                    $countExistingInventory = Inventory::where('product_style', $dataInfo->order_product_style)
                        ->where('product_color', $dataInfo->order_product_color)
                        ->where('product_size', $dataInfo->order_product_size)
                        ->count();

                    $product = Product::find($dataInfo->order_product_ID);

                    if ($countExistingInventory > 0) {
                        Inventory::where('product_style', $dataInfo->order_product_style)
                            ->where('product_color', $dataInfo->order_product_color)
                            ->where('product_size', $dataInfo->order_product_size)
                            ->increment('product_quantity', $dataInfo->order_quantity);
                    } else {
                        $product = Product::find($dataInfo->order_product_ID);

                        if ($product) {

                            Inventory::create([
                                'product_ID' => $dataInfo->order_product_ID,
                                'product_style' => $dataInfo->order_product_style,
                                'product_color' => $dataInfo->order_product_color,
                                'product_size' => $dataInfo->order_product_size,
                                'product_cost' => $product->product_cost,
                                'product_wholesale_price' => $product->product_wholesale_price,
                                'product_vendor_ID' => $dataInfo->order_vendor_ID,
                                'product_vendor_name' => $dataInfo->order_vendor_name,
                                'product_link' => $product->product_link,
                                'product_image' => $product->product_image,
                                'product_quantity' => $dataInfo->order_quantity
                            ]);
                        }
                    }
                }
            }

            OrderAllocation::whereIn('order_ID', $request->orders)
                ->update([
                    'order_status' => 'Allocated',
                    'order_quantity' => 0
                ]);

            Order::whereIn('order_ID', $request->orders)
                ->update(['order_status' => 'Allocated']);
        } catch (\Exception $e) {
            return redirect()->route('order-allocations.index')
                ->with('error', 'Error during bulk allocation: ' . $e->getMessage());
        }

        return redirect()->route('order-allocations.index')
            ->with('success', 'Orders bulk allocated successfully');
    }

    public function bulkStage(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:dt_order_allocation,order_ID'
        ]);

        OrderAllocation::whereIn('order_ID', $request->orders)
            ->update([
                'staging_flag' => 'Yes',
                'staging_date' => now()->format('Y-m-d')
            ]);

        return redirect()->route('order-allocations.index')
            ->with('success', 'Orders marked as staged successfully');
    }


    public function toggleStaging($id)
    {
        // Check if user is authenticated and not customer
        if (auth()->guest() || auth()->user()->admin_role === 'customer') {
            return redirect('/'); // or use `route('login')`
        }

        $order = DB::table('dt_order_allocation')->where('order_ID', $id)->first();

        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        $newStatus = $order->staging_flag === 'Yes' ? 'No' : 'Yes';
        $newDate = $newStatus === 'Yes' ? now()->toDateString() : 'NA';

        DB::table('dt_order_allocation')
            ->where('order_ID', $id)
            ->update([
                'staging_flag' => $newStatus,
                'staging_date' => $newDate,
            ]);

        return back()->with('success', 'Staging status updated.');
    }


    public function bulkUnstage(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:dt_order_allocation,order_ID'
        ]);

        OrderAllocation::whereIn('order_ID', $request->orders)
            ->update([
                'staging_flag' => 'No',
                'staging_date' => 'NA'
            ]);

        return redirect()->route('order-allocations.index')
            ->with('success', 'Orders un-staged successfully');
    }

    public function allocateSingle(Request $request)
    {
        $validated = $request->validate([
            'orderIDNow' => 'required|integer|exists:dt_order_allocation,order_ID',
            'itemnumber' => 'required|integer|min:1'
        ]);

        $orderIDNow = $request->orderIDNow;
        $orderValueNow = rtrim($request->itemnumber, ' ');

        // $dataInfo = OrderAllocation::find($orderIDNow);
        $dataInfo = OrderAllocation::where("order_ID", $orderIDNow)->first();
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        if ($orderValueNow > $dataInfo->order_quantity) {
            $orderValueNow = $dataInfo->order_quantity;
        }

        try {
            // Move to history
            $historyData = $dataInfo->toArray();
            $historyData['order_quantity'] = $orderValueNow;
            OrderHistory::create($historyData);

            // Update order quantity
            $dataInfo->decrement('order_quantity', $orderValueNow);

            // Update status if quantity is 0
            if ($dataInfo->order_quantity <= 0) {
                $dataInfo->update(['order_status' => 'Allocated']);
                Order::where('order_ID', $orderIDNow)
                    ->update(['order_status' => 'Allocated']);
            }

            // Update inventory if order is from owner company
            if (strtolower($dataInfo->order_customer_name) == strtolower($ownerComp)) {
                $countExistingInventory = Inventory::where('product_style', $dataInfo->order_product_style)
                    ->where('product_color', $dataInfo->order_product_color)
                    ->where('product_size', $dataInfo->order_product_size)
                    ->count();

                if ($countExistingInventory > 0) {
                    Inventory::where('product_style', $dataInfo->order_product_style)
                        ->where('product_color', $dataInfo->order_product_color)
                        ->where('product_size', $dataInfo->order_product_size)
                        ->increment('product_quantity', $orderValueNow);
                } else {
                    $product = Product::find($dataInfo->order_product_ID);

                    Inventory::create([
                        'product_ID' => $dataInfo->order_product_ID,
                        'product_style' => $dataInfo->order_product_style,
                        'product_color' => $dataInfo->order_product_color,
                        'product_size' => $dataInfo->order_product_size,
                        'product_cost' => $product->product_cost,
                        'product_wholesale_price' => $product->product_wholesale_price,
                        'product_vendor_ID' => $dataInfo->order_vendor_ID,
                        'product_vendor_name' => $dataInfo->order_vendor_name,
                        'product_link' => $product->product_link,
                        'product_image' => $product->product_image,
                        'product_quantity' => $orderValueNow
                    ]);
                }
            }
        } catch (\Exception $e) {
            return redirect()->route('order-allocations.index')
                ->with('error', 'Error during allocation: ' . $e->getMessage());
        }

        return redirect()->route('order-allocations.index')
            ->with('success', 'Order allocated successfully');
    }



    public function downloadAllocation()
    {
        $orders = OrderAllocation::where('order_status', '!=', 'Allocated')->get();

        $filename = 'allocation_orders_download_' . now()->format('Y-m-d_H-i-s');
        $resultsExport = [];

        // Headers
        $resultsExport[] = [
            "order_ID",
            "order_customer_ID",
            "order_customer_name",
            "order_vendor_ID",
            "order_vendor_name",
            "vendor_purchase_ID",
            "order_product_ID",
            "order_product_style",
            "order_product_color",
            "order_product_size",
            "order_quantity",
            "given_by_invntry",
            "given_by_onway",
            "order_cost",
            "order_purchase_price",
            "order_note",
            "purchase_id",
            "created_at",
            "placed_at",
            "onway_vndr_prchs_ids",
            "onway_cstmr_prchs_ids",
            "staging_date",
            "sub_products",
            "order_status"
        ];

        // Data rows
        foreach ($orders as $order) {
            $resultsExport[] = [
                $order->order_ID,
                $order->order_customer_ID,
                strtoupper($order->order_customer_name),
                $order->order_vendor_ID,
                strtoupper($order->order_vendor_name),
                strtoupper($order->vendor_purchase_ID),
                $order->order_product_ID,
                strtoupper($order->order_product_style),
                strtoupper($order->order_product_color),
                $order->order_product_size,
                $order->order_quantity,
                $order->given_by_invntry,
                $order->given_by_onway,
                $order->order_cost,
                $order->order_purchase_price,
                $order->order_note,
                $order->purchase_id,
                $order->created_at,
                $order->created_at_allocation,
                strtoupper($order->onway_vndr_prchs_ids),
                $order->onway_cstmr_prchs_ids,
                $order->staging_date,
                $order->sub_products,
                $order->order_status
            ];
        }

        return \Shuchkin\SimpleXLSXGen::fromArray($resultsExport)
            ->downloadAs($filename . '.xlsx');
    }

    public function clearAllOrders()
    {
        if (!auth()->user()->hasRole('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () {
            DB::table('dt_order')->truncate();
            DB::table('dt_order_final')->truncate();
            DB::table('dt_order_allocation')->truncate();
            DB::table('dt_order_history')->truncate();
        });

        return redirect()->back()->with('success', 'All orders have been cleared.');
    }

    public function cancelOrders(Request $request)
    {
        $validated = $request->validate([
            'orderIDs' => 'required|array',
            'orderIDs.*' => 'exists:dt_order_allocation,order_ID'
        ]);

        DB::transaction(function () use ($validated) {
            // Get the order IDs as a comma-separated string for raw query
            $orderIDs = implode("','", $validated['orderIDs']);

            // Use raw query to match your exact logic
            DB::insert("
            INSERT INTO `dt_order_allocation_cancel`(
                `allocation_ID`, 
                `final_ID`, 
                `order_ID`, 
                `order_customer_ID`, 
                `order_customer_name`, 
                `order_vendor_ID`, 
                `order_vendor_name`, 
                `vendor_purchase_ID`, 
                `order_product_ID`, 
                `order_product_style`, 
                `order_product_color`, 
                `order_product_size`, 
                `order_quantity`, 
                `given_by_invntry`, 
                `given_by_onway`, 
                `order_cost`, 
                `order_purchase_price`, 
                `order_note`, 
                `purchase_id`, 
                `created_at`, 
                `created_at_final`, 
                `created_at_allocation`, 
                `onway_vndr_prchs_ids`, 
                `onway_cstmr_prchs_ids`, 
                `order_status`, 
                `bypass`, 
                `order_wear_date`, 
                `user_flag`, 
                `order_GUID`, 
                `staging_flag`, 
                `staging_date`,
                `sub_products`
            ) 
            SELECT * 
            FROM dt_order_allocation 
            WHERE order_ID IN ('" . $orderIDs . "')
        ");

            // Delete the cancelled orders
            DB::delete("DELETE FROM dt_order_allocation WHERE order_ID IN ('" . $orderIDs . "')");
        });

        return response()->json(['message' => 'Order(s) Cancelled Successfully!']);
    }

    public function deleteAllocated($id)
    {
        // if (auth()->guest() || auth()->user()->admin_role === 'customer') {
        //     return redirect('/');
        // }

        try {
            DB::transaction(function () use ($id) {
                $ownerComp = DB::table('dt_cust')->where('cust_owner', 'Yes')->value('cust_comp_name');
                $ownerCompID = DB::table('dt_cust')->where('cust_owner', 'Yes')->value('cust_ID');

                $onWayCount = DB::table('dt_order_allocation')->where('order_ID', $id)->value('given_by_onway');
                $inventoryCount = DB::table('dt_order_allocation')->where('order_ID', $id)->value('given_by_invntry');

                if ($onWayCount == 0 && $inventoryCount == 0) {
                    DB::table('dt_order_allocation')->where('order_ID', $id)->delete();
                    DB::table('dt_order')->where('order_ID', $id)->delete();
                } elseif ($onWayCount > 0) {
                    $order = DB::table('dt_order')->where('order_ID', $id)->first();

                    DB::table('dt_order_allocation')->insert([
                        'final_ID' => 0,
                        'order_ID' => $order->order_ID,
                        'order_customer_ID' => $ownerCompID,
                        'order_customer_name' => $ownerComp,
                        'order_vendor_ID' => $order->order_vendor_ID,
                        'order_vendor_name' => $order->order_vendor_name,
                        'vendor_purchase_ID' => $order->onway_vndr_prchs_ids,
                        'order_product_ID' => $order->order_product_ID,
                        'order_product_style' => $order->order_product_style,
                        'order_product_color' => $order->order_product_color,
                        'order_product_size' => $order->order_product_size,
                        'order_quantity' => $order->order_quantity,
                        'given_by_invntry' => 0,
                        'given_by_onway' => 0,
                        'order_cost' => $order->order_cost,
                        'order_purchase_price' => $order->order_purchase_price,
                        'order_note' => $order->order_note,
                        'purchase_id' => $order->onway_cstmr_prchs_ids,
                        'created_at' => now(),
                        'created_at_final' => now(),
                        'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                        'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                        'order_wear_date' => $order->order_wear_date,
                        'sub_products' => json_encode($order->sub_products ?? []),
                        'user_flag' => $order->user_flag,
                    ]);

                    DB::table('dt_order_allocation')
                        ->where('order_customer_name', '!=', $ownerComp)
                        ->where('order_ID', $id)
                        ->delete();

                    DB::table('dt_order')->where('order_ID', $id)->delete();
                } elseif ($inventoryCount > 0) {
                    $order = DB::table('dt_order_allocation')->where('order_ID', $id)->first();

                    DB::table('dt_inventory')
                        ->where('product_style', $order->order_product_style)
                        ->where('product_color', $order->order_product_color)
                        ->where('product_size', $order->order_product_size)
                        ->update([
                            'product_quantity' => $order->order_quantity,
                        ]);

                    DB::table('dt_order_allocation')->where('order_ID', $id)->delete();
                    DB::table('dt_order')->where('order_ID', $id)->delete();
                }
            });

            return redirect()->route('order-allocations.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function deleteFullOrder($id)
    {
        if (auth()->guest() || auth()->user()->admin_role === 'customer') {
            return redirect('/');
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('dt_order_allocation')->where('order_ID', $id)->delete();
                DB::table('dt_order_final')->where('order_ID', $id)->delete();
                DB::table('dt_order')->where('order_ID', $id)->delete();
            });

            return redirect()->route('order-allocations.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }




    public function edit($id)
    {
        // Authentication check
        if (!Auth::check() || Auth::user()->admin_role === 'customer') {
            return redirect()->route('home');
        }

        // $order = OrderAllocation::where("order_ID", $id)->first();
        $order = OrderAllocation::where("order_ID", $id)
        ->whereNot("order_status", "Allocated")
        ->first();

        $ownerCompany = "";
        $ownerCompany1 = Customer::where('cust_owner', 'Yes')->first();
        if ($ownerCompany1) {
            $ownerCompany = $ownerCompany1->cust_comp_name;
        }

        // Get related data
        $colors = Product::where('product_style', $order->order_product_style)
            ->where('product_status', 1)
            ->distinct('product_color')
            ->pluck('product_color');

        // $sizeRange = Product::where('product_style', $order->order_product_style)
        //     ->first()->product_size_range;
        
        $sizeRange = Product::where('product_style', $order->order_product_style)
            ->value('product_size_range');
        
        // dd($sizeRange);

        // $costProduct = Product::where('product_style', $order->order_product_style)
        //     ->first()->product_wholesale_price;
        
        $costProduct = Product::where('product_style', $order->order_product_style)
    ->value('product_wholesale_price');

        $vendors = Vendor::select('vendor_ID', 'vendor_comp_name')->get();
        $customers = Customer::orderBy('cust_comp_name', 'asc')->get(['cust_ID', 'cust_comp_name']);

        $products = Product::select('product_style', 'product_vendor_name')->distinct()->get();

        $vxp = Product::select('product_style', 'product_vendor_name', 'product_vendor_ID')
            ->distinct()
            ->get();


        $product = Product::where('product_style',  $order->order_product_style)->first();
        $sub_products = $product && $product->sub_products ? $product->sub_products : [];
        $subProducts = is_string($sub_products)
            ? json_decode($sub_products, true)
            : $sub_products;


        return view('order-allocations.edit', compact(
            'order',
            'ownerCompany',
            'colors',
            'sizeRange',
            'costProduct',
            'vendors',
            'customers',
            'products',
            'subProducts',
            'vxp'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'style' => 'required|string',
            'color' => 'required|string',
            // 'size' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'purchase_id' => 'required|string',
            'vpID' => 'nullable|string',
            'wearDate' => 'required|date',
            'note' => 'nullable|string',
            'customers' => 'required|string'
        ]);
        
        // dd($request);

        // Get product info
        $product = Product::where('product_style', strtolower($request->style))
            ->where('product_color', $request->color)
            ->firstOrFail();

        // Calculate costs
        $orderCost = ($request->size < 18)
            ? $request->quantity * $product->product_wholesale_price
            : $request->quantity * ($product->product_wholesale_price + 30);

        $orderPurchasePrice = $request->quantity * $product->product_cost;

        // Get customer info
        $customer = Customer::where('cust_comp_name', $request->customers)->firstOrFail();

        $order  = OrderAllocation::where("order_ID", $id)->first();
        // Update all three related order tables
        $this->updateOrderTables(
            $order->order_ID,
            $customer,
            $product,
            $request,
            $orderCost,
            $orderPurchasePrice
        );

        return redirect()->route('order-allocations.index')->with('success', 'Order updated successfully');
    }

    protected function updateOrderTables($orderId, $customer, $product, $request, $orderCost, $orderPurchasePrice)
    {
        $commonData = [
            'order_customer_ID' => $customer->cust_ID,
            'order_customer_name' => $customer->cust_comp_name,
            'order_vendor_ID' => $product->product_vendor_ID,
            'order_vendor_name' => $product->product_vendor_name,
            'order_product_ID' => $product->product_ID,
            'order_product_style' => $request->style,
            'order_product_color' => $request->color,
            'order_product_size' => $request->size,
            'order_quantity' => $request->quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $request->note ?? "",
            'purchase_id' => $request->purchase_id,
            'sub_products' => $request->sub_products,
            'order_wear_date' => $request->wearDate
        ];

        $allocationData = array_merge($commonData, [
            'vendor_purchase_ID' => $request->vpID
        ]);

        // Update all three tables
        OrderAllocation::where('order_ID', $orderId)->update($allocationData);
        OrderFinal::where('order_ID', $orderId)->update($commonData);
        Order::where('order_ID', $orderId)->update($commonData);
    }


    public function show($id)
    {
        $order = OrderAllocation::where("order_ID", $id)->first();

        if (!$order) {
            return  abort(404, 'Not Found.');
        }

        return response()->json([
            'venName' => $order->order_vendor_name,
            'venPur' => $order->vendor_purchase_ID,
            'ordID' => $order->order_ID,
            'CusName' => $order->order_customer_name,
            'styleNumber' => $order->order_product_style,
            'prodColor' => $order->order_product_color,
            'prodSize' => $order->order_product_size,
            'ordQuant' => $order->order_quantity,
            'fromIn' => $order->given_by_invntry,
            'fromONW' => $order->given_by_onway
        ]);
    }

    public function allocate(Request $request, $id)
    {
        $request->validate([
            'itemnumber' => 'required|integer|min:1'
        ]);
        $order = OrderAllocation::where("order_ID", $id)->first();
        $orderValueNow = min($request->itemnumber, $order->order_quantity);
        $ownerCompany = "";
        $ownerCompany1 = Customer::where('cust_owner', 'Yes')->first();
        if ($ownerCompany1) {
            $ownerCompany = $ownerCompany1->cust_comp_name;
        }

        $order = OrderAllocation::where("order_ID", $id)->first();

        $product = Product::find($order->order_product_ID);

        if (!$product) {
            return redirect()->route('order-allocations.index')->with('error', 'Product not Found');
        }

        DB::transaction(function () use ($id, $orderValueNow, $ownerCompany) {
            // $order = OrderAllocation::findOrFail($id);
            $order = OrderAllocation::where("order_ID", $id)->first();

            $product = Product::find($order->order_product_ID);

            if (!$product) {
                return redirect()->route('order-allocations.index')->with('error', 'Product not Found');
            }

            // Create history record
            OrderHistory::create(array_merge(
                $order->toArray(),
                ['order_quantity' => $orderValueNow]
            ));

            // Update allocation
            $order->decrement('order_quantity', $orderValueNow);

            // Update status if fully allocated
            if ($order->order_quantity <= 0) {
                $order->update(['order_status' => 'Allocated']);
                Order::where('order_ID', $id)->update(['order_status' => 'Allocated']);
            }

            // Update inventory if owner company
            if (strtolower($order->order_customer_name) === strtolower($ownerCompany)) {
                $this->updateInventory($order, $orderValueNow);
            }
        });

        return redirect()->route('order-allocations.index')->with('success', 'Order allocated successfully');
    }

    protected function updateInventory($order, $quantity)
    {
        $inventory = Inventory::where('product_style', $order->order_product_style)
            ->where('product_color', $order->order_product_color)
            ->where('product_size', $order->order_product_size)
            ->first();

        if ($inventory) {
            $inventory->increment('product_quantity', $quantity);
        } else {
            $product = Product::find($order->order_product_ID);

            if (!$product) {
                return redirect()->route('order-allocations.index')->with('error', 'Product not Found');
            }

            Inventory::create([
                'product_ID' => $order->order_product_ID,
                'product_style' => $order->order_product_style,
                'product_color' => $order->order_product_color,
                'product_size' => $order->order_product_size,
                'product_cost' => $product->product_cost,
                'product_wholesale_price' => $product->product_wholesale_price,
                'product_vendor_ID' => $order->order_vendor_ID,
                'product_vendor_name' => $order->order_vendor_name,
                'product_link' => $product->product_link,
                'product_image' => $product->product_image,
                'product_quantity' => $quantity
            ]);
        }
    }
}
