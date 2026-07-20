<?php

namespace App\Http\Controllers;

use App\Models\OrderHistoryArchive;
use App\Models\OrderHistory;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderHistoryArchiveController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('role:admin|superadmin');
    }

    public function index(Request $request)
    {

        
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        
        $ownerComp = Customer::where('cust_owner', 'Yes')->value('cust_comp_name');
        $archiveList = OrderHistoryArchive::distinct()->pluck('archive_name');
        $orders = collect();

        if ($request->has('action')) {
            $archiveName = $request->action;
            $orders = OrderHistoryArchive::where('archive_name', $archiveName)
                ->orderByDesc('history_date')
                ->get();
        }

        return view('order-history-archives.index', compact(
            'ownerComp',
            'archiveList',
            'orders'
        ));
    }

    public function restore(Request $request)
    {
        $request->validate([
            'history' => 'sometimes|array',
            'history.*' => 'exists:dt_order_history_archive,history_ID',
            'archive_name' => 'sometimes|string'
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('history')) {
                // Restore selected orders
                $historyIds = $request->history;

                // Insert into history table
                OrderHistory::insertUsing(
                    [
                        'history_ID', 'allocation_ID', 'final_ID', 'order_ID', 'order_customer_ID',
                        'order_customer_name', 'order_vendor_ID', 'order_vendor_name', 'vendor_purchase_ID',
                        'order_product_ID', 'order_product_style', 'order_product_color', 'order_product_size',
                        'order_quantity', 'given_by_invntry', 'given_by_onway', 'order_cost', 'order_purchase_price',
                        'order_note', 'purchase_id', 'created_at', 'created_at_final', 'created_at_allocation',
                        'onway_vndr_prchs_ids', 'onway_cstmr_prchs_ids', 'history_date', 'order_wear_date',
                        'user_flag', 'order_GUID' ,'sub_products'
                    ],
                    OrderHistoryArchive::whereIn('history_ID', $historyIds)
                        ->select(
                            'history_ID', 'allocation_ID', 'final_ID', 'order_ID', 'order_customer_ID',
                            'order_customer_name', 'order_vendor_ID', 'order_vendor_name', 'vendor_purchase_ID',
                            'order_product_ID', 'order_product_style', 'order_product_color', 'order_product_size',
                            'order_quantity', 'given_by_invntry', 'given_by_onway', 'order_cost', 'order_purchase_price',
                            'order_note', 'purchase_id', 'created_at', 'created_at_final', 'created_at_allocation',
                            'onway_vndr_prchs_ids', 'onway_cstmr_prchs_ids', 'history_date', 'order_wear_date',
                            'user_flag', 'order_GUID' , 'sub_products'
                        )
                );

                // Delete from archive
                OrderHistoryArchive::whereIn('history_ID', $historyIds)->delete();
            } elseif ($request->has('archive_name')) {
                // Restore entire archive
                $archiveName = $request->archive_name;

                // Insert into history table
                OrderHistory::insertUsing(
                    [
                        'history_ID', 'allocation_ID', 'final_ID', 'order_ID', 'order_customer_ID',
                        'order_customer_name', 'order_vendor_ID', 'order_vendor_name', 'vendor_purchase_ID',
                        'order_product_ID', 'order_product_style', 'order_product_color', 'order_product_size',
                        'order_quantity', 'given_by_invntry', 'given_by_onway', 'order_cost', 'order_purchase_price',
                        'order_note', 'purchase_id', 'created_at', 'created_at_final', 'created_at_allocation',
                        'onway_vndr_prchs_ids', 'onway_cstmr_prchs_ids', 'history_date', 'order_wear_date',
                        'user_flag', 'order_GUID' , 'sub_products'
                    ],
                    OrderHistoryArchive::where('archive_name', $archiveName)
                        ->select(
                            'history_ID', 'allocation_ID', 'final_ID', 'order_ID', 'order_customer_ID',
                            'order_customer_name', 'order_vendor_ID', 'order_vendor_name', 'vendor_purchase_ID',
                            'order_product_ID', 'order_product_style', 'order_product_color', 'order_product_size',
                            'order_quantity', 'given_by_invntry', 'given_by_onway', 'order_cost', 'order_purchase_price',
                            'order_note', 'purchase_id', 'created_at', 'created_at_final', 'created_at_allocation',
                            'onway_vndr_prchs_ids', 'onway_cstmr_prchs_ids', 'history_date', 'order_wear_date',
                            'user_flag', 'order_GUID' , 'sub_products'
                        )
                );

                // Delete from archive
                OrderHistoryArchive::where('archive_name', $archiveName)->delete();
            }

            DB::commit();
            return redirect()->route('order-history-archives.index')
                ->with('success', 'Orders restored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('order-history-archives.index')
                ->with('error', 'Error restoring orders: ' . $e->getMessage());
        }
    }
}