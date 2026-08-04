<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFinal;
use App\Models\OrderFinalCancel;
use App\Models\OrderAllocation;
use App\Models\Vendor;
use App\Models\EmailBody as EmailTemplate;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Shuchkin\SimpleXLSXGen;

use App\Models\OrderAllocationCancel;
use App\Models\OrderCancel;
use App\Models\OrderHistoryArchive;
use App\Models\OrderHistory;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\CustomerOrderConfirmation;
use App\Mail\VendorOrderNotification;


class OrderFinalController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        if (Auth::user()->admin_role == 'customer') {
            return redirect()->route('home');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        $totOrderQuant = OrderFinal::where('order_status', '!=', 'Placed')->sum('order_quantity') ?? 0;
        $totOrderQuant_Comp = OrderFinal::where('order_status', '!=', 'Placed')
            ->where('order_customer_name', $ownerComp)
            ->count();
        $totOrderQuant_Others = OrderFinal::where('order_status', '!=', 'Placed')
            ->where('order_customer_name', '!=', $ownerComp)
            ->count();

        return view('admin.final-orders.index', compact(
            'ownerComp',
            'totOrderQuant',
            'totOrderQuant_Comp',
            'totOrderQuant_Others'
        ));
    }
    
    public function getFinalOrdersData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $searchValue = trim((string) $request->input('search.value', ''));
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Column index -> DB column, matching the <thead> order in the Blade view.
        // 0 (checkbox), 7 (sub_products), 19 (actions) are intentionally omitted.
        $sortableColumns = [
            1 => 'order_ID',
            2 => 'order_GUID',
            3 => 'order_vendor_name',
            4 => 'order_customer_name',
            5 => 'order_product_style',
            6 => 'order_product_color',
            8 => 'order_product_size',
            9 => 'order_quantity',
            10 => 'given_by_invntry',
            11 => 'given_by_onway',
            12 => 'order_cost',
            13 => 'order_purchase_price',
            14 => 'created_at',
            15 => 'order_status',
            16 => 'purchase_id',
            17 => 'order_wear_date',
            18 => 'user_flag',
        ];

        $base = OrderFinal::where('order_status', '!=', 'Placed');

        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('order_GUID', 'like', "%{$searchValue}%")
                    ->orWhere('order_vendor_name', 'like', "%{$searchValue}%")
                    ->orWhere('order_customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_style', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_color', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_size', 'like', "%{$searchValue}%")
                    ->orWhere('order_status', 'like', "%{$searchValue}%")
                    ->orWhere('purchase_id', 'like', "%{$searchValue}%")
                    ->orWhere('user_flag', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        if ($orderColumnIndex !== null && isset($sortableColumns[$orderColumnIndex])) {
            $query->orderBy($sortableColumns[$orderColumnIndex], $orderDir);
        } else {
            $query->orderBy('order_ID', 'desc');
        }

        if ($length != -1) {
            $query->offset($start)->limit($length);
        }

        $rows = $query->get();

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $canDelete = auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1';

        $data = [];
        foreach ($rows as $order) {

            // Same row-coloring rules as the old Blade @php block (order matters —
            // later conditions override earlier ones, same as the original).
            $rowStyle = '';
            if (str_contains(strtoupper((string) $order->order_customer_name), strtoupper((string) $ownerComp))) {
                $rowStyle = 'background-color: #3f4d67; color:white;';
            }
            if ($order->given_by_invntry > 0) {
                $rowStyle = 'background-color: rgb(0 100 12); color:white;';
            }
            if ($order->given_by_onway > 0) {
                $rowStyle = 'background-color: rgb(209 198 0); color:black;';
            }
            if ($order->order_status == 'Confirmed to Customer') {
                $rowStyle = 'background-color: #90EE90; color:black;';
            }

            $subProductsArr = [];
            if (!empty($order->sub_products)) {
                $decoded = is_array($order->sub_products)
                    ? $order->sub_products
                    : json_decode($order->sub_products, true);
                if (is_array($decoded)) {
                    $subProductsArr = $decoded;
                }
            }
            $subProductsText = implode(', ', $subProductsArr);

            $displayStatus = $order->order_status == 'Pending' ? 'Accepted' : $order->order_status;
            $createdDate = $order->created_at ? explode(' ', (string) $order->created_at)[0] : '';

            $actions = '<a target="_self" class="btn btn-success mb-0 btn-sm" href="' . route('final-orders.edit', $order->final_ID) . '">Edit</a>';
            if ($canDelete) {
                $actions .= ' <a target="_self" class="btn btn-danger mb-0 btn-sm" href="' . route('order-finals.delete-id', $order->order_ID) . '">Delete</a>';
            }
            $actions .= ' <input name="bypass" type="submit" class="btn btn-warning mb-0 btn-sm" value="Bypass" '
                . 'onclick="javascript:document.getElementById(\'orderIDNew\').value=' . (int) $order->order_ID . ';">';
            $actions .= '<input type="text" hidden name="orderID" value="' . e($order->final_ID) . '">';

            $data[] = [
                'checkbox' => '<input class="form-check-input" type="checkbox" value="' . e($order->order_ID) . '" id="chk_final_' . e($order->order_ID) . '" name="orders[]"><label class="form-check-label" for="chk_final_' . e($order->order_ID) . '"></label>',
                'order_id' => e($order->order_ID),
                'order_guid' => e($order->order_GUID),
                'vendor' => strtoupper(e($order->order_vendor_name)),
                'customer' => strtoupper(e($order->order_customer_name)),
                'style' => strtoupper(e($order->order_product_style)),
                'color' => strtoupper(e($order->order_product_color)),
                'sub_products' => e($subProductsText),
                'size' => e($order->order_product_size),
                'quantity' => e($order->order_quantity),
                'from_inventory' => e($order->given_by_invntry),
                'from_onway' => e($order->given_by_onway),
                'total_cost' => e($order->order_cost),
                'total_price' => e($order->order_purchase_price),
                'place_date' => e($createdDate),
                'status' => e($displayStatus),
                'purchase_id' => e($order->purchase_id),
                'wear_date' => e($order->order_wear_date),
                'user' => e($order->user_flag),
                'actions' => $actions,
                'row_style' => $rowStyle,
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function confirmCustomer(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:dt_order_final,order_ID'
        ]);

        $orderIDs = $request->input('orders');
        $orderIDsChecked = implode(',', $orderIDs);

        DB::transaction(function () use ($orderIDs, $orderIDsChecked) {
            // Get email template
            $emailTemplate = EmailTemplate::where('email_role', 'customer')->first();

            // Group orders by customer
            $customerOrders = OrderFinal::selectRaw('order_customer_ID, GROUP_CONCAT(order_ID) as orders')
                ->whereIn('order_ID', $orderIDs)
                ->groupBy('order_customer_ID')
                ->get();

            foreach ($customerOrders as $customerOrder) {
                $customer = Customer::find($customerOrder->order_customer_ID);
                $orders = Order::whereIn('order_ID', explode(',', $customerOrder->orders))->get();

                // Get vendor messages
                $vendorIds = $orders->pluck('order_vendor_ID')->unique();
                $vendors = Vendor::whereIn('vendor_ID', $vendorIds)->get()->keyBy('vendor_ID');
                $vendorMessages = $vendors->map(function ($vendor) {
                    return $vendor->message;
                });

                // dd($customerOrder);

                $totalCost = $orders->sum('order_purchase_price');

                // Send email
                Mail::to($customer->cust_email)
                    ->cc(config('mail.from.address'))
                    ->send(new CustomerOrderConfirmation(
                        $emailTemplate->email_body,
                        $orders,
                        $vendorMessages,
                        $totalCost
                    ));

                // Update order statuses
                Order::whereIn('order_ID', explode(',', $customerOrder->orders))
                    ->update(['order_status' => 'Confirmed']);
            }

            OrderFinal::whereIn('order_ID', $orderIDs)
                ->update(['order_status' => 'Confirmed to Customer']);
        });

        return redirect()->route('final-orders.index')
            ->with('success', 'Orders confirmed to customers');
    }

    public function confirmVendorTestMail(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:dt_order_final,order_ID',
            'dateNow' => 'required|date'
        ]);


        $orderIDs = $request->input('orders');
        $orderIDsChecked = implode(',', $orderIDs);
        $shipByDate = $request->input('dateNow');




        // DB::transaction(function () use ($orderIDs, $orderIDsChecked, $shipByDate) {
        // Get email template
        $emailTemplate = EmailTemplate::where('email_role', 'vendor')->first();

        // Group orders by vendor
        $vendorOrders = OrderFinal::selectRaw('order_vendor_ID, order_vendor_name, GROUP_CONCAT(order_ID) as orders')
            ->whereIn('order_ID', $orderIDs)
            ->where('order_status', '!=', 'Placed')
            ->groupBy('order_vendor_ID', 'order_vendor_name')
            ->get();

        foreach ($vendorOrders as $vendorOrder) {
            $vendor = Vendor::where('vendor_comp_name', $vendorOrder->order_vendor_name)->first();

            if (!$vendor) {
                return redirect()->route('final-orders.index')
                    ->with('error', $vendorOrder->order_vendor_name . " Vendor Not Found");
            }


            $vendorPurchaseId = strtoupper($vendorOrder->order_vendor_name) . '_' . now()->format('d_M_Y') . '_' . config('app.name');

            // Get unique styles for this vendor
            $orders = OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))->get();
            // $styleGroups = $orders->groupBy('order_product_style');

            $styleGroups = $orders->groupBy(function ($item) {
                return trim(strtoupper($item->order_product_style));
            });


            // Prepare data for email
            $styleData = [];
            $minSize = 0;
            $maxSize = 28;

            foreach ($styleGroups as $style => $styleOrders) {
                $colorGroups = $styleOrders->groupBy('order_product_color');
                $colorData = [];

                foreach ($colorGroups as $color => $colorOrders) {
                    $sizes = [];
                    for ($i = $minSize; $i <= $maxSize; $i += 2) {
                        $sizes[$i] = 0;
                    }

                    foreach ($colorOrders as $order) {
                        // $sizeIndex = ($order->order_product_size - $minSize) / 2;
                        $sizes[$order->order_product_size] += $order->order_quantity;
                    }

                    $colorData[$color] = $sizes;
                }

                $styleData[$style] = $colorData;
            }

            $totalPrice = $orders->sum('order_cost');
            $emailBody = $emailTemplate->email_body;
            $styleGroups = $styleData;

            return view('emails.vendor_order', compact(
                'emailBody',
                'styleGroups',
                'minSize',
                'maxSize',
                'totalPrice',
                'vendorPurchaseId',
                'shipByDate',
                'orders',
            ));
        }
        // });

        return redirect()->route('final-orders.index')
            ->with('success', 'Orders sent to vendors');
    }

    // public function confirmVendor(Request $request)
    // {
    //     $request->validate([
    //         'orders' => 'required|array',
    //         'orders.*' => 'exists:dt_order_final,order_ID',
    //         'dateNow' => 'required|date'
    //     ]);


    //     $orderIDs = $request->input('orders');
    //     $orderIDsChecked = implode(',', $orderIDs);
    //     $shipByDate = $request->input('dateNow');




    //     DB::transaction(function () use ($orderIDs, $orderIDsChecked, $shipByDate) {
    //         // Get email template
    //         $emailTemplate = EmailTemplate::where('email_role', 'vendor')->first();

    //         // Group orders by vendor
    //         $vendorOrders = OrderFinal::selectRaw('order_vendor_ID, order_vendor_name, GROUP_CONCAT(order_ID) as orders')
    //             ->whereIn('order_ID', $orderIDs)
    //             ->where('order_status', '!=', 'Placed')
    //             ->groupBy('order_vendor_ID', 'order_vendor_name')
    //             ->get();

    //         foreach ($vendorOrders as $vendorOrder) {
    //             $vendor = Vendor::where('vendor_comp_name', $vendorOrder->order_vendor_name)->first();

    //             if (!$vendor) {
    //                 return redirect()->route('final-orders.index')
    //                     ->with('error',  $vendorOrder->order_vendor_name . " Vendor Not Found");
    //             }


    //             $vendorPurchaseId = strtoupper($vendorOrder->order_vendor_name) . '_' . now()->format('d_M_Y') . '_' . config('app.name');

    //             // Get unique styles for this vendor
    //             $orders = OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))->get();
    //             // $styleGroups = $orders->groupBy('order_product_style');

    //             $styleGroups = $orders->groupBy(function ($item) {
    //                 return trim(strtoupper($item->order_product_style));
    //             });


    //             // Prepare data for email
    //             $styleData = [];
    //             $minSize = 0;
    //             $maxSize = 28;

    //             foreach ($styleGroups as $style => $styleOrders) {
    //                 $colorGroups = $styleOrders->groupBy('order_product_color');
    //                 $colorData = [];

    //                 foreach ($colorGroups as $color => $colorOrders) {
    //                     $sizes = [];
    //                     for ($i = $minSize; $i <= $maxSize; $i += 2) {
    //                         $sizes[$i] = 0;
    //                     }

    //                     foreach ($colorOrders as $order) {
    //                         // $sizeIndex = ($order->order_product_size - $minSize) / 2;
    //                         $sizes[$order->order_product_size] += $order->order_quantity;
    //                     }

    //                     $colorData[$color] = $sizes;
    //                 }

    //                 $styleData[$style] = $colorData;
    //             }

    //             $totalPrice = $orders->sum('order_cost');

    //             Mail::to($vendor->vendor_email)
    //                 ->cc(config('mail.from.address'))
    //                 ->send(new VendorOrderNotification(
    //                     $emailTemplate->email_body,
    //                     $styleData,
    //                     $minSize,
    //                     $maxSize,
    //                     $totalPrice,
    //                     $vendorPurchaseId,
    //                     $shipByDate,
    //                     $orders
    //                 ));

    //             // Update orders and create allocations
    //             Order::whereIn('order_ID', explode(',', $vendorOrder->orders))
    //                 ->update(['order_status' => 'Placed']);

    //             OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))
    //                 ->update(['order_status' => 'Placed']);

    //             // Create allocation records
    //             $finalOrders = OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))->get();

    //             foreach ($finalOrders as $order) {
    //                 OrderAllocation::create([
    //                     'final_ID' => $order->final_ID,
    //                     'order_ID' => $order->order_ID,
    //                     'order_customer_ID' => $order->order_customer_ID,
    //                     'order_customer_name' => $order->order_customer_name,
    //                     'order_vendor_ID' => $order->order_vendor_ID,
    //                     'order_vendor_name' => $order->order_vendor_name,
    //                     'order_product_ID' => $order->order_product_ID,
    //                     'order_product_style' => $order->order_product_style,
    //                     'order_product_color' => $order->order_product_color,
    //                     'order_product_size' => $order->order_product_size,
    //                     'order_quantity' => $order->order_quantity,
    //                     'given_by_invntry' => $order->given_by_invntry,
    //                     'given_by_onway' => $order->given_by_onway,
    //                     'order_cost' => $order->order_cost,
    //                     'order_purchase_price' => $order->order_purchase_price,
    //                     'order_note' => $order->order_note,
    //                     'purchase_id' => $order->purchase_id,
    //                     'created_at' => $order->created_at,
    //                     'created_at_final' => $order->created_at_final,
    //                     'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
    //                     'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
    //                     'vendor_purchase_ID' => $vendorPurchaseId,
    //                     'order_wear_date' => $order->order_wear_date,
    //                     'user_flag' => $order->user_flag,
    //                     'sub_products' => $order->sub_products,
    //                     'order_GUID' => $order->order_GUID
    //                 ]);
    //             }
    //         }
    //     });

    //     return redirect()->route('final-orders.index')
    //         ->with('success', 'Orders sent to vendors');
    // }

    public function confirmVendor(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:dt_order_final,order_ID',
            'dateNow' => 'required|date'
        ]);

        $orderIDs = $request->input('orders');
        $orderIDsChecked = implode(',', $orderIDs);
        $shipByDate = $request->input('dateNow');

        DB::transaction(function () use ($orderIDs, $orderIDsChecked, $shipByDate) {
            // Get email template
            $emailTemplate = EmailTemplate::where('email_role', 'vendor')->first();

            // Group orders by vendor
            $vendorOrders = OrderFinal::selectRaw('order_vendor_ID, order_vendor_name, GROUP_CONCAT(order_ID) as orders')
                ->whereIn('order_ID', $orderIDs)
                ->where('order_status', '!=', 'Placed')
                ->groupBy('order_vendor_ID', 'order_vendor_name')
                ->get();

            foreach ($vendorOrders as $vendorOrder) {
                $vendor = Vendor::where('vendor_comp_name', $vendorOrder->order_vendor_name)->first();

                if (!$vendor) {
                    return redirect()->route('final-orders.index')
                        ->with('error', $vendorOrder->order_vendor_name . " Vendor Not Found");
                }

                $vendorPurchaseId = strtoupper($vendorOrder->order_vendor_name) . '_' . now()->format('d_M_Y') . '_' . config('app.name');

                // Get unique styles for this vendor
                $orders = OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))->get();
                // $styleGroups = $orders->groupBy('order_product_style');

                $styleGroups = $orders->groupBy(function ($item) {
                    return trim(strtoupper($item->order_product_style));
                });

                // Prepare data for email
                $styleData = [];
                $minSize = 0;
                $maxSize = 28;

                foreach ($styleGroups as $style => $styleOrders) {
                    $colorGroups = $styleOrders->groupBy('order_product_color');
                    $colorData = [];

                    // foreach ($colorGroups as $color => $colorOrders) {
                    //     $sizes = [];

                    //     foreach ($colorOrders as $order) {

                    //         $size = $order->order_product_size;
                    //         dd($size);

                    //         // IMPORTANT: preserve "00" explicitly
                    //         if ($size === '00' || $size === 0 || $size === '0') {
                    //             $sizeKey = '00';
                    //         } else {
                    //             $sizeKey = (string) $size;
                    //         }

                    //         $sizes[$sizeKey] = ($sizes[$sizeKey] ?? 0) + $order->order_quantity;
                    //     }

                    //     $colorData[$color] = $sizes;
                    // }

                    foreach ($colorGroups as $color => $colorOrders) {

                        $sizes = [];

                        foreach ($colorOrders as $order) {

                            $size = (string) $order->order_product_size;

                            if ($size === '00') {
                                $sizeKey = '00';
                            } else {
                                $sizeKey = (string) $size;
                            }

                            if (!isset($sizes[$sizeKey])) {
                                $sizes[$sizeKey] = 0;
                            }

                            $sizes[$sizeKey] += $order->order_quantity;
                        }

                        // THIS WAS MISSING
                        $colorData[$color] = $sizes;
                    }

                    $styleData[$style] = $colorData;
                }

                $totalPrice = $orders->sum('order_cost');

                // dd($styleData);

                // $sizeHeaders = [];

                // foreach ($styleData as $style => $colors) {
                //     foreach ($colors as $color => $sizes) {
                //         $sizeHeaders = array_unique(array_merge($sizeHeaders, array_keys($sizes)));
                //     }
                // }

                // sort($sizeHeaders);

                $sizeHeaders = ['00', '0', '2', '4', '6', '8', '10', '12', '14', '16', '18', '20', '22', '24', '26', '28'];

                Mail::to($vendor->vendor_email)
                    ->cc(config('mail.from.address'))
                    ->send(new VendorOrderNotification(
                        $emailTemplate->email_body,
                        $styleData,
                        // $minSize,
                        // $maxSize,
                        $sizeHeaders,
                        $totalPrice,
                        $vendorPurchaseId,
                        $shipByDate,
                        $orders
                    ));

                // Update orders and create allocations
                Order::whereIn('order_ID', explode(',', $vendorOrder->orders))
                    ->update(['order_status' => 'Placed']);

                OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))
                    ->update(['order_status' => 'Placed']);

                // Create allocation records
                $finalOrders = OrderFinal::whereIn('order_ID', explode(',', $vendorOrder->orders))->get();

                foreach ($finalOrders as $order) {
                    OrderAllocation::create([
                        'final_ID' => $order->final_ID,
                        'order_ID' => $order->order_ID,
                        'order_customer_ID' => $order->order_customer_ID,
                        'order_customer_name' => $order->order_customer_name,
                        'order_vendor_ID' => $order->order_vendor_ID,
                        'order_vendor_name' => $order->order_vendor_name,
                        'order_product_ID' => $order->order_product_ID,
                        'order_product_style' => $order->order_product_style,
                        'order_product_color' => $order->order_product_color,
                        'order_product_size' => $order->order_product_size,
                        'order_quantity' => $order->order_quantity,
                        'given_by_invntry' => $order->given_by_invntry,
                        'given_by_onway' => $order->given_by_onway,
                        'order_cost' => $order->order_cost,
                        'order_purchase_price' => $order->order_purchase_price,
                        'order_note' => $order->order_note,
                        'purchase_id' => $order->purchase_id,
                        'created_at' => $order->created_at,
                        'created_at_final' => $order->created_at_final,
                        'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                        'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                        'vendor_purchase_ID' => $vendorPurchaseId,
                        'order_wear_date' => $order->order_wear_date,
                        'user_flag' => $order->user_flag,
                        'sub_products' => $order->sub_products,
                        'order_GUID' => $order->order_GUID
                    ]);
                }
            }
        });

        return redirect()->route('final-orders.index')
            ->with('success', 'Orders sent to vendors');
    }

    public function bypass(Request $request)
    {
        $request->validate([
            'orderIDNew' => 'required|exists:dt_order_final,order_ID'
        ]);

        $orderID = $request->input('orderIDNew');

        DB::transaction(function () use ($orderID) {
            // Update order statuses
            Order::where('order_ID', $orderID)
                ->update(['order_status' => 'Placed']);

            OrderFinal::where('order_ID', $orderID)
                ->update(['order_status' => 'Placed']);

            // Create bypass allocation
            $order = OrderFinal::where('order_ID', $orderID)->first();

            OrderAllocation::create([
                'final_ID' => $order->final_ID,
                'order_ID' => $order->order_ID,
                'order_customer_ID' => $order->order_customer_ID,
                'order_customer_name' => $order->order_customer_name,
                'order_vendor_ID' => $order->order_vendor_ID,
                'order_vendor_name' => $order->order_vendor_name,
                'order_product_ID' => $order->order_product_ID,
                'order_product_style' => $order->order_product_style,
                'order_product_color' => $order->order_product_color,
                'order_product_size' => $order->order_product_size,
                'order_quantity' => $order->order_quantity,
                'given_by_invntry' => $order->given_by_invntry,
                // 'given_by_onway' => $order->given_by_onway,
                'given_by_onway' => 1,
                'order_cost' => $order->order_cost,
                'order_purchase_price' => $order->order_purchase_price,
                'order_note' => $order->order_note,
                'purchase_id' => $order->purchase_id,
                'created_at' => $order->created_at,
                'created_at_final' => $order->created_at_final,
                'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                'vendor_purchase_ID' => 'bypass',
                'bypass' => 1,
                'order_wear_date' => $order->order_wear_date,
                'user_flag' => $order->user_flag,
                'sub_products' => $order->sub_products,
                'order_GUID' => $order->order_GUID
            ]);
        });

        return redirect()->route('final-orders.index')
            ->with('success', 'Order bypassed');
    }

    public function cancel2(Request $request)
    {
        $orderIDs = $request->input('orders');

        if (empty($orderIDs)) {
            return redirect()->route('final-orders.index')->with('error', 'No orders selected');
        }

        // Update order status to cancelled
        OrderFinal::whereIn('order_ID', $orderIDs)->update(['order_status' => 'Cancelled']);

        return redirect()->route('final-orders.index')->with('success', 'Orders cancelled successfully');
    }


    public function cancel(Request $request)
    {
        // Validate that orderIDs exists and is an array
        $request->validate([
            'orderIDs' => 'required|array',
            'orderIDs.*' => 'required|integer'
        ]);

        $orderIDs = $request->input('orderIDs');


        try {
            // DB::transaction(function () use ($orderIDs) {
            //     // Insert into final cancel table
            //     DB::table('dt_order_final_cancel')->insertUsing([
            //         'final_ID',
            //         'order_ID',
            //         'order_customer_ID',
            //         'order_customer_name',
            //         'order_vendor_ID',
            //         'order_vendor_name',
            //         'order_product_ID',
            //         'order_product_style',
            //         'order_product_color',
            //         'order_product_size',
            //         'order_quantity',
            //         'given_by_invntry',
            //         'given_by_onway',
            //         'order_cost',
            //         'order_purchase_price',
            //         'order_note',
            //         'purchase_id',
            //         'created_at',
            //         'created_at_final',
            //         'onway_vndr_prchs_ids',
            //         'onway_cstmr_prchs_ids',
            //         'order_status',
            //         'order_wear_date',
            //         'user_flag',
            //         'sub_products',
            //         'order_GUID'
            //     ], function ($query) use ($orderIDs) {
            //         $query->select([
            //             'final_ID',
            //             'order_ID',
            //             'order_customer_ID',
            //             'order_customer_name',
            //             'order_vendor_ID',
            //             'order_vendor_name',
            //             'order_product_ID',
            //             'order_product_style',
            //             'order_product_color',
            //             'order_product_size',
            //             'order_quantity',
            //             'given_by_invntry',
            //             'given_by_onway',
            //             'order_cost',
            //             'order_purchase_price',
            //             'order_note',
            //             'purchase_id',
            //             'created_at',
            //             DB::raw('NOW() as created_at_final'),
            //             'onway_vndr_prchs_ids',
            //             'onway_cstmr_prchs_ids',
            //             'order_status',
            //             'order_wear_date',
            //             'user_flag',
            //              DB::raw('JSON_OBJECT(sub_products) as sub_products'),
            //             'order_GUID'
            //         ])->from('dt_order_final')
            //             ->whereIn('order_ID', $orderIDs);
            //     });

            //     // Delete from original final table
            //     DB::table('dt_order_final')
            //         ->whereIn('order_ID', $orderIDs)
            //         ->delete();
            // });


            $orders = OrderFinal::whereIn('order_ID', $orderIDs)->get();
            $finalOrderData = $orders->map(function ($order) {
                return [
                    'final_ID' => $order->final_ID,
                    'order_ID' => $order->order_ID,
                    'order_customer_ID' => $order->order_customer_ID,
                    'order_customer_name' => $order->order_customer_name,
                    'order_vendor_ID' => $order->order_vendor_ID,
                    'order_vendor_name' => $order->order_vendor_name,
                    'order_product_ID' => $order->order_product_ID,
                    'order_product_style' => $order->order_product_style,
                    'order_product_color' => $order->order_product_color,
                    'order_product_size' => $order->order_product_size,
                    'order_quantity' => $order->order_quantity,
                    'given_by_invntry' => $order->given_by_invntry,
                    'given_by_onway' => $order->given_by_onway,
                    'order_cost' => $order->order_cost,
                    'order_purchase_price' => $order->order_purchase_price,
                    'order_note' => $order->order_note,
                    'purchase_id' => $order->purchase_id,
                    'created_at' => $order->created_at,
                    'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                    'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                    'order_status' => $order->order_status,
                    'order_wear_date' => $order->order_wear_date,
                    'user_flag' => $order->user_flag,
                    'sub_products' => json_encode($order->sub_products ?? []),  // Manually encode to JSON
                    'order_GUID' => $order->order_GUID,
                    'created_at_final' => now(), // You can add a custom timestamp
                ];
            })->toArray();

            DB::table('dt_order_final_cancel')->insert($finalOrderData);

            return response()->json([
                'success' => true,
                'message' => 'Final order(s) cancelled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel final orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadFinalOrders()
    {
        $orders = OrderFinal::where('order_status', '!=', 'Placed')->get();

        $filename = 'final_orders_download_' . now()->format('Y-m-d_H-i-s');
        $resultsExport = [];

        // Headers
        $resultsExport[] = [
            "order_ID",
            "order_customer_ID",
            "order_customer_name",
            "order_vendor_ID",
            "order_vendor_name",
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
                $order->created_at_final,
                strtoupper($order->onway_vndr_prchs_ids),
                $order->onway_cstmr_prchs_ids,
                $order->sub_products,
                $order->order_status
            ];
        }

        return SimpleXLSXGen::fromArray($resultsExport)
            ->downloadAs($filename . '.xlsx');
    }

    public function clearAllOrders()
    {
        if (auth()->user()->admin_role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        // DB::transaction(function () {
        //     DB::table('dt_order')->truncate();
        //     DB::table('dt_order_final')->truncate();
        //     DB::table('dt_order_allocation')->truncate();
        //     DB::table('dt_order_history')->truncate();
        // });

        DB::transaction(function () {
            Order::query()->delete();
            OrderFinal::query()->delete();
            OrderAllocation::query()->delete();
            OrderHistory::query()->delete();
            OrderAllocationCancel::query()->delete();
            OrderCancel::query()->delete();
            OrderHistoryArchive::query()->delete();
            OrderFinalCancel::query()->delete();
        });
        return redirect()->back()->with('success', 'All orders have been cleared.');
    }

    public function cancelOrders(Request $request)
    {
        $validated = $request->validate([
            'orderIDs' => 'required|array',
            'orderIDs.*' => 'exists:dt_order_final,order_ID'
        ]);

        // DB::transaction(function () use ($validated) {
        //     // Copy to cancel table
        //     OrderFinalCancel::insert(
        //         OrderFinal::whereIn('order_ID', $validated['orderIDs'])
        //             ->get()
        //             ->toArray()
        //     );

        //     // Delete from final table
        //     OrderFinal::whereIn('order_ID', $validated['orderIDs'])->delete();
        // });

        try {
            DB::transaction(function () use ($validated) {
                // Copy to cancel table
                $finalOrderData = OrderFinal::whereIn('order_ID', $validated['orderIDs'])
                    ->get()
                    ->map(function ($order) {
                        return [
                            'final_ID' => $order->final_ID,
                            'order_ID' => $order->order_ID,
                            'order_customer_ID' => $order->order_customer_ID,
                            'order_customer_name' => $order->order_customer_name,
                            'order_vendor_ID' => $order->order_vendor_ID,
                            'order_vendor_name' => $order->order_vendor_name,
                            'order_product_ID' => $order->order_product_ID,
                            'order_product_style' => $order->order_product_style,
                            'order_product_color' => $order->order_product_color,
                            'order_product_size' => $order->order_product_size,
                            'order_quantity' => $order->order_quantity,
                            'given_by_invntry' => $order->given_by_invntry,
                            'given_by_onway' => $order->given_by_onway,
                            'order_cost' => $order->order_cost,
                            'order_purchase_price' => $order->order_purchase_price,
                            'order_note' => $order->order_note,
                            'purchase_id' => $order->purchase_id,
                            'created_at' => $order->created_at,
                            'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                            'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                            'order_status' => $order->order_status,
                            'order_wear_date' => $order->order_wear_date,
                            'user_flag' => $order->user_flag,
                            'sub_products' => json_encode($order->sub_products ?? []),  // Manually encode sub_products as JSON
                            'order_GUID' => $order->order_GUID,
                        ];
                    })->toArray(); // Convert the collection to an array

                // Insert into final cancel table
                OrderFinalCancel::insert($finalOrderData);

                // Delete from the original final table
                OrderFinal::whereIn('order_ID', $validated['orderIDs'])->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Final order(s) cancelled successfully!'
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            // Log::error('Failed to cancel final orders', ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel final orders: ' . $e->getMessage()
            ], 500);
        }


        return response()->json(['message' => 'Order(s) cancelled successfully!']);
    }


    public function edit($id)
    {
        // Authentication check
        if (!Auth::check() || Auth::user()->admin_role === 'customer') {
            return redirect()->route('home');
        }

        $order = OrderFinal::findOrFail($id);
        $ownerCompany1 = Customer::where('cust_owner', 'Yes')->first();

        $ownerCompany = "NA";
        if ($ownerCompany1) {
            $ownerCompany = $ownerCompany1->cust_comp_name;
        }

        // Get related data
        $colors = Product::where('product_style', $order->order_product_style)
            ->where('product_status', 1)
            ->distinct('product_color')
            ->pluck('product_color');

        $sizeRange = Product::where('product_style', $order->order_product_style)
            ->first()->product_size_range;

        $costProduct = Product::where('product_style', $order->order_product_style)
            ->first()->product_wholesale_price;

        $vendors = Vendor::select('vendor_ID', 'vendor_comp_name')->get();
        $customers = Customer::orderBy('cust_comp_name', 'asc')->get(['cust_ID', 'cust_comp_name']);

        $products = Product::select('product_style', 'product_vendor_name')->distinct()->get();
        $productStyles = Product::distinct('product_style')->pluck('product_style');

        $vxp = Product::select('product_style', 'product_vendor_name', 'product_vendor_ID')
            ->distinct()
            ->get();


        $product = Product::where('product_style', $order->order_product_style)->first();


        $sub_products = $product && $product->sub_products ? $product->sub_products : [];
        $subProducts = is_string($sub_products)
            ? json_decode($sub_products, true)
            : $sub_products;

        return view('admin.final-orders.edit', compact(
            'order',
            'ownerCompany',
            'colors',
            'sizeRange',
            'costProduct',
            'vendors',
            'customers',
            'products',
            'productStyles',
            'subProducts',
            'vxp'
        ));
    }

    public function update(Request $request, $id)
    {
        $finalOrder = OrderFinal::findOrFail($id);
        $order = Order::findOrFail($finalOrder->order_ID);
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

        // Update final order
        $finalOrder->update([
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
        ]);

        // Update original order
        $order->update([
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
        ]);

        return redirect()->route('final-orders.index')->with('success', 'Order updated successfully');
    }

    public function deleteFinalOrder($orderID)
    {
        try {
            // Delete from dt_order_final
            $deletedFinal = OrderFinal::where('order_ID', $orderID)->delete();

            if ($deletedFinal) {
                // Then delete from dt_order
                $deletedOrder = Order::where('order_ID', $orderID)->delete();

                if ($deletedOrder) {
                    return redirect()->back()->with('success', 'Deleted Success.');
                }
            }

            // If deletes fail, you can decide what to do, for now just back with error
            return back()->withErrors(['error' => 'Failed to delete the order records.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
