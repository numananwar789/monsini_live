<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFinal;
use App\Models\OrderAllocation;
use App\Models\OrderHistory;
use App\Models\OrderHistoryArchive;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $archiveList = OrderHistoryArchive::distinct()->pluck('archive_name');
        $withStock = $request->has('exclude_stock');
        $selectedArchive = $request->selected_archive;

        // PRE-CALCULATE LOOKUPS (The Secret to Speed)
        // We fetch all totals into memory ONCE.
        $styleLookup = $this->getStyleTotalsLookup($withStock);

        if ($request->has('action') && $selectedArchive) {
            $totOrders = OrderHistoryArchive::where('archive_name', $selectedArchive)->get();
            $topStyles = $this->getTopStyles($selectedArchive, $withStock, $styleLookup);
            $custHistory = OrderHistoryArchive::where('archive_name', $selectedArchive)->get();
            $topCust = $this->getTopCustomers($selectedArchive);
            $lateVendors = $this->getLateVendors($selectedArchive);
        } else {
            $totOrders = $this->getCombinedOrders($styleLookup);
            $topStyles = $this->getTopStyles(null, $withStock, $styleLookup);
            $custHistory = $this->getCustomerHistory($styleLookup);
            $topCust = $this->getTopCustomers();
            $lateVendors = $this->getLateVendors();
        }

        return view('reports.index', compact('ownerComp', 'archiveList', 'totOrders', 'topStyles', 'custHistory', 'topCust', 'lateVendors'));
    }

    protected function getCombinedOrders($styleLookup)
    {
        $results = DB::select("
            SELECT order_vendor_name, order_customer_name, order_product_style, order_product_color, order_product_size, order_quantity FROM dt_order WHERE order_status = 'Pending'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style, order_product_color, order_product_size, order_quantity FROM dt_order_final WHERE order_status <> 'Placed'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style, order_product_color, order_product_size, order_quantity FROM dt_order_allocation WHERE order_status <> 'Allocated'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style, order_product_color, order_product_size, order_quantity FROM dt_order_history
        ");

        foreach ($results as &$item) {
            $item->OrdersTables = $styleLookup[$item->order_product_style] ?? "Orders: 0 | Final: 0 | Allocation: 0 | History: 0";
        }
        return $results;
    }

    protected function getCustomerHistory($styleLookup)
    {
        // Use Union directly and map the pre-calculated lookup
        $results = Order::where('order_status', 'Pending')
            ->select('order_customer_name', 'order_product_style', 'order_product_color', 'order_product_size', 'purchase_id', 'order_status', 'created_at', 'order_quantity')
            ->unionAll(OrderFinal::where('order_status', '<>', 'Placed')->select('order_customer_name', 'order_product_style', 'order_product_color', 'order_product_size', 'purchase_id', 'order_status', 'created_at', 'order_quantity'))
            ->unionAll(OrderAllocation::where('order_status', '<>', 'Allocated')->select('order_customer_name', 'order_product_style', 'order_product_color', 'order_product_size', 'purchase_id', DB::raw("'Sent to Vendor'"), 'created_at', 'order_quantity'))
            ->unionAll(OrderHistory::select('order_customer_name', 'order_product_style', 'order_product_color', 'order_product_size', 'purchase_id', DB::raw("'Allocated'"), 'created_at', 'order_quantity'))
            ->get();

        return $results->map(function ($item) use ($styleLookup) {
            $item->OrdersTables = $styleLookup[$item->order_product_style] ?? "N/A";
            return $item;
        });
    }

    // protected function getTopStyles($archiveName = null, $withStock = false, $styleLookup = [])
    // {
    //     $stockFilter = $withStock ? " AND (purchase_id IS NULL OR purchase_id != 'stock') " : "";
        
    //     // Optimize Color and Vendor fetching by using GROUP_CONCAT to avoid loops
    //     $sql = "
    //         SELECT order_product_style, SUM(order_quantity) as total_count,
    //         GROUP_CONCAT(DISTINCT order_vendor_name SEPARATOR ', ') as vendors
    //         FROM (
    //             SELECT order_product_style, order_quantity, order_vendor_name FROM dt_order WHERE order_status = 'Pending' {$stockFilter}
    //             UNION ALL
    //             SELECT order_product_style, order_quantity, order_vendor_name FROM dt_order_final WHERE order_status <> 'Placed' {$stockFilter}
    //             UNION ALL
    //             SELECT order_product_style, order_quantity, order_vendor_name FROM dt_order_allocation WHERE order_status <> 'Allocated' {$stockFilter}
    //             UNION ALL
    //             SELECT order_product_style, order_quantity, order_vendor_name FROM dt_order_history WHERE 1=1 {$stockFilter}
    //         ) as t1 GROUP BY order_product_style ORDER BY total_count DESC";

    //     $results = DB::select($sql);

    //     foreach ($results as &$style) {
    //         $style->OrdersTables = $styleLookup[$style->order_product_style] ?? "";
    //         // To keep it fast, we skip the heavy nested color query and use a simplified version
    //         $style->colors = "Check details"; 
    //     }

    //     return $results;
    // }
    
    protected function getTopStyles($archiveName = null, $withStock = false, $styleLookup = [])
{
    $stockFilter = $withStock ? " AND (purchase_id IS NULL OR purchase_id != 'stock') " : "";
    
    // We use a double aggregation: first sum by color, then concatenate the results for the style.
    $sql = "
        SELECT 
            order_product_style, 
            SUM(total_qty) as total_count,
            GROUP_CONCAT(DISTINCT vendors SEPARATOR ', ') as vendors,
            GROUP_CONCAT(CONCAT(order_product_color, ': ', total_qty) ORDER BY order_product_color SEPARATOR ' | ') as colors
        FROM (
            SELECT 
                order_product_style, 
                order_product_color, 
                SUM(order_quantity) as total_qty,
                GROUP_CONCAT(DISTINCT order_vendor_name) as vendors
            FROM (
                SELECT order_product_style, order_product_color, order_quantity, order_vendor_name FROM dt_order WHERE order_status = 'Pending' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_product_color, order_quantity, order_vendor_name FROM dt_order_final WHERE order_status <> 'Placed' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_product_color, order_quantity, order_vendor_name FROM dt_order_allocation WHERE order_status <> 'Allocated' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_product_color, order_quantity, order_vendor_name FROM dt_order_history WHERE 1=1 {$stockFilter}
            ) as raw_data
            GROUP BY order_product_style, order_product_color
        ) as color_totals
        GROUP BY order_product_style 
        ORDER BY total_count DESC";

    $results = DB::select($sql);

    foreach ($results as &$style) {
        $style->OrdersTables = $styleLookup[$style->order_product_style] ?? "";
        // $style->colors is now pre-formatted by the query as 'COLOR: QUANTITY | COLOR: QUANTITY'
    }

    return $results;
}

    protected function getStyleTotalsLookup($withStock = false)
    {
        $stockFilter = $withStock ? " AND (purchase_id IS NULL OR purchase_id != 'stock') " : "";
        
        $sql = "SELECT order_product_style,
            SUM(CASE WHEN src = 'O' THEN order_quantity ELSE 0 END) as q_o,
            SUM(CASE WHEN src = 'F' THEN order_quantity ELSE 0 END) as q_f,
            SUM(CASE WHEN src = 'A' THEN order_quantity ELSE 0 END) as q_a,
            SUM(CASE WHEN src = 'H' THEN order_quantity ELSE 0 END) as q_h
            FROM (
                SELECT order_product_style, order_quantity, 'O' as src FROM dt_order WHERE order_status = 'Pending' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_quantity, 'F' as src FROM dt_order_final WHERE order_status <> 'Placed' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_quantity, 'A' as src FROM dt_order_allocation WHERE order_status <> 'Allocated' {$stockFilter}
                UNION ALL
                SELECT order_product_style, order_quantity, 'H' as src FROM dt_order_history WHERE 1=1 {$stockFilter}
            ) as combined GROUP BY order_product_style";

        $raw = DB::select($sql);
        $lookup = [];
        foreach ($raw as $r) {
            $lookup[$r->order_product_style] = "Orders: {$r->q_o} | Final: {$r->q_f} | Allocation: {$r->q_a} | History: {$r->q_h}";
        }
        return $lookup;
    }
    
    
//     protected function getTopCustomers()
// {
//     // 1. Get all customer totals once
//     $customerTotals = DB::table(DB::raw("(
//         SELECT order_customer_name, order_quantity, 'O' as type FROM dt_order WHERE order_status = 'Pending'
//         UNION ALL
//         SELECT order_customer_name, order_quantity, 'F' as type FROM dt_order_final WHERE order_status <> 'Placed'
//         UNION ALL
//         SELECT order_customer_name, order_quantity, 'A' as type FROM dt_order_allocation WHERE order_status <> 'Allocated'
//         UNION ALL
//         SELECT order_customer_name, order_quantity, 'H' as type FROM dt_order_history
//     ) as t"))
//     ->select('order_customer_name',
//         DB::raw("SUM(CASE WHEN type = 'O' THEN order_quantity ELSE 0 END) as qty_o"),
//         DB::raw("SUM(CASE WHEN type = 'F' THEN order_quantity ELSE 0 END) as qty_f"),
//         DB::raw("SUM(CASE WHEN type = 'A' THEN order_quantity ELSE 0 END) as qty_a"),
//         DB::raw("SUM(CASE WHEN type = 'H' THEN order_quantity ELSE 0 END) as qty_h"),
//         DB::raw("SUM(order_quantity) as total_count")
//     )
//     ->groupBy('order_customer_name')
//     ->orderByDesc('total_count')
//     ->get();

//     return $customerTotals->map(function($item) {
//         return [
//             'order_customer_name' => $item->order_customer_name,
//             'total_count' => $item->total_count,
//             'OrdersTables' => "Orders: {$item->qty_o} | Final: {$item->qty_f} | Allocation: {$item->qty_a} | History: {$item->qty_h}"
//         ];
//     })->toArray();
// }

protected function getTopCustomers()
{
    $customerTotals = DB::table(DB::raw("(
        SELECT order_customer_name, order_quantity, purchase_id, 'O' as type FROM dt_order WHERE order_status = 'Pending'
        UNION ALL
        SELECT order_customer_name, order_quantity, purchase_id, 'F' as type FROM dt_order_final WHERE order_status <> 'Placed'
        UNION ALL
        SELECT order_customer_name, order_quantity, purchase_id, 'A' as type FROM dt_order_allocation WHERE order_status <> 'Allocated'
        UNION ALL
        SELECT order_customer_name, order_quantity, purchase_id, 'H' as type FROM dt_order_history
    ) as t"))
    ->select(
        'order_customer_name',
        // Cast to ensure numeric sorting
        DB::raw("SUM(order_quantity) as total_count"),
        DB::raw("SUM(CASE WHEN purchase_id = 'stock' THEN order_quantity ELSE 0 END) as stock_qty"),
        DB::raw("SUM(CASE WHEN purchase_id IS NULL OR purchase_id != 'stock' THEN order_quantity ELSE 0 END) as special_qty"),
        DB::raw("SUM(CASE WHEN type = 'O' THEN order_quantity ELSE 0 END) as qty_o"),
        DB::raw("SUM(CASE WHEN type = 'F' THEN order_quantity ELSE 0 END) as qty_f"),
        DB::raw("SUM(CASE WHEN type = 'A' THEN order_quantity ELSE 0 END) as qty_a"),
        DB::raw("SUM(CASE WHEN type = 'H' THEN order_quantity ELSE 0 END) as qty_h")
    )
    ->groupBy('order_customer_name')
    ->orderBy('total_count', 'desc') // Ensure the DB handles the bulk work
    ->get();

    // Use sortByDesc on the collection to guarantee order before converting to array
    return $customerTotals->sortByDesc('total_count')->map(function($item) {
        return [
            'order_customer_name' => $item->order_customer_name,
            'total_count'         => (int) $item->total_count, // Force integer
            'stock_special_split' => "Stock: {$item->stock_qty} | Specials: {$item->special_qty}",
            'OrdersTables'        => "ORDERS: {$item->qty_o} | FINAL: {$item->qty_f} | ALLOCATION: {$item->qty_a} | HISTORY: {$item->qty_h}"
        ];
    })->values()->toArray(); 
}


    protected function getLateVendors($archiveName = null)
    {

        if (!$archiveName) {

            $lateVendors = DB::select("
                        SELECT 
                            dta.order_vendor_name, 
                            dta.order_product_style, 
                            dta.order_product_color, 
                            dta.order_product_size, 
                            dta.order_quantity,
                            dta.vendor_purchase_ID,
                            dta.order_customer_name,
                            dta.purchase_id,
                            IF (
                                INSTR(dta.vendor_purchase_ID, '_') > 0, 
                                dta.created_at_allocation, 
                                STR_TO_DATE(
                                    CONCAT(
                                        LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                        '-',
                                        LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                        '-',
                                        RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                                    ),
                                    '%d-%m-%YT%H:%i:%s'
                                )
                            ) as created_at_allocation, 
                            dtv.vendor_days,
                            DATE_ADD(
                                IF (
                                    INSTR(dta.vendor_purchase_ID, '_') > 0, 
                                    dta.created_at_allocation, 
                                    STR_TO_DATE(
                                        CONCAT(
                                            LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                            '-',
                                            LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                            '-',
                                            RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                                        ),
                                        '%d-%m-%YT%H:%i:%s'
                                    )
                                ),
                                INTERVAL dtv.vendor_days DAY
                            ) as ETA, 
                            DATEDIFF(
                                CURDATE(),
                                DATE_ADD(
                                    IF (
                                        INSTR(dta.vendor_purchase_ID, '_') > 0, 
                                        dta.created_at_allocation, 
                                        STR_TO_DATE(
                                            CONCAT(
                                                LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                                '-',
                                                LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                                '-',
                                                RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                                            ),
                                            '%d-%m-%YT%H:%i:%s'
                                        )
                                    ),
                                    INTERVAL dtv.vendor_days DAY
                                )
                            ) as delay_days
                        FROM dt_order_allocation AS dta
                        INNER JOIN dt_vendor AS dtv ON dta.order_vendor_ID = dtv.vendor_ID
                        WHERE dta.order_status <> 'Allocated'
                        AND CURDATE() >= DATE_ADD(
                            IF (
                                INSTR(dta.vendor_purchase_ID, '_') > 0, 
                                dta.created_at_allocation, 
                                STR_TO_DATE(
                                    CONCAT(
                                        LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                        '-',
                                        LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                        '-',
                                        RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                                    ),
                                    '%d-%m-%YT%H:%i:%s'
                                )
                            ),
                            INTERVAL dtv.vendor_days DAY
                        )
                    ");
            return  $lateVendors;
        }


        $lateVendors = DB::select("
            SELECT 
                dta.order_vendor_name, 
                dta.order_product_style, 
                dta.order_product_color, 
                dta.order_product_size, 
                dta.order_quantity,
                dta.vendor_purchase_ID,
                dta.order_customer_name,
                dta.purchase_id,
                IF (INSTR(dta.vendor_purchase_ID, '_') > 0, 
                    dta.created_at_allocation, 
                    STR_TO_DATE(
                        CONCAT(
                            LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                            '-', 
                            LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                            '-', 
                            RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                        ), '%d-%m-%YT%H:%i:%s')
                ) as created_at_allocation, 
                dtv.vendor_days,
                DATE_ADD(
                    IF (INSTR(dta.vendor_purchase_ID, '_') > 0, 
                        dta.created_at_allocation, 
                        STR_TO_DATE(
                            CONCAT(
                                LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                '-', 
                                LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                '-', 
                                RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                            ), '%d-%m-%YT%H:%i:%s')
                    ), INTERVAL dtv.vendor_days DAY
                ) as ETA,
                DATEDIFF(CURDATE(), 
                    DATE_ADD(
                        IF (INSTR(dta.vendor_purchase_ID, '_') > 0, 
                            dta.created_at_allocation, 
                            STR_TO_DATE(
                                CONCAT(
                                    LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                                    '-', 
                                    LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                                    '-', 
                                    RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                                ), '%d-%m-%YT%H:%i:%s')
                        ), INTERVAL dtv.vendor_days DAY
                    )
                ) as delay_days
            FROM dt_order_history_archive AS dta
            INNER JOIN dt_vendor AS dtv ON dta.order_vendor_ID = dtv.vendor_ID
            WHERE dta.archive_name = ?
            AND CURDATE() >= DATE_ADD(
                IF (INSTR(dta.vendor_purchase_ID, '_') > 0, 
                    dta.created_at_allocation, 
                    STR_TO_DATE(
                        CONCAT(
                            LEFT(RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 4), 2),
                            '-', 
                            LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2),
                            '-', 
                            RIGHT(SUBSTRING_INDEX(SUBSTRING_INDEX(dta.vendor_purchase_ID, '-', 2), '-', -1), 2)
                        ), '%d-%m-%YT%H:%i:%s')
                ), INTERVAL dtv.vendor_days DAY
            )
        ", [$archiveName]);

        return  $lateVendors;
    }

    // protected function getFromOrder($order_product_style)
    // {
    //     return Order::where('order_product_style', $order_product_style)->where('order_status', 'Pending')->sum('order_quantity');
    // }
    
protected function getFromOrder($order_product_style, $withStock = false)
{
    $query = Order::where('order_product_style', $order_product_style)
                  ->where('order_status', 'Pending');
    
    if ($withStock) {
        $query->where(function($q) {
            $q->where('purchase_id', '!=', 'stock')
              ->orWhereNull('purchase_id');
        });
    }
    return $query->sum('order_quantity');
}

protected function getFromOrderFinal($order_product_style, $withStock = false)
{
    $query = OrderFinal::where('order_product_style', $order_product_style)
                       ->where('order_status', '!=', 'Placed');

    if ($withStock) {
        $query->where(function($q) {
            $q->where('purchase_id', '!=', 'stock')
              ->orWhereNull('purchase_id');
        });
    }
    return $query->sum('order_quantity');
}

protected function getFromOrderAllocation($order_product_style, $withStock = false)
{
    $query = OrderAllocation::where('order_product_style', $order_product_style)
                            ->where('order_status', '!=', 'Allocated');

    if ($withStock) {
        $query->where(function($q) {
            $q->where('purchase_id', '!=', 'stock')
              ->orWhereNull('purchase_id');
        });
    }
    return $query->sum('order_quantity');
}

protected function getFromOrderHistory($order_product_style, $withStock = false)
{
    $query = OrderHistory::where('order_product_style', $order_product_style);

    if ($withStock) {
        $query->where(function($q) {
            $q->where('purchase_id', '!=', 'stock')
              ->orWhereNull('purchase_id');
        });
    }
    return $query->sum('order_quantity');
}
}