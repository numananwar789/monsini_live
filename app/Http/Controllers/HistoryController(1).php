<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\OrderFinal;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderAllocation;
use App\Models\Vendor;
use App\Models\OrderHistory;

class HistoryController extends Controller
{
    public function index()
    {

        
        // Get customer info
        $customer = Auth::user()->customer;

        // dd(Auth::user());

        // Get order history
        $orders = DB::table('dt_order')
            ->leftJoin('dt_vendor', 'dt_order.order_vendor_ID', '=', 'dt_vendor.vendor_ID')
            ->select(
                'dt_order.order_ID',
                'dt_order.order_product_style',
                'dt_order.order_product_color',
                'dt_order.order_product_size',
                'dt_order.order_quantity',
                'dt_order.order_purchase_price',
                'dt_order.order_note',
                'dt_order.purchase_id',
                'dt_order.sub_products',
                'dt_order.created_at',
                DB::raw("'NA' AS order_ship_date"),
                'dt_order.order_status',
                'dt_vendor.vendor_days',
                'dt_order.order_wear_date'
            )
            ->where('order_customer_ID', $customer->cust_ID)
            ->where('order_status', 'Allocated')
            ->get();

        return view('customer.history', compact('orders', 'customer'));
    }

    public function destroy(Request $request, $orderId)
    {
        try {
            $order = DB::table('dt_order')->where('order_ID', $orderId)->first();

            if (!$order) {
                return redirect()->back()->with('error', 'Order not found');
            }

            $onWayCount = $order->given_by_onway;
            $inventoryCount = $order->given_by_invntry;

            if ($onWayCount == 0 && $inventoryCount == 0) {
                // Simple delete
                DB::table('dt_order')->where('order_ID', $orderId)->delete();
            } elseif ($onWayCount > 0) {
                // Get owner info
                $owner = DB::table('dt_cust')
                    ->where('cust_owner', 'Yes')
                    ->first(['cust_ID', 'cust_comp_name']);

                // Insert into allocation table
                DB::table('dt_order_allocation')->insert([
                    'final_ID' => 0,
                    'order_ID' => $order->order_ID,
                    'order_customer_ID' => $owner->cust_ID,
                    'order_customer_name' => $owner->cust_comp_name,
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
                    'sub_products' =>json_encode($order->sub_products ?? []),
                    'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                    'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids
                ]);

                // Delete the order
                DB::table('dt_order')->where('order_ID', $orderId)->delete();
            } elseif ($inventoryCount > 0) {
                // Update inventory
                DB::table('dt_inventory')
                    ->where('product_style', $order->order_product_style)
                    ->where('product_color', $order->order_product_color)
                    ->where('product_size', $order->order_product_size)
                    ->increment('product_quantity', $order->order_quantity);

                // Delete the order
                DB::table('dt_order')->where('order_ID', $orderId)->delete();
            }

            return redirect()->route('customer.history')->with('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


}
