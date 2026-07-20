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

        $ownerComp      = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $archiveList    = OrderHistoryArchive::distinct()->pluck('archive_name');
        $withStock      = $request->has('exclude_stock');
        $selectedArchive = $request->selected_archive;

        if ($request->has('action') && $selectedArchive) {
            // ── ARCHIVE MODE ──────────────────────────────────────────────
            // All data comes from dt_order_history_archive filtered by archive_name
            $styleLookup = $this->getStyleTotalsLookup($withStock, $selectedArchive);
            $totOrders   = OrderHistoryArchive::where('archive_name', $selectedArchive)->get();
            $topStyles   = $this->getTopStyles($selectedArchive, $withStock, $styleLookup);
            $custHistory = OrderHistoryArchive::where('archive_name', $selectedArchive)->get();
            $topCust     = $this->getTopCustomers($selectedArchive);
            $lateVendors = $this->getLateVendors($selectedArchive);
        } else {
            // ── LIVE MODE ─────────────────────────────────────────────────
            // Data comes from all four live tables
            $styleLookup = $this->getStyleTotalsLookup($withStock);
            $totOrders   = $this->getCombinedOrders($styleLookup);
            $topStyles   = $this->getTopStyles(null, $withStock, $styleLookup);
            $custHistory = $this->getCustomerHistory($styleLookup);
            $topCust     = $this->getTopCustomers();
            $lateVendors = $this->getLateVendors();
        }

        return view('reports.index', compact(
            'ownerComp', 'archiveList', 'totOrders',
            'topStyles', 'custHistory', 'topCust', 'lateVendors'
        ));
    }

    // =========================================================================
    // LIVE MODE helpers
    // =========================================================================

    protected function getCombinedOrders($styleLookup)
    {
        $results = DB::select("
            SELECT order_vendor_name, order_customer_name, order_product_style,
                   order_product_color, order_product_size, order_quantity
            FROM dt_order WHERE order_status = 'Pending'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style,
                   order_product_color, order_product_size, order_quantity
            FROM dt_order_final WHERE order_status <> 'Placed'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style,
                   order_product_color, order_product_size, order_quantity
            FROM dt_order_allocation WHERE order_status <> 'Allocated'
            UNION ALL
            SELECT order_vendor_name, order_customer_name, order_product_style,
                   order_product_color, order_product_size, order_quantity
            FROM dt_order_history
        ");

        foreach ($results as &$item) {
            $item->OrdersTables = $styleLookup[$item->order_product_style]
                ?? "Orders: 0 | Final: 0 | Allocation: 0 | History: 0";
        }
        return $results;
    }

    protected function getCustomerHistory($styleLookup)
    {
        $results = Order::where('order_status', 'Pending')
            ->select('order_customer_name', 'order_product_style', 'order_product_color',
                     'order_product_size', 'purchase_id', 'order_status', 'created_at', 'order_quantity')
            ->unionAll(
                OrderFinal::where('order_status', '<>', 'Placed')
                    ->select('order_customer_name', 'order_product_style', 'order_product_color',
                             'order_product_size', 'purchase_id', 'order_status', 'created_at', 'order_quantity')
            )
            ->unionAll(
                OrderAllocation::where('order_status', '<>', 'Allocated')
                    ->select('order_customer_name', 'order_product_style', 'order_product_color',
                             'order_product_size', 'purchase_id', DB::raw("'Sent to Vendor'"), 'created_at', 'order_quantity')
            )
            ->unionAll(
                OrderHistory::select('order_customer_name', 'order_product_style', 'order_product_color',
                                     'order_product_size', 'purchase_id', DB::raw("'Allocated'"), 'created_at', 'order_quantity')
            )
            ->get();

        return $results->map(function ($item) use ($styleLookup) {
            $item->OrdersTables = $styleLookup[$item->order_product_style] ?? "N/A";
            return $item;
        });
    }

    // =========================================================================
    // SHARED helpers (work in both live and archive mode)
    // =========================================================================

    /**
     * @param  string|null $archiveName  NULL = live tables, string = archive table
     * @param  bool        $withStock
     * @param  array       $styleLookup
     */
    protected function getTopStyles($archiveName = null, $withStock = false, $styleLookup = [])
    {
        $stockFilter = $withStock
            ? " AND (purchase_id IS NULL OR purchase_id != 'stock') "
            : "";

        if ($archiveName) {
            // ── Archive: single table, filter by archive_name ─────────────
            $sql = "
                SELECT
                    order_product_style,
                    SUM(total_qty)  AS total_count,
                    GROUP_CONCAT(DISTINCT vendors SEPARATOR ', ') AS vendors,
                    GROUP_CONCAT(
                        CONCAT(order_product_color, ': ', total_qty)
                        ORDER BY order_product_color SEPARATOR ' | '
                    ) AS colors
                FROM (
                    SELECT
                        order_product_style,
                        order_product_color,
                        SUM(order_quantity)                        AS total_qty,
                        GROUP_CONCAT(DISTINCT order_vendor_name)   AS vendors
                    FROM dt_order_history_archive
                    WHERE archive_name = ? {$stockFilter}
                    GROUP BY order_product_style, order_product_color
                ) AS color_totals
                GROUP BY order_product_style
                ORDER BY total_count DESC";

            $results = DB::select($sql, [$archiveName]);
        } else {
            // ── Live: union of four tables ────────────────────────────────
            $sql = "
                SELECT
                    order_product_style,
                    SUM(total_qty)  AS total_count,
                    GROUP_CONCAT(DISTINCT vendors SEPARATOR ', ') AS vendors,
                    GROUP_CONCAT(
                        CONCAT(order_product_color, ': ', total_qty)
                        ORDER BY order_product_color SEPARATOR ' | '
                    ) AS colors
                FROM (
                    SELECT
                        order_product_style,
                        order_product_color,
                        SUM(order_quantity)                        AS total_qty,
                        GROUP_CONCAT(DISTINCT order_vendor_name)   AS vendors
                    FROM (
                        SELECT order_product_style, order_product_color, order_quantity, order_vendor_name
                        FROM dt_order WHERE order_status = 'Pending' {$stockFilter}
                        UNION ALL
                        SELECT order_product_style, order_product_color, order_quantity, order_vendor_name
                        FROM dt_order_final WHERE order_status <> 'Placed' {$stockFilter}
                        UNION ALL
                        SELECT order_product_style, order_product_color, order_quantity, order_vendor_name
                        FROM dt_order_allocation WHERE order_status <> 'Allocated' {$stockFilter}
                        UNION ALL
                        SELECT order_product_style, order_product_color, order_quantity, order_vendor_name
                        FROM dt_order_history WHERE 1=1 {$stockFilter}
                    ) AS raw_data
                    GROUP BY order_product_style, order_product_color
                ) AS color_totals
                GROUP BY order_product_style
                ORDER BY total_count DESC";

            $results = DB::select($sql);
        }

        foreach ($results as &$style) {
            $style->OrdersTables = $styleLookup[$style->order_product_style] ?? "";
        }

        return $results;
    }

    /**
     * @param  bool        $withStock
     * @param  string|null $archiveName  NULL = live tables, string = archive table
     */
    protected function getStyleTotalsLookup($withStock = false, $archiveName = null)
    {
        $stockFilter = $withStock
            ? " AND (purchase_id IS NULL OR purchase_id != 'stock') "
            : "";

        if ($archiveName) {
            // ── Archive ───────────────────────────────────────────────────
            $sql = "
                SELECT order_product_style,
                    SUM(order_quantity) AS q_o,
                    0                  AS q_f,
                    0                  AS q_a,
                    SUM(order_quantity) AS q_h
                FROM dt_order_history_archive
                WHERE archive_name = ? {$stockFilter}
                GROUP BY order_product_style";

            $raw = DB::select($sql, [$archiveName]);
        } else {
            // ── Live ──────────────────────────────────────────────────────
            $sql = "
                SELECT order_product_style,
                    SUM(CASE WHEN src = 'O' THEN order_quantity ELSE 0 END) AS q_o,
                    SUM(CASE WHEN src = 'F' THEN order_quantity ELSE 0 END) AS q_f,
                    SUM(CASE WHEN src = 'A' THEN order_quantity ELSE 0 END) AS q_a,
                    SUM(CASE WHEN src = 'H' THEN order_quantity ELSE 0 END) AS q_h
                FROM (
                    SELECT order_product_style, order_quantity, 'O' AS src
                    FROM dt_order WHERE order_status = 'Pending' {$stockFilter}
                    UNION ALL
                    SELECT order_product_style, order_quantity, 'F' AS src
                    FROM dt_order_final WHERE order_status <> 'Placed' {$stockFilter}
                    UNION ALL
                    SELECT order_product_style, order_quantity, 'A' AS src
                    FROM dt_order_allocation WHERE order_status <> 'Allocated' {$stockFilter}
                    UNION ALL
                    SELECT order_product_style, order_quantity, 'H' AS src
                    FROM dt_order_history WHERE 1=1 {$stockFilter}
                ) AS combined
                GROUP BY order_product_style";

            $raw = DB::select($sql);
        }

        $lookup = [];
        foreach ($raw as $r) {
            $lookup[$r->order_product_style] =
                "Orders: {$r->q_o} | Final: {$r->q_f} | Allocation: {$r->q_a} | History: {$r->q_h}";
        }
        return $lookup;
    }

    /**
     * @param  string|null $archiveName  NULL = live tables, string = archive table
     */
    protected function getTopCustomers($archiveName = null)
    {
        if ($archiveName) {
            // ── Archive: all data in one table ────────────────────────────
            $customerTotals = DB::table('dt_order_history_archive')
                ->where('archive_name', $archiveName)
                ->select(
                    'order_customer_name',
                    DB::raw("SUM(order_quantity) AS total_count"),
                    DB::raw("SUM(CASE WHEN purchase_id = 'stock' THEN order_quantity ELSE 0 END) AS stock_qty"),
                    DB::raw("SUM(CASE WHEN purchase_id IS NULL OR purchase_id != 'stock' THEN order_quantity ELSE 0 END) AS special_qty"),
                    DB::raw("0 AS qty_o"),
                    DB::raw("0 AS qty_f"),
                    DB::raw("0 AS qty_a"),
                    DB::raw("SUM(order_quantity) AS qty_h")
                )
                ->groupBy('order_customer_name')
                ->orderByDesc('total_count')
                ->get();
        } else {
            // ── Live: union of four tables ────────────────────────────────
            $customerTotals = DB::table(DB::raw("(
                SELECT order_customer_name, order_quantity, purchase_id, 'O' AS type
                FROM dt_order WHERE order_status = 'Pending'
                UNION ALL
                SELECT order_customer_name, order_quantity, purchase_id, 'F' AS type
                FROM dt_order_final WHERE order_status <> 'Placed'
                UNION ALL
                SELECT order_customer_name, order_quantity, purchase_id, 'A' AS type
                FROM dt_order_allocation WHERE order_status <> 'Allocated'
                UNION ALL
                SELECT order_customer_name, order_quantity, purchase_id, 'H' AS type
                FROM dt_order_history
            ) AS t"))
            ->select(
                'order_customer_name',
                DB::raw("SUM(order_quantity) AS total_count"),
                DB::raw("SUM(CASE WHEN purchase_id = 'stock' THEN order_quantity ELSE 0 END) AS stock_qty"),
                DB::raw("SUM(CASE WHEN purchase_id IS NULL OR purchase_id != 'stock' THEN order_quantity ELSE 0 END) AS special_qty"),
                DB::raw("SUM(CASE WHEN type = 'O' THEN order_quantity ELSE 0 END) AS qty_o"),
                DB::raw("SUM(CASE WHEN type = 'F' THEN order_quantity ELSE 0 END) AS qty_f"),
                DB::raw("SUM(CASE WHEN type = 'A' THEN order_quantity ELSE 0 END) AS qty_a"),
                DB::raw("SUM(CASE WHEN type = 'H' THEN order_quantity ELSE 0 END) AS qty_h")
            )
            ->groupBy('order_customer_name')
            ->orderByDesc('total_count')
            ->get();
        }

        return $customerTotals->sortByDesc('total_count')->map(function ($item) {
            return [
                'order_customer_name' => $item->order_customer_name,
                'total_count'         => (int) $item->total_count,
                'stock_special_split' => "Stock: {$item->stock_qty} | Specials: {$item->special_qty}",
                'OrdersTables'        => "ORDERS: {$item->qty_o} | FINAL: {$item->qty_f} | ALLOCATION: {$item->qty_a} | HISTORY: {$item->qty_h}",
            ];
        })->values()->toArray();
    }

    protected function getLateVendors($archiveName = null)
    {
        // Reusable expression to derive the allocation date
        $dateExpr = "
            IF(
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
            )";

        $commonSelect = "
            SELECT
                dta.order_vendor_name,
                dta.order_product_style,
                dta.order_product_color,
                dta.order_product_size,
                dta.order_quantity,
                dta.vendor_purchase_ID,
                dta.order_customer_name,
                dta.purchase_id,
                {$dateExpr}                                          AS created_at_allocation,
                dtv.vendor_days,
                DATE_ADD({$dateExpr}, INTERVAL dtv.vendor_days DAY) AS ETA,
                DATEDIFF(CURDATE(), DATE_ADD({$dateExpr}, INTERVAL dtv.vendor_days DAY)) AS delay_days
            FROM {table} AS dta
            INNER JOIN dt_vendor AS dtv ON dta.order_vendor_ID = dtv.vendor_ID
            WHERE {where}
            AND CURDATE() >= DATE_ADD({$dateExpr}, INTERVAL dtv.vendor_days DAY)
        ";

        if ($archiveName) {
            $sql    = str_replace(
                ['{table}', '{where}'],
                ['dt_order_history_archive', "dta.archive_name = ?"],
                $commonSelect
            );
            return DB::select($sql, [$archiveName]);
        }

        $sql = str_replace(
            ['{table}', '{where}'],
            ['dt_order_allocation', "dta.order_status <> 'Allocated'"],
            $commonSelect
        );
        return DB::select($sql);
    }

    // =========================================================================
    // Per-style per-table helpers (kept for any direct use elsewhere)
    // =========================================================================

    protected function getFromOrder($order_product_style, $withStock = false)
    {
        $query = Order::where('order_product_style', $order_product_style)
                      ->where('order_status', 'Pending');
        if ($withStock) {
            $query->where(fn($q) => $q->where('purchase_id', '!=', 'stock')->orWhereNull('purchase_id'));
        }
        return $query->sum('order_quantity');
    }

    protected function getFromOrderFinal($order_product_style, $withStock = false)
    {
        $query = OrderFinal::where('order_product_style', $order_product_style)
                           ->where('order_status', '!=', 'Placed');
        if ($withStock) {
            $query->where(fn($q) => $q->where('purchase_id', '!=', 'stock')->orWhereNull('purchase_id'));
        }
        return $query->sum('order_quantity');
    }

    protected function getFromOrderAllocation($order_product_style, $withStock = false)
    {
        $query = OrderAllocation::where('order_product_style', $order_product_style)
                                ->where('order_status', '!=', 'Allocated');
        if ($withStock) {
            $query->where(fn($q) => $q->where('purchase_id', '!=', 'stock')->orWhereNull('purchase_id'));
        }
        return $query->sum('order_quantity');
    }

    protected function getFromOrderHistory($order_product_style, $withStock = false)
    {
        $query = OrderHistory::where('order_product_style', $order_product_style);
        if ($withStock) {
            $query->where(fn($q) => $q->where('purchase_id', '!=', 'stock')->orWhereNull('purchase_id'));
        }
        return $query->sum('order_quantity');
    }
}