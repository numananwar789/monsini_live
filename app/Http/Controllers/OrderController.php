<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\OrderAllocation;
use Illuminate\Support\Facades\Auth;
use App\Imports\OrdersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\OrderHistory;
use App\Models\EmailBody as EmailTemplate;
use App\Models\OrderFinal;
use App\Models\OrderAllocationCancel;
use App\Models\OrderCancel;
use App\Models\OrderHistoryArchive;
use App\Models\OrderFinalCancel;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exports\PendingOrdersExport;


use Illuminate\Http\Request;

class OrderController extends Controller
{
    // ============================================================================
    // 1) Replace the existing index() method with this trimmed version.
    //    It no longer loads every pending order — that now happens in
    //    getOrdersData() below, driven by DataTables' serverSide paging.
    // ============================================================================

    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        if (Auth::user()->admin_role == 'customer') {
            return redirect()->route('home');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

        // $orderList removed — rows are now loaded via AJAX (getOrdersData()).
        // $ownerComp is still needed here for nothing in the view itself now
        // (row coloring moved server-side into getOrdersData()), but we keep
        // it in case other parts of the view/layout reference it.

        return view('admin.orders.index', compact('ownerComp'));
    }

    // ============================================================================
    // 2) New method: AJAX endpoint consumed by the DataTables "serverSide"
    //    config on orders.index. Only ever loads $length rows (default 25),
    //    not every pending order.
    // ============================================================================

    public function getOrdersData(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $searchValue = trim((string) $request->input('search.value', ''));
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Column index -> DB column, matching the <thead> order in the Blade view.
        // 0 (checkbox), 9 (sub_products), 19 (actions) are intentionally omitted (not sortable).
        $sortableColumns = [
            1 => 'order_ID',
            2 => 'order_GUID',
            3 => 'purchase_id',
            4 => 'created_at',
            5 => 'order_customer_name',
            6 => 'order_vendor_name',
            7 => 'order_product_style',
            8 => 'order_product_color',
            10 => 'order_product_size',
            11 => 'order_quantity',
            12 => 'order_wear_date',
            13 => 'given_by_invntry',
            14 => 'given_by_onway',
            15 => 'order_cost',
            16 => 'order_purchase_price',
            17 => 'order_status',
            18 => 'user_flag',
        ];

        $base = Order::where('order_status', 'Pending');

        $recordsTotal = (clone $base)->count();

        $query = clone $base;

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('order_GUID', 'like', "%{$searchValue}%")
                    ->orWhere('purchase_id', 'like', "%{$searchValue}%")
                    ->orWhere('order_customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('order_vendor_name', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_style', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_color', 'like', "%{$searchValue}%")
                    ->orWhere('order_product_size', 'like', "%{$searchValue}%")
                    ->orWhere('order_status', 'like', "%{$searchValue}%")
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

            // Same row-coloring rules as the old Blade @php block, moved here
            // since server-side rows are now built as JSON, not <tr> markup.
            $rowStyle = '';
            if (strtoupper((string) $order->order_customer_name) === strtoupper((string) $ownerComp)) {
                $rowStyle = 'background-color: #3f4d67; color:white;';
            }
            if ($order->given_by_invntry > 0) {
                $rowStyle = 'background-color: rgb(0 100 12); color:white;';
            }
            if ($order->given_by_onway > 0) {
                $rowStyle = 'background-color: rgb(209 198 0); color:black;';
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

            $actions = '<a target="_self" class="btn btn-success mb-0 btn-sm" href="' . route('orders.edit', $order->order_ID) . '">Edit</a>';
            if ($canDelete) {
                $actions .= ' <a target="_self" class="btn btn-danger mb-0 mr-0 btn-sm" href="' . route('orders.delete-id', $order->order_ID) . '">Delete</a>';
            }
            $actions .= '<input type="hidden" name="orderID" value="' . e($order->order_ID) . '">';

            $createdDate = $order->created_at ? explode(' ', (string) $order->created_at)[0] : '';

            $data[] = [
                'checkbox' => '<input form="orderForm" class="form-check-input" type="checkbox" value="' . e($order->order_ID) . '" id="chk_order_' . e($order->order_ID) . '" name="orders[]"><label class="form-check-label" for="chk_order_' . e($order->order_ID) . '"></label>',
                'order_id' => e($order->order_ID),
                'order_guid' => e($order->order_GUID),
                'purchase_id' => e($order->purchase_id),
                'place_date' => e($createdDate),
                'customer' => strtoupper(e($order->order_customer_name)),
                'vendor' => strtoupper(e($order->order_vendor_name)),
                'style' => strtoupper(e($order->order_product_style)),
                'color' => strtoupper(e($order->order_product_color)),
                'sub_products' => e($subProductsText),
                'size' => e($order->order_product_size),
                'quantity' => e($order->order_quantity),
                'wear_date' => e($order->order_wear_date),
                'from_inventory' => e($order->given_by_invntry),
                'from_onway' => e($order->given_by_onway),
                'total_cost' => e($order->order_cost),
                'total_price' => e($order->order_purchase_price),
                'status' => e($order->order_status),
                'user' => e($order->user_flag),
                'actions' => $actions,
                // extra keys, not bound to a <th> column, read via createdRow in JS
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

    // ============================================================================
    // 3) New method: powers the "Accept Customer Orders" conflict-warning modal.
    //    This must cover ALL pending orders that would conflict (not just the
    //    current page), so it's a separate, smaller, targeted query rather than
    //    being folded into every getOrdersData() page load.
    // ============================================================================

    public function getFlaggedOrders()
    {
        $orders = Order::where('order_status', 'Pending')
            ->where('given_by_invntry', '>', 0)
            ->get(['order_ID', 'order_GUID', 'order_product_style', 'order_product_color', 'sub_products', 'order_quantity']);

        $flagged = [];
        foreach ($orders as $order) {
            $subs = [];
            if (!empty($order->sub_products)) {
                $decoded = is_array($order->sub_products)
                    ? $order->sub_products
                    : json_decode($order->sub_products, true);
                if (is_array($decoded)) {
                    $subs = $decoded;
                }
            }
            if (empty($subs)) {
                continue;
            }
            $flagged[] = [
                'id' => (string) $order->order_ID,
                'guid' => (string) $order->order_GUID,
                'style' => strtoupper((string) $order->order_product_style),
                'color' => strtoupper((string) $order->order_product_color),
                'subs' => implode(', ', $subs),
                'qty' => (string) $order->order_quantity,
            ];
        }

        return response()->json($flagged);
    }

    public function accept(Request $request)
    {
        // Validate the request
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:dt_order,order_ID'
        ]);

        $orderIds = array_filter($request->orders);
        $orderIdsString = implode(',', $orderIds);
        $ownerCompany = "";
        $ownerCompany1 = Customer::where('cust_owner', 'Yes')->first();
        if ($ownerCompany1) {
            $ownerCompany = $ownerCompany1->cust_comp_name;
        }

        // Process inventory and onway orders
        $this->processInventoryOnwayOrders($orderIdsString, $ownerCompany);

        // Process non-owner company orders
        $this->processNonOwnerOrders($orderIdsString, $ownerCompany);

        // Process owner company orders
        $this->processOwnerOrders($orderIdsString, $ownerCompany);

        return redirect()->route('orders.index')->with('success', 'Orders processed successfully');
    }

    protected function processInventoryOnwayOrders($orderIdsString, $ownerCompany)
    {
        $inventoryOnwayOrders = Order::whereIn('order_ID', explode(',', $orderIdsString))
            ->where('order_customer_name', '!=', $ownerCompany)
            ->where(function ($query) {
                $query->where('given_by_invntry', '>', 0)
                    ->orWhere('given_by_onway', '>', 0);
            })
            ->get();

        if ($inventoryOnwayOrders->isEmpty()) {
            return;
        }

        // Group orders by customer
        $groupedOrders = $inventoryOnwayOrders->groupBy('order_customer_ID');

        foreach ($groupedOrders as $customerId => $orders) {
            $this->sendOrderConfirmationEmail($customerId, $orders);

            // Update order status to 'Placed'
            Order::whereIn('order_ID', $orders->pluck('order_ID'))
                ->update(['order_status' => 'Placed']);

            // Insert into order allocations
            $this->createOrderAllocations($orders);
        }
    }

    protected function sendOrderConfirmationEmail($customerId, $orders)
    {
        $customer = Customer::findOrFail($customerId);
        $emailTemplate = EmailTemplate::where('email_role', 'stock_customer')->first();
        $totalCost = $orders->sum('order_purchase_price');

        // Prepare email content
        $emailContent = view('emails.order_confirmation', [
            'emailBody' => $emailTemplate->email_body,
            'orders' => $orders,
            'totalCost' => $totalCost
        ])->render();

        try {
            // Mail::to($customer->cust_email)
            //     ->cc(config('mail.from.address'))
            //     ->send(new OrderConfirmation($emailContent));
        } catch (Exception $e) {
            // Log email error
            \Log::error("Failed to send order confirmation email: " . $e->getMessage());
        }
    }

    protected function createOrderAllocations($orders)
    {
        $allocationData = $orders->map(function ($order) {
            return [
                'final_ID' => 0,
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
                'created_at_final' => $order->created_at,
                'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                'vendor_purchase_ID' => $order->onway_vndr_prchs_ids,
                'order_wear_date' => $order->order_wear_date,
                'sub_products' => json_encode($order->sub_products ?? []),
                'user_flag' => $order->user_flag,
                'order_GUID' => $order->order_GUID
            ];
        })->toArray();

        OrderAllocation::insert($allocationData);
    }

    protected function processNonOwnerOrders($orderIdsString, $ownerCompany)
    {
        $nonOwnerOrders = Order::whereIn('order_ID', explode(',', $orderIdsString))
            ->where('given_by_invntry', 0)
            ->where('given_by_onway', 0)
            ->where('order_customer_name', '!=', $ownerCompany)
            ->get();

        if ($nonOwnerOrders->isEmpty()) {
            return;
        }

        $this->createFinalOrders($nonOwnerOrders);

        Order::whereIn('order_ID', $nonOwnerOrders->pluck('order_ID'))
            ->update(['order_status' => 'Accepted']);
    }

    protected function processOwnerOrders($orderIdsString, $ownerCompany)
    {
        $ownerOrders = Order::whereIn('order_ID', explode(',', $orderIdsString))
            ->where('given_by_invntry', 0)
            ->where('given_by_onway', 0)
            ->where('order_customer_name', $ownerCompany)
            ->get();

        if ($ownerOrders->isEmpty()) {
            return;
        }

        $this->createFinalOrders($ownerOrders);

        Order::whereIn('order_ID', $ownerOrders->pluck('order_ID'))
            ->update(['order_status' => 'Accepted']);
    }

    protected function createFinalOrders($orders)
    {
        $finalOrderData = $orders->map(function ($order) {
            return [
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
                'order_wear_date' => $order->order_wear_date,
                'user_flag' => $order->user_flag,
                'sub_products' => json_encode($order->sub_products ?? []),
                'order_GUID' => $order->order_GUID
            ];
        })->toArray();

        OrderFinal::insert($finalOrderData);
    }

    public function cancel1(Request $request)
    {
        $orderIDs = $request->input('orders');

        if (empty($orderIDs)) {
            return redirect()->route('orders.index')->with('error', 'No orders selected');
        }

        Order::whereIn('order_ID', $orderIDs)->update(['order_status' => 'Cancelled']);

        return redirect()->route('orders.index')->with('success', 'Orders cancelled successfully');
    }

    public function cancel(Request $request)
    {
        // Validate that orderIDs exists and is an array
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer'
        ]);

        $orderIDs = $request->input('orders');
        try {
            DB::transaction(function () use ($orderIDs) {
                // Insert into cancel table
                DB::table('dt_order_cancel')->insertUsing([
                    'order_ID',
                    'order_customer_ID',
                    'order_customer_name',
                    'order_vendor_ID',
                    'order_vendor_name',
                    'order_product_ID',
                    'order_product_style',
                    'order_product_color',
                    'order_product_size',
                    'order_quantity',
                    'given_by_invntry',
                    'given_by_onway',
                    'order_cost',
                    'order_purchase_price',
                    'order_note',
                    'purchase_id',
                    'created_at',
                    'onway_vndr_prchs_ids',
                    'onway_cstmr_prchs_ids',
                    'order_status',
                    'order_wear_date',
                    'user_flag',
                    'sub_products',
                    'order_GUID'
                ], function ($query) use ($orderIDs) {
                    $query->select([
                        'order_ID',
                        'order_customer_ID',
                        'order_customer_name',
                        'order_vendor_ID',
                        'order_vendor_name',
                        'order_product_ID',
                        'order_product_style',
                        'order_product_color',
                        'order_product_size',
                        'order_quantity',
                        'given_by_invntry',
                        'given_by_onway',
                        'order_cost',
                        'order_purchase_price',
                        'order_note',
                        'purchase_id',
                        'created_at',
                        'onway_vndr_prchs_ids',
                        'onway_cstmr_prchs_ids',
                        'order_status',
                        'order_wear_date',
                        'user_flag',
                        'sub_products',
                        'order_GUID'
                    ])->from('dt_order')
                        ->whereIn('order_ID', $orderIDs);
                });

                // Delete from original table
                DB::table('dt_order')
                    ->whereIn('order_ID', $orderIDs)
                    ->delete();
            });

            return redirect()->route('orders.index')->with('success', 'Order(s) cancelled successfully!');
        } catch (Exception $e) {

            return redirect()->route('orders.index')->with('error', 'Failed to cancel orders: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $import = new OrdersImport();
        Excel::import($import, $request->file('file'));

        $message = $import->cnfrm_order_count . '/' . $import->order_count . ' orders imported successfully.';

        if (count($import->errors)) {
            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $import->errors);
        }

        return redirect()->route('orders.index')
            ->with('success', $message);
    }

    public function create()
    {
        // Check user role
        if (Auth::user()->admin_role == 'customer') {
            return redirect()->route('home');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $custList = Customer::orderBy('cust_comp_name', 'ASC')->get(['cust_ID', 'cust_comp_name']);
        $prodList = Product::select('product_style', 'product_vendor_name')
            ->distinct()
            ->orderBy('product_style', 'ASC')
            ->get();
        $vendorProductList = Product::select('product_style', 'product_vendor_name', 'product_vendor_ID')
            ->distinct()
            ->get();

        return view('admin.orders.create', compact('ownerComp', 'custList', 'prodList', 'vendorProductList'));
    }

    public function store2(Request $request)
    {
        $validated = $request->validate([
            'customers' => 'required',
            'style' => 'required',
            'color' => 'required',
            'size' => 'required',
            'quantity' => 'required|numeric|min:1',
            'purchase_id' => 'required',
            'wearDate' => 'required|date|after:today'
        ]);

        // Check for duplicate purchase ID
        $customer = Customer::where('cust_comp_name', $request->customers)->first();
        $duplicateCheck = Order::where('purchase_id', $request->purchase_id)
            ->where('order_customer_ID', $customer->cust_ID)
            ->where('purchase_ID', '<>', 'STOCK')
            ->count();

        if ($duplicateCheck > 0) {
            return back()->withInput()->with('error', 'Duplicate purchase ID. Please use a different one.');
        }

        // Get product info
        $product = Product::where('product_style', strtolower($request->style))
            ->where('product_color', $request->color)
            ->firstOrFail();

        // Calculate costs based on size
        $cost = $product->product_cost;
        $wholesalePrice = $product->product_wholesale_price;

        if ($request->size >= 18) {
            $wholesalePrice += 30;
        }
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $orderCost = $request->quantity * $wholesalePrice;
        $orderPurchasePrice = $request->quantity * $cost;

        // Check inventory and onway quantities
        $inventoryCount = Inventory::where('product_style', $request->style)
            ->where('product_color', $request->color)
            ->where('product_size', $request->size)
            ->sum('product_quantity') ?? 0;

        $onWayCount = OrderAllocation::where('order_product_style', $request->style)
            ->where('order_product_color', $request->color)
            ->where('order_product_size', $request->size)
            ->where('order_customer_name', $ownerComp)
            ->where('order_quantity', '>', 0)
            ->sum('order_quantity') ?? 0;

        // Process order based on inventory/onway availability
        // This would include the complex logic from the original PHP code
        // for handling inventory, onway allocations, etc.

        // For brevity, I'll show a simplified version:

        $onwayVendorPurId = "NA";
        $onwayCustPurId = "NA";

        if (($inventoryCount == 0 && $onWayCount == 0) || $customer->cust_comp_name == $ownerComp) {
            // Create simple order
            Order::create([
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
                'order_wear_date' => $request->wearDate,
                'user_flag' => 'admin',
                'onway_vndr_prchs_ids' => $onwayVendorPurId,
                'onway_cstmr_prchs_ids' => $onwayCustPurId,
                'order_GUID' => mt_rand(10000, 99999) . date('YmdHis')
            ]);
        } else {
            // Handle inventory/onway allocation logic
            // This would be more complex as per the original code
        }

        return redirect()->route('orders.index')->with('success', 'Order added successfully');
    }



    public function getColors($style)
    {
        $colors = Product::where('product_style', $style)
            ->select('product_color')
            ->distinct()
            ->get()
            ->pluck('product_color');

        $options = '<option value="">Choose a Color</option>';
        foreach ($colors as $color) {
            $options .= '<option value="' . $color . '">' . strtoupper($color) . '</option>';
        }

        return $options;
    }

    public function getSizes($style)
    {
        $product = Product::selectRaw('
                product_style,
                MAX(product_size_range) as product_size_range
            ')
            ->where('product_style', $style)
            ->groupBy('product_style')
            ->first();

        if (!$product || !$product->product_size_range) {
            return '<option value="">No sizes available</option>';
        }

        $range = explode('-', $product->product_size_range);

        $originalMin = trim($range[0]);
        $min = (int) trim($range[0]);
        $max = (int) trim($range[1]);

        $options = '<option value="">Choose Size</option>';

        // CASE: 00-24
        if ($originalMin === '00') {

            $options .= '<option value="00">00</option>';

            for ($i = 0; $i <= $max; $i += 2) {
                $options .= '<option value="' . $i . '">' . $i . '</option>';
            }

        } else {

            for ($i = $min; $i <= $max; $i += 2) {
                $options .= '<option value="' . $i . '">' . $i . '</option>';
            }
        }

        return $options;
    }

    public function getCost($style)
    {
        $product = Product::where('product_style', $style)->first();
        return $product ? $product->product_wholesale_price : 0;
    }

    public function getCost2(Request $request)
    {
        $style = $request->style;
        $product = Product::where('product_style', $style)->first();
        return $product ? $product->product_wholesale_price : 0;
    }


    public function edit($id)
    {
        // Authentication check
        if (!Auth::check() || Auth::user()->admin_role === 'customer') {
            return redirect()->route('home');
        }

        $order = Order::findOrFail($id);

        // Get related data
        // $ownerCompany = Customer::where('cust_owner', 'Yes')->first()->cust_comp_name;

        $colors = Product::where('product_status', 1)
            ->where('product_style', $order->order_product_style)
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


        return view('admin.orders.edit', compact(
            'order',
            // 'ownerCompany',
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
        $order = Order::findOrFail($id);

        // Get form data
        $prodStyle = $request->style;
        $prodColor = $request->color;
        $prodSize = $request->size;
        $prodQuant = $request->quantity;
        $prodPur = $request->purchase_id;
        $wearDate = $request->wearDate;

        // Get product info
        $product = Product::where('product_style', strtolower($prodStyle))
            ->where('product_color', $prodColor)
            ->first();

        // Calculate costs
        // $orderCost = ($prodSize < 18)
        //     ? $prodQuant * $product->product_wholesale_price
        //     : $prodQuant * ($product->product_wholesale_price + 30);

        $orderCost = $this->calculateOrderCost(
            $prodStyle,
            $prodSize,
            $prodQuant,
            $product->product_wholesale_price
        );


        $orderPurchasePrice = $prodQuant * $product->product_cost;

        // Get customer info
        $customer = Customer::where('cust_comp_name', $request->customers)->first();

        // Update order
        $order->update([
            'order_customer_ID' => $customer->cust_ID,
            'order_customer_name' => $customer->cust_comp_name,
            'order_vendor_ID' => $product->product_vendor_ID,
            'order_vendor_name' => $product->product_vendor_name,
            'order_product_ID' => $product->product_ID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $prodQuant,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $request->note,
            'sub_products' => $request->sub_products,
            'purchase_id' => $prodPur,
            'order_wear_date' => $wearDate
        ]);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully');
    }


    public function getSize(Request $request)
    {
        $products = Product::where('product_style', $request->style_get)->get();

        $sizeRanges = [];
        foreach ($products as $product) {
            $sizeRange = explode('-', $product->product_size_range);
            $sizeRanges[] = [
                'min' => $sizeRange[0],
                'max' => $sizeRange[1]
            ];
        }

        // If you want to return all size ranges (original behavior)
        // return response()->json($sizeRanges);

        // Or if you want to return just the first unique size range (more common use case)
        if (count($sizeRanges) > 0) {
            return response()->json($sizeRanges[0]);
        }

        return response()->json(['min' => 0, 'max' => 0]);
    }

    public function getColor(Request $request)
    {
        $colors = Product::where('product_style', $request->style)
            ->where('product_status', 1)
            ->distinct()
            ->pluck('product_color');

        $options = '<option value="">Choose A Color</option>';

        foreach ($colors as $color) {
            $trimmedColor = trim($color);
            $options .= '<option value="' . e($trimmedColor) . '">' . strtoupper(e($trimmedColor)) . '</option>';
        }

        return $options;
    }

    public function exportPending()
    {
        $filename = 'orders_download_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new PendingOrdersExport, $filename);
    }

    public function refresh()
    {
        // Authentication check
        if (!Auth::check() || Auth::user()->admin_role === 'customer') {
            return redirect()->route('home');
        }

        DB::transaction(function () {
            $pendingOrders = Order::where('order_status', 'Pending')->get();
            $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');

            foreach ($pendingOrders as $order) {

                $inventoryCount = Inventory::where('product_style', $order->order_product_style)
                    ->where('product_color', $order->order_product_color)
                    ->where('product_size', $order->order_product_size)
                    ->sum('product_quantity');


                $onWayCount = OrderAllocation::where('order_product_style', $order->order_product_style)
                    ->where('order_product_color', $order->order_product_color)
                    ->where('order_product_size', $order->order_product_size)
                    ->where('order_customer_name', $ownerComp)
                    ->where('order_quantity', '>', 0)
                    ->sum('order_quantity');

                $ordRemQuantity = $order->order_quantity - $order->given_by_invntry - $order->given_by_onway;

                // Handle inventory and onway items
                $remainingQuantity = $ordRemQuantity;

                // Process inventory first if available
                if ($inventoryCount > 0 && $remainingQuantity > 0) {
                    if ($inventoryCount > $remainingQuantity) {
                        $order->update(['given_by_invntry' => $order->given_by_invntry + $ordRemQuantity]);

                        Inventory::where('product_style', $order->order_product_style)
                            ->where('product_color', $order->order_product_color)
                            ->where('product_size', $order->order_product_size)
                            ->decrement('product_quantity', $remainingQuantity);

                        $remainingQuantity = 0;
                    } elseif ($inventoryCount < $remainingQuantity) {
                        $order->update(['given_by_invntry' => $order->given_by_invntry + $inventoryCount]);

                        // Update inventory to 0
                        Inventory::where('product_style', $order->order_product_style)
                            ->where('product_color', $order->order_product_color)
                            ->where('product_size', $order->order_product_size)
                            ->update(['product_quantity' => 0]);

                        $remainingQuantity -= $inventoryCount;
                    } else {
                        // $inventoryCount == $remainingQuantity

                        $order->update(['given_by_invntry' => $order->given_by_invntry + $inventoryCount]);

                        // Update inventory to 0
                        Inventory::where('product_style', $order->order_product_style)
                            ->where('product_color', $order->order_product_color)
                            ->where('product_size', $order->order_product_size)
                            ->update(['product_quantity' => 0]);

                        $remainingQuantity = 0;
                    }
                }

                // Process onway items if still remaining quantity
                if ($onWayCount > 0 && $remainingQuantity > 0) {
                    $onWayOrders = OrderAllocation::where('order_product_style', $order->order_product_style)
                        ->where('order_product_color', $order->order_product_color)
                        ->where('order_product_size', $order->order_product_size)
                        ->where('order_customer_name', $ownerComp)
                        ->where('order_quantity', '>', 0)
                        ->orderByDesc('order_quantity')
                        ->get();

                    foreach ($onWayOrders as $orderNow) {
                        if ($remainingQuantity <= 0)
                            break;

                        if ($orderNow->order_quantity > $remainingQuantity) {

                            $order->update(['given_by_onway' => $order->given_by_onway + $remainingQuantity]);
                            // Update allocation quantity
                            $orderNow->decrement('order_quantity', $remainingQuantity);

                            $remainingQuantity = 0;
                        } elseif ($orderNow->order_quantity < $remainingQuantity) {
                            $order->update(['given_by_onway' => $order->given_by_onway + $orderNow->order_quantity]);
                            // Delete related records
                            Order::where('order_ID', $orderNow->order_ID)->delete();
                            OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                            $orderNow->delete();

                            $remainingQuantity -= $orderNow->order_quantity;
                        } else { // $orderNow->order_quantity == $remainingQuantity
                            $order->update(['given_by_onway' => $order->given_by_onway + $orderNow->order_quantity]);
                            // Delete related records
                            Order::where('order_ID', $orderNow->order_ID)->delete();
                            OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                            $orderNow->delete();

                            $remainingQuantity = 0;
                        }
                    }
                }
            }
        });

        return redirect()->route('orders.index')->with('success', 'Orders refreshed successfully');
    }

    public function clearAll()
    {
        // Authentication and authorization check
        if (!Auth::check() || Auth::user()->admin_role !== 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

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
        return redirect()->route('orders.index')->with('success', 'All orders cleared successfully');
    }




    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'style' => 'required',
            'color' => 'required',
            'size' => 'required|numeric',
            'quantity' => 'required|numeric',
            'purchase_id' => 'required',
            'wearDate' => 'required',
            'customers' => 'required',
            'note' => 'nullable',

            'sub_products' => 'nullable|array',
            'sub_products.*' => 'string',

        ]);


        // Get form data
        $sub_products = $validated['sub_products'] ?? [];
        $prodStyle = strtolower($request->input('style'));
        $prodColor = $request->input('color');
        $prodSize = $request->input('size');
        $prodQuant = (int) $request->input('quantity');
        $prodPur = $request->input('purchase_id');
        $wearDateNow = $request->input('wearDate');
        $orderNote = $request->input('note');



        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name'); // Assuming you have this in your config

        // Get product info
        $prodInfo = Product::where('product_style', $prodStyle)
            ->where('product_color', $prodColor)
            ->first();

        if (!$prodInfo) {
            return back()->with('error', 'Product not found');
        }

        $vendorID = $prodInfo->product_vendor_ID;
        $vendorName = $prodInfo->product_vendor_name;
        $productID = $prodInfo->product_ID;

        // Calculate order cost based on size
        // $orderCost = $prodSize < 18
        //     ? $prodQuant * $prodInfo->product_wholesale_price
        //     : $prodQuant * ($prodInfo->product_wholesale_price + 30);

        $basePrice = $prodInfo->product_wholesale_price;
        $addition = 0;

        if ($prodSize >= 18) {
            if (strtoupper(substr($prodStyle, 0, 1)) === 'B') {
                $addition = 60;
            } else {
                $addition = 30;
            }
        }

        $orderCost = $prodQuant * ($basePrice + $addition);

        $orderPurchasePrice = $prodQuant * $prodInfo->product_cost;

        $onwayVendorPurId = "NA";
        $onwayCustPurId = "NA";

        // Get customer info
        $custInfo = Customer::where('cust_comp_name', $request->input('customers'))->first();
        if (!$custInfo) {
            return back()->with('error', 'Customer not found');
        }

        $customer_ID = $custInfo->cust_ID;
        $customerName = $custInfo->cust_comp_name;

        // Check for previous purchase IDs
        $prevPurCheck = Order::where('purchase_id', $prodPur)
            ->where('order_customer_ID', $customer_ID)
            ->where('purchase_ID', '!=', 'STOCK')
            ->count();

        if ($prevPurCheck > 0) {
            return back()->with('error', 'Duplicate purchase ID(s). Please use a different one.');
        }

        // Begin database transaction
        DB::beginTransaction();

        try {
            // Check inventory count
            $inventoryCount = Inventory::where('product_style', $prodStyle)
                ->where('product_color', $prodColor)
                ->where('product_size', $prodSize)
                ->sum('product_quantity');

            // Check on way count
            $onWayCount = OrderAllocation::where('order_product_style', $prodStyle)
                ->where('order_product_color', $prodColor)
                ->where('order_product_size', $prodSize)
                ->where('order_customer_name', $ownerComp)
                ->where('order_quantity', '>', 0)
                ->sum('order_quantity');

            if (($inventoryCount == 0 && $onWayCount == 0) || $customerName == $ownerComp) {
                // Insert simple orders or company orders without any onway or inventory items
                Order::create([
                    'order_customer_ID' => $customer_ID,
                    'order_customer_name' => $customerName,
                    'order_vendor_ID' => $vendorID,
                    'order_vendor_name' => $vendorName,
                    'order_product_ID' => $productID,
                    'order_product_style' => $prodStyle,
                    'order_product_color' => $prodColor,
                    'order_product_size' => $prodSize,
                    'order_quantity' => $prodQuant,
                    'order_cost' => $orderPurchasePrice,
                    'order_purchase_price' => $orderCost,
                    'order_note' => $orderNote,
                    'purchase_id' => $prodPur,
                    'onway_vndr_prchs_ids' => $onwayVendorPurId,
                    'onway_cstmr_prchs_ids' => $onwayCustPurId,
                    'order_wear_date' => $wearDateNow,
                    'sub_products' => $sub_products,
                    'user_flag' => 'admin',
                    'order_GUID' => $this->generateOrderGuid(),
                    'created_at' => now(),

                ]);

                DB::commit();
                return redirect()->route('orders.index')->with('success', 'Order created successfully');
            } else {
                // Handle inventory and onway items
                $remainingQuantity = $prodQuant;

                // Process inventory first if available
                if ($inventoryCount > 0 && $remainingQuantity > 0) {
                    if ($inventoryCount > $remainingQuantity) {
                        $this->createOrderFromInventory(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $remainingQuantity,
                            $prodInfo,
                            $orderNote,
                            $prodPur,
                            $onwayVendorPurId,
                            $onwayCustPurId,
                            $wearDateNow,
                            $remainingQuantity,
                            $sub_products,
                        );

                        // Update inventory
                        Inventory::where('product_style', $prodStyle)
                            ->where('product_color', $prodColor)
                            ->where('product_size', $prodSize)
                            ->decrement('product_quantity', $remainingQuantity);

                        $remainingQuantity = 0;
                    } elseif ($inventoryCount < $remainingQuantity) {
                        $this->createOrderFromInventory(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $inventoryCount,
                            $prodInfo,
                            $orderNote,
                            $prodPur,
                            $onwayVendorPurId,
                            $onwayCustPurId,
                            $wearDateNow,
                            $inventoryCount,
                            $sub_products,
                        );

                        // Update inventory to 0
                        Inventory::where('product_style', $prodStyle)
                            ->where('product_color', $prodColor)
                            ->where('product_size', $prodSize)
                            ->update(['product_quantity' => 0]);

                        $remainingQuantity -= $inventoryCount;
                    } else { // $inventoryCount == $remainingQuantity
                        $this->createOrderFromInventory(
                            $customer_ID,
                            $customerName,
                            $vendorID,
                            $vendorName,
                            $productID,
                            $prodStyle,
                            $prodColor,
                            $prodSize,
                            $remainingQuantity,
                            $prodInfo,
                            $orderNote,
                            $prodPur,
                            $onwayVendorPurId,
                            $onwayCustPurId,
                            $wearDateNow,
                            $remainingQuantity,
                            $sub_products,
                        );

                        // Update inventory to 0
                        Inventory::where('product_style', $prodStyle)
                            ->where('product_color', $prodColor)
                            ->where('product_size', $prodSize)
                            ->update(['product_quantity' => 0]);

                        $remainingQuantity = 0;
                    }
                }

                // Process onway items if still remaining quantity
                if ($onWayCount > 0 && $remainingQuantity > 0) {
                    $onWayOrders = OrderAllocation::where('order_product_style', $prodStyle)
                        ->where('order_product_color', $prodColor)
                        ->where('order_product_size', $prodSize)
                        ->where('order_customer_name', $ownerComp)
                        ->where('order_quantity', '>', 0)
                        ->orderByDesc('order_quantity')
                        ->get();

                    foreach ($onWayOrders as $orderNow) {
                        if ($remainingQuantity <= 0)
                            break;

                        if ($orderNow->order_quantity > $remainingQuantity) {
                            $this->createOrderFromOnway(
                                $customer_ID,
                                $customerName,
                                $vendorID,
                                $vendorName,
                                $productID,
                                $prodStyle,
                                $prodColor,
                                $prodSize,
                                $remainingQuantity,
                                $prodInfo,
                                $orderNote,
                                $prodPur,
                                $orderNow->vendor_purchase_ID,
                                $orderNow->purchase_id,
                                $wearDateNow,
                                $remainingQuantity,
                                $sub_products,
                            );

                            // Update allocation quantity
                            $orderNow->decrement('order_quantity', $remainingQuantity);
                            $remainingQuantity = 0;
                        } elseif ($orderNow->order_quantity < $remainingQuantity) {
                            $this->createOrderFromOnway(
                                $customer_ID,
                                $customerName,
                                $vendorID,
                                $vendorName,
                                $productID,
                                $prodStyle,
                                $prodColor,
                                $prodSize,
                                $orderNow->order_quantity,
                                $prodInfo,
                                $orderNote,
                                $prodPur,
                                $orderNow->vendor_purchase_ID,
                                $orderNow->purchase_id,
                                $wearDateNow,
                                $orderNow->order_quantity,
                                $sub_products,
                            );

                            // Delete related records
                            Order::where('order_ID', $orderNow->order_ID)->delete();
                            OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                            $orderNow->delete();

                            $remainingQuantity -= $orderNow->order_quantity;
                        } else { // $orderNow->order_quantity == $remainingQuantity
                            $this->createOrderFromOnway(
                                $customer_ID,
                                $customerName,
                                $vendorID,
                                $vendorName,
                                $productID,
                                $prodStyle,
                                $prodColor,
                                $prodSize,
                                $remainingQuantity,
                                $prodInfo,
                                $orderNote,
                                $prodPur,
                                $orderNow->vendor_purchase_ID,
                                $orderNow->purchase_id,
                                $wearDateNow,
                                $remainingQuantity,
                                $sub_products,
                            );

                            // Delete related records
                            Order::where('order_ID', $orderNow->order_ID)->delete();
                            OrderFinal::where('order_ID', $orderNow->order_ID)->delete();
                            $orderNow->delete();

                            $remainingQuantity = 0;
                        }
                    }
                }

                // If still remaining quantity, create normal order
                if ($remainingQuantity > 0) {
                    $this->createNormalOrder(
                        $customer_ID,
                        $customerName,
                        $vendorID,
                        $vendorName,
                        $productID,
                        $prodStyle,
                        $prodColor,
                        $prodSize,
                        $remainingQuantity,
                        $prodInfo,
                        $orderNote,
                        $prodPur,
                        $onwayVendorPurId,
                        $onwayCustPurId,
                        $wearDateNow,
                        $sub_products,
                    );
                }

                DB::commit();
                return redirect()->route('orders.index')->with('success', 'Order created successfully');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to create order from inventory
     */
    private function createOrderFromInventory(
        $customer_ID,
        $customerName,
        $vendorID,
        $vendorName,
        $productID,
        $prodStyle,
        $prodColor,
        $prodSize,
        $quantity,
        $prodInfo,
        $orderNote,
        $prodPur,
        $onwayVendorPurId,
        $onwayCustPurId,
        $wearDateNow,
        $givenByInventory,
        $sub_products,
    ) {
        $orderCost = $this->calculateOrderCost(
            $prodStyle,
            $prodSize,
            $quantity,
            $prodInfo->product_wholesale_price
        );

        $orderPurchasePrice = $quantity * $prodInfo->product_cost;

        Order::create([
            'order_customer_ID' => $customer_ID,
            'order_customer_name' => $customerName,
            'order_vendor_ID' => $vendorID,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $productID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $orderNote,
            'purchase_id' => $prodPur,
            'onway_vndr_prchs_ids' => $onwayVendorPurId,
            'onway_cstmr_prchs_ids' => $onwayCustPurId,
            'given_by_invntry' => $givenByInventory,
            'order_wear_date' => $wearDateNow,
            'user_flag' => 'admin',
            'sub_products' => $sub_products,
            'order_GUID' => $this->generateOrderGuid(),
            'created_at' => now(),
        ]);
    }

    /**
     * Helper method to create order from onway allocation
     */
    private function createOrderFromOnway(
        $customer_ID,
        $customerName,
        $vendorID,
        $vendorName,
        $productID,
        $prodStyle,
        $prodColor,
        $prodSize,
        $quantity,
        $prodInfo,
        $orderNote,
        $prodPur,
        $vendorPurchaseId,
        $customerPurchaseId,
        $wearDateNow,
        $givenByOnway,
        $sub_products,
    ) {
        // $orderCost = $prodSize < 18
        //     ? $quantity * $prodInfo->product_wholesale_price
        //     : $quantity * ($prodInfo->product_wholesale_price + 30);

        $orderCost = $this->calculateOrderCost(
            $prodStyle,
            $prodSize,
            $quantity,
            $prodInfo->product_wholesale_price
        );
        $orderPurchasePrice = $quantity * $prodInfo->product_cost;

        Order::create([
            'order_customer_ID' => $customer_ID,
            'order_customer_name' => $customerName,
            'order_vendor_ID' => $vendorID,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $productID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $orderNote,
            'purchase_id' => $prodPur,
            'given_by_onway' => $givenByOnway,
            'onway_vndr_prchs_ids' => $vendorPurchaseId,
            'onway_cstmr_prchs_ids' => $customerPurchaseId,
            'order_wear_date' => $wearDateNow,
            'user_flag' => 'admin',
            'sub_products' => $sub_products,
            'order_GUID' => $this->generateOrderGuid(),
            'created_at' => now(),
        ]);
    }

    /**
     * Helper method to create normal order
     */
    private function createNormalOrder(
        $customer_ID,
        $customerName,
        $vendorID,
        $vendorName,
        $productID,
        $prodStyle,
        $prodColor,
        $prodSize,
        $quantity,
        $prodInfo,
        $orderNote,
        $prodPur,
        $onwayVendorPurId,
        $onwayCustPurId,
        $wearDateNow,
        $sub_products,
    ) {
        $orderCost = $prodSize < 18
            ? $quantity * $prodInfo->product_wholesale_price
            : $quantity * ($prodInfo->product_wholesale_price + 30);

        $orderPurchasePrice = $quantity * $prodInfo->product_cost;

        Order::create([
            'order_customer_ID' => $customer_ID,
            'order_customer_name' => $customerName,
            'order_vendor_ID' => $vendorID,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $productID,
            'order_product_style' => $prodStyle,
            'order_product_color' => $prodColor,
            'order_product_size' => $prodSize,
            'order_quantity' => $quantity,
            'order_cost' => $orderPurchasePrice,
            'order_purchase_price' => $orderCost,
            'order_note' => $orderNote,
            'purchase_id' => $prodPur,
            'onway_vndr_prchs_ids' => $onwayVendorPurId,
            'onway_cstmr_prchs_ids' => $onwayCustPurId,
            'order_wear_date' => $wearDateNow,
            'sub_products' => $sub_products,
            'user_flag' => 'admin',
            'order_GUID' => $this->generateOrderGuid(),
            'created_at' => now(),
        ]);
    }

    private function calculateOrderCost($style, $size, $quantity, $basePrice)
    {
        $addition = 0;

        if ((int) $size >= 18) {
            $addition = strtoupper(substr(trim($style), 0, 1)) === 'B' ? 60 : 30;
        }

        return (int) $quantity * ((float) $basePrice + $addition);
    }

    /**
     * Generate a unique order GUID
     */
    private function generateOrderGuid()
    {
        return rand(10000, 99999) . now()->format('YmdHis');
    }

    public function deleteOrder($id)
    {
        // Check if user is authenticated and is not a customer
        if (Auth::user()->admin_role == 'customer') {
            return redirect()->route('home');
        }

        $fileToDel = $id;

        // Get owner company data
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $ownerCompID = Customer::where('cust_owner', 'Yes')->value('cust_ID');

        try {
            $order = Order::findOrFail($fileToDel);

            $onWayCount = $order->given_by_onway;
            $inventoryCount = $order->given_by_invntry;

            if ($onWayCount == 0 && $inventoryCount == 0) {
                // Just delete order
                $order->delete();
                return redirect()->route('orders.index'); // adjust route name
            } else if ($onWayCount > 0) {
                // Insert into dt_order_allocation based on order
                OrderAllocation::create([
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
                    'created_at' => $order->created_at,
                    'created_at_final' => $order->created_at,
                    'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                    'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                    'order_wear_date' => $order->order_wear_date,
                    'user_flag' => $order->user_flag,
                ]);

                // Delete original order
                $order->delete();

                return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
            } else if ($inventoryCount > 0) {
                // Update inventory quantity
                $inventory = Inventory::where('product_style', $order->order_product_style)
                    ->where('product_color', $order->order_product_color)
                    ->where('product_size', $order->order_product_size)
                    ->first();

                if ($inventory) {
                    $inventory->product_quantity += $order->order_quantity;
                    $inventory->save();
                }

                // Delete order
                $order->delete();

                return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
            }

        } catch (Exception $e) {
            // Log error and show message (or handle as needed)
            return back()->withErrors([$e->getMessage()]);
        }
    }
}
