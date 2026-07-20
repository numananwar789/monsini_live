<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderAllocation;
use App\Models\OrderFinal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class OrdersImport implements ToCollection, WithHeadingRow
{
    public $order_count = 0;
    public $cnfrm_order_count = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        // Get all necessary data upfront to reduce queries
        $customers = Customer::all()->keyBy(function($item) {
            return strtolower(trim($item->cust_comp_name));
        });
        
        $products = Product::active()->get()->groupBy(function($item) {
            return strtolower(trim($item->product_style));
        });

        foreach ($rows as $row) {
            $this->order_count += 1;

            // Normalize data from Excel
            $customerName = isset($row['customer_name']) ? strtolower(trim($row['customer_name'])) : 'NA';
            $style = isset($row['style']) ? strtolower(trim($row['style'])) : 'NA';
            $color = isset($row['color']) ? strtolower(trim($row['color'])) : 'NA';
            $size = $row['size'] ?? 'NA';
            $quantity = $row['quantity'] ?? 'NA';
            $from_inventry = $row['from_inventry'] ?? 0;
            $from_onway = $row['from_onway'] ?? 0;
            $note = $row['note'] ?? 'NA';
            $purchase_id = $row['purchase_id'] ?? 'NA';
            $vendor_purchase_id = $row['vendor_purchase_id'] ?? 'NA';
            $customer_purchase_id = $row['customer_purchase_id'] ?? 'NA';
            $status = isset($row['status']) ? strtolower(trim($row['status'])) : 'NA';

           
            $subProductsRaw = $row['sub_products'] ?? null;
            $subProducts = [];

            if (!empty($subProductsRaw)) {
                $subProductsArray = array_map('trim', explode(',', $subProductsRaw));
                $subProductsArray = array_filter($subProductsArray);
                $subProducts = $subProductsArray;
            }

            

            // $product = $products[strtolower(trim("A10003"))]->first();
            

            
            // dd($range);
            //order_product_style
            //
            //A10003
            // Validate customer
            if (!isset($customers[$customerName])) {
                $this->errors[] = "The customer: '$customerName' doesn't exist in the DB.";
                continue;
            }
            $customer = $customers[$customerName];

            // Validate product style
            if (!isset($products[$style])) {
                $this->errors[] = "The product style: '$style' doesn't exist in the DB.";
                continue;
            }

            // Find matching product with color
            $product = $products[$style]->first(function($item) use ($color) {
                return strtolower(trim($item->product_color)) === $color;
            });

            if (!$product) {
                $this->errors[] = "The color: '$color' for product style: '$style' doesn't exist OR isn't active in the DB.";
                continue;
            }

            $range = explode('-', $product->product_size_range);
            $min = (int)$range[0];
            $max = (int)$range[1];

            if (!is_numeric($size) || $size < $min || $size > $max) {
                $this->errors[] = "The size: '$size' for product style: '$style' doesn't valid size. Its not in range '$product->product_size_range'";
                continue;
            }

            if ( ($size%2) == 1) {
                $this->errors[] = "The size: '$size' for product style: '$style' must be even";
                continue;
            }
            
            // Calculate costs
            $cost = $product->product_wholesale_price * $quantity;
            if ($size >= 18) {
                $cost = ($product->product_wholesale_price + 30) * $quantity;
            }
            $wholesale = $product->product_cost * $quantity;

            try {
                if ($status == "pending") {
                    $this->createPendingOrder($customer, $product, $row, $cost, $wholesale,$subProducts);
                } elseif ($status == "placed") {
                    $this->createPlacedOrder($customer, $product, $row, $cost, $wholesale,$subProducts);
                }
                
                $this->cnfrm_order_count += 1;
            } catch (\Exception $e) {
                Log::error("Order import failed: " . $e->getMessage());
                $this->errors[] = $style . ',' . $color . ',' . $size . ',' . $customerName . ',' . $purchase_id;
            }
        }
    }

    protected function createPendingOrder($customer, $product, $row, $cost, $wholesale, $subProducts = [])
    {
        Order::create([
            'order_customer_ID' => $customer->cust_ID,
            'order_customer_name' => $customer->cust_comp_name,
            'order_vendor_ID' => $product->product_vendor_ID,
            'order_vendor_name' => $product->product_vendor_name,
            'order_product_ID' => $product->product_ID,
            'order_product_style' => $product->product_style,
            'sub_products' => $subProducts,
            'order_product_color' => $product->product_color,
            'order_product_size' => $row['size'],
            'order_quantity' => $row['quantity'],
            'given_by_invntry' => $row['from_inventry'] ?? 0,
            'given_by_onway' => $row['from_onway'] ?? 0,
            'order_cost' => $wholesale,
            'order_purchase_price' => $cost,
            'order_note' => $row['note'] ?? 'NA',
            'purchase_id' => $row['purchase_id'] ?? 'NA',
            'onway_vndr_prchs_ids' => $row['vendor_purchase_id'] ?? 'NA',
            'onway_cstmr_prchs_ids' => $row['customer_purchase_id'] ?? 'NA',
            'order_status' => 'pending'
        ]);
    }

    protected function createPlacedOrder($customer, $product, $row, $cost, $wholesale , $subProducts = [])
    {
        // Create order
        $order = Order::create([
            'order_customer_ID' => $customer->cust_ID,
            'order_customer_name' => $customer->cust_comp_name,
            'order_vendor_ID' => $product->product_vendor_ID,
            'order_vendor_name' => $product->product_vendor_name,
            'order_product_ID' => $product->product_ID,
            'order_product_style' => $product->product_style,
            'order_product_color' => $product->product_color,
            'sub_products' => $subProducts,
            'order_product_size' => $row['size'],
            'order_quantity' => $row['quantity'],
            'given_by_invntry' => $row['from_inventry'] ?? 0,
            'given_by_onway' => $row['from_onway'] ?? 0,
            'order_cost' => $wholesale,
            'order_purchase_price' => $cost,
            'order_note' => $row['note'] ?? 'NA',
            'purchase_id' => $row['purchase_id'] ?? 'NA',
            'onway_vndr_prchs_ids' => $row['vendor_purchase_id'] ?? 'NA',
            'onway_cstmr_prchs_ids' => $row['customer_purchase_id'] ?? 'NA',
            'order_status' => 'placed'
        ]);

        if (($row['from_inventry'] ?? 0) > 0 || ($row['from_onway'] ?? 0) > 0) {
            // Create allocation
            OrderAllocation::create([
                'final_ID' => 0,
                'order_ID' => $order->order_ID,
                'order_customer_ID' => $order->order_customer_ID,
                'order_customer_name' => $order->order_customer_name,
                'order_vendor_ID' => $order->order_vendor_ID,
                'order_vendor_name' => $order->order_vendor_name,
                'order_product_ID' => $order->order_product_ID,
                'sub_products' => $subProducts,
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
                'created_at' => now(),
                'created_at_final' => now(),
                'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                'vendor_purchase_ID' => $order->onway_vndr_prchs_ids
            ]);
        } else {
            // Create final order
            $finalOrder = OrderFinal::create([
                'order_ID' => $order->order_ID,
                'order_customer_ID' => $order->order_customer_ID,
                'order_customer_name' => $order->order_customer_name,
                'order_vendor_ID' => $order->order_vendor_ID,
                'order_vendor_name' => $order->order_vendor_name,
                'order_product_ID' => $order->order_product_ID,
                'sub_products' => $subProducts,
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
                'created_at' => now(),
                'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                'order_status' => 'placed'
            ]);

            // Create allocation for final order
            OrderAllocation::create([
                'final_ID' => $finalOrder->final_ID,
                'order_ID' => $finalOrder->order_ID,
                'order_customer_ID' => $finalOrder->order_customer_ID,
                'order_customer_name' => $finalOrder->order_customer_name,
                'order_vendor_ID' => $finalOrder->order_vendor_ID,
                'sub_products' => $subProducts,
                'order_vendor_name' => $finalOrder->order_vendor_name,
                'order_product_ID' => $finalOrder->order_product_ID,
                'order_product_style' => $finalOrder->order_product_style,
                'order_product_color' => $finalOrder->order_product_color,
                'order_product_size' => $finalOrder->order_product_size,
                'order_quantity' => $finalOrder->order_quantity,
                'given_by_invntry' => $finalOrder->given_by_invntry,
                'given_by_onway' => $finalOrder->given_by_onway,
                'order_cost' => $finalOrder->order_cost,
                'order_purchase_price' => $finalOrder->order_purchase_price,
                'order_note' => $finalOrder->order_note,
                'purchase_id' => $finalOrder->purchase_id,
                'created_at' => now(),
                'created_at_final' => now(),
                'onway_vndr_prchs_ids' => $finalOrder->onway_vndr_prchs_ids,
                'onway_cstmr_prchs_ids' => $finalOrder->onway_cstmr_prchs_ids,
                'vendor_purchase_ID' => $finalOrder->onway_vndr_prchs_ids
            ]);
        }
    }
}