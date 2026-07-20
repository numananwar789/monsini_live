<?php

namespace App\Imports;

use App\Models\Customer;
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
        $customers = Customer::all()->keyBy(function ($item) {
            return strtolower(trim($item->cust_comp_name));
        });

        $products = Product::active()->get()->groupBy(function ($item) {
            return strtolower(trim($item->product_style));
        });

        foreach ($rows as $index => $row) {
            $this->order_count++;

            try {
                $excelRowNumber = $index + 2;

                $customerName = strtolower(trim($row['customer_name'] ?? ''));
                $style = strtolower(trim($row['style'] ?? ''));
                $color = strtolower(trim($row['color'] ?? ''));
                $size = trim($row['size'] ?? '');
                $quantity = trim($row['quantity'] ?? '');
                $status = strtolower(trim($row['status'] ?? ''));

                $from_inventory = $row['from_inventory'] ?? $row['from_inventry'] ?? 0;
                $from_onway = $row['from_onway'] ?? 0;

                $subProductsRaw = $row['sub_products'] ?? null;
                $subProducts = [];

                if (!empty($subProductsRaw)) {
                    $subProducts = array_filter(array_map('trim', explode(',', $subProductsRaw)));
                }

                if ($customerName === '') {
                    $this->errors[] = "Row {$excelRowNumber}: Customer name is missing.";
                    continue;
                }

                if (!isset($customers[$customerName])) {
                    $this->errors[] = "Row {$excelRowNumber}: Customer '{$customerName}' does not exist in DB.";
                    continue;
                }

                if ($style === '') {
                    $this->errors[] = "Row {$excelRowNumber}: Style is missing.";
                    continue;
                }

                if (!isset($products[$style])) {
                    $this->errors[] = "Row {$excelRowNumber}: Product style '{$style}' does not exist in DB.";
                    continue;
                }

                $product = $products[$style]->first(function ($item) use ($color) {
                    return strtolower(trim($item->product_color)) === $color;
                });

                if (!$product) {
                    $this->errors[] = "Row {$excelRowNumber}: Color '{$color}' for style '{$style}' does not exist or is not active.";
                    continue;
                }

                if (!is_numeric($size)) {
                    $this->errors[] = "Row {$excelRowNumber}: Size '{$size}' is invalid.";
                    continue;
                }

                $range = explode('-', $product->product_size_range);

                if (count($range) < 2 || !is_numeric($range[0]) || !is_numeric($range[1])) {
                    $this->errors[] = "Row {$excelRowNumber}: Invalid size range for style '{$style}'.";
                    continue;
                }

                $size = (int) $size;
                $min = (int) $range[0];
                $max = (int) $range[1];

                if ($size < $min || $size > $max) {
                    $this->errors[] = "Row {$excelRowNumber}: Size '{$size}' is not in valid range '{$product->product_size_range}'.";
                    continue;
                }

                if (($size % 2) == 1) {
                    $this->errors[] = "Row {$excelRowNumber}: Size '{$size}' for style '{$style}' must be even.";
                    continue;
                }

                if (!is_numeric($quantity)) {
                    $this->errors[] = "Row {$excelRowNumber}: Quantity '{$quantity}' is invalid.";
                    continue;
                }

                $quantity = (int) $quantity;

                if ($quantity <= 0) {
                    $this->errors[] = "Row {$excelRowNumber}: Quantity must be greater than 0.";
                    continue;
                }

                if (!in_array($status, ['pending', 'placed'])) {
                    $this->errors[] = "Row {$excelRowNumber}: Status must be pending or placed.";
                    continue;
                }

                $wholesalePrice = $this->cleanNumber($product->product_wholesale_price);
                $productCost = $this->cleanNumber($product->product_cost);

                if (!is_numeric($wholesalePrice)) {
                    $this->errors[] = "Row {$excelRowNumber}: Invalid wholesale price for style '{$style}'.";
                    continue;
                }

                if (!is_numeric($productCost)) {
                    $this->errors[] = "Row {$excelRowNumber}: Invalid product cost for style '{$style}'.";
                    continue;
                }

                // $cost = (float) $wholesalePrice * $quantity;

                // if ($size >= 18) {
                //     $cost = ((float) $wholesalePrice + 30) * $quantity;
                // }
                
                $extraPrice = 30;
                
                if (str_starts_with(strtolower($product->product_style), 'b')) {
                    $extraPrice = 60;
                }
                
                $cost = (float) $wholesalePrice * $quantity;
                
                if ($size >= 18) {
                    $cost = ((float) $wholesalePrice + $extraPrice) * $quantity;
                } 


                $wholesale = (float) $productCost * $quantity;

                $row['size'] = $size;
                $row['quantity'] = $quantity;
                $row['from_inventory'] = $from_inventory;
                $row['from_inventry'] = $from_inventory;
                $row['from_onway'] = $from_onway;

                $customer = $customers[$customerName];

                if ($status === 'pending') {
                    $this->createPendingOrder($customer, $product, $row, $cost, $wholesale, $subProducts);
                }

                if ($status === 'placed') {
                    $this->createPlacedOrder($customer, $product, $row, $cost, $wholesale, $subProducts);
                }

                $this->cnfrm_order_count++;

            } catch (\Throwable $e) {
                Log::error("Order import failed on row " . ($index + 2) . ": " . $e->getMessage());

                $this->errors[] = "Row " . ($index + 2) . ": Import failed. " . $e->getMessage();

                continue;
            }
        }
    }

    private function cleanNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/[^0-9.]/', '', (string) $value);
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
            'given_by_invntry' => $row['from_inventory'] ?? 0,
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

    protected function createPlacedOrder($customer, $product, $row, $cost, $wholesale, $subProducts = [])
    {
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
            'given_by_invntry' => $row['from_inventory'] ?? 0,
            'given_by_onway' => $row['from_onway'] ?? 0,
            'order_cost' => $wholesale,
            'order_purchase_price' => $cost,
            'order_note' => $row['note'] ?? 'NA',
            'purchase_id' => $row['purchase_id'] ?? 'NA',
            'onway_vndr_prchs_ids' => $row['vendor_purchase_id'] ?? 'NA',
            'onway_cstmr_prchs_ids' => $row['customer_purchase_id'] ?? 'NA',
            'order_status' => 'placed'
        ]);

        if (($row['from_inventory'] ?? 0) > 0 || ($row['from_onway'] ?? 0) > 0) {
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