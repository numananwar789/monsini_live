<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustOrderController extends Controller
{
    public function index()
    {
        // Get customer info
        $customer = Auth::user()->customer;
        
        // dd($customer);
        // Get orders
        $orders = DB::table('dt_order')
            ->leftJoin('dt_vendor', 'dt_order.order_vendor_ID', '=', 'dt_vendor.vendor_ID')
            ->select(
                'dt_order.order_ID',
                'dt_order.order_product_style',
                'dt_order.order_product_color',
                'dt_order.order_product_size',
                'dt_order.order_quantity',
                'dt_order.order_cost',
                'dt_order.order_purchase_price',
                'dt_order.order_note',
                'dt_order.purchase_id',
                'dt_order.created_at',
                'dt_order.order_status',
                'dt_order.sub_products',
                'dt_order.given_by_invntry',
                'dt_order.given_by_onway',
                'dt_order.order_wear_date',
                'dt_vendor.vendor_days',
                'dt_vendor.vendor_days_stock'
            )
            ->where('order_customer_ID', $customer->cust_ID)
            ->where('order_status', '!=', 'Allocated')
            ->get();

        return view('customer.orders', compact('orders', 'customer'));
    }

    public function destroy(Request $request)
    {
        try {
            $orderId = $request->input('orderID');
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
                    'sub_products' => json_encode($order->sub_products ?? []),
                    'created_at_final' => $order->created_at,
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

            return redirect()->route('customer.orders.index')->with('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($orderId)
    {
        $order = DB::table('dt_order')->where('order_ID', $orderId)->first();
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'order_quantity' => $order->order_quantity,
            'purchase_id' => $order->purchase_id,
            'given_by_invntry' => $order->given_by_invntry,
            'given_by_onway' => $order->given_by_onway,
            'order_ID' => $order->order_ID
        ]);
    }

    public function update(Request $request, $orderId)
    {
        try {
            DB::table('dt_order')
                ->where('order_ID', $orderId)
                ->update([
                    'order_quantity' => $request->quantity,
                    'purchase_id' => $request->purchase_id
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}