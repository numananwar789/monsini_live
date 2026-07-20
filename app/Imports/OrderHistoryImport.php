<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderHistoryImport implements ToCollection, WithHeadingRow
{
    public $order_count = 0;
    public $cnfrm_order_count = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        // Preload data for better performance
        $vendors = Vendor::all()->keyBy(function ($item) {
            return Str::lower(trim($item->vendor_comp_name));
        });

        $customers = Customer::all()->keyBy(function ($item) {
            return Str::lower(trim($item->cust_comp_name));
        });

        $products = Product::all()->groupBy(function ($item) {
            return Str::lower(trim($item->product_style));
        });

        DB::transaction(function () use ($rows, $vendors, $customers, $products) {
            foreach ($rows as $row) {
                if (!isset($row['customer_name'])) continue;

                $this->order_count += 1;

                // Normalize data
                $customerName = Str::lower(trim($row['customer_name'] ?? 'NA'));
                $style = Str::lower(trim($row['style'] ?? 'NA'));
                $color = Str::lower(trim($row['color'] ?? 'NA'));
                $size = $row['size'] ?? 'NA';
                $quantity = $row['quantity'] ?? 'NA';
                $fromInventory = $row['from_inventry'] ?? 0;
                $fromOnway = $row['from_onway'] ?? 0;
                $note = $row['note'] ?? 'NA';
                $purchaseId = $row['purchase_id'] ?? 'NA';
                $vendorPurchaseId = $row['vendor_purchase_id'] ?? 'NA';
                $customerPurchaseId = $row['customer_purchase_id'] ?? 'NA';
                $status = Str::lower(trim($row['status'] ?? 'NA'));
                $orderWearDate = $row['order_wear_date'] ?? 'NA';

                // Find customer
                $customer = $customers->get($customerName);
                if (!$customer) {
                    $this->errors[] = "Customer '$customerName' not found";
                    continue;
                }

                // Find product and vendor
                $product = $products->get($style)->first();
                if (!$product) {
                    $this->errors[] = "Product style '$style' not found";
                    continue;
                }

                $vendor = $vendors->get(Str::lower(trim($product->product_vendor_name)));
                if (!$vendor) {
                    $this->errors[] = "Vendor for product '$style' not found";
                    continue;
                }

                if (!is_numeric($size) || $size < 0 || $size > 28) {
                    $this->errors[] = "The size: '$size' for product style: '$style' doesn't valid size.";
                    continue;
                }

                // Calculate costs
                $cost = $product->product_cost * $quantity;
                if ($size >= 18) {
                    $cost = ($product->product_cost + 30) * $quantity;
                }
                $wholesale = $product->product_wholesale_price * $quantity;

                try {
                    // Create order
                    $order = Order::create([
                        'order_customer_ID' => $customer->cust_ID,
                        'order_customer_name' => $customerName,
                        'order_vendor_ID' => $vendor->vendor_ID,
                        'order_vendor_name' => $vendor->vendor_comp_name,
                        'order_product_ID' => $product->product_ID,
                        'order_product_style' => $style,
                        'order_product_color' => $color,
                        'order_product_size' => $size,
                        'order_quantity' => $quantity,
                        'given_by_invntry' => $fromInventory,
                        'given_by_onway' => $fromOnway,
                        'order_cost' => $cost,
                        'order_purchase_price' => $wholesale,
                        'order_note' => $note,
                        'purchase_id' => $purchaseId,
                        'onway_vndr_prchs_ids' => $vendorPurchaseId,
                        'onway_cstmr_prchs_ids' => $customerPurchaseId,
                        'order_status' => 'Allocated',
                        'order_wear_date' => $orderWearDate,
                        'user_flag' => 'admin'
                    ]);

                    // Create history record
                    OrderHistory::create([
                        'allocation_ID' => 0,
                        'final_ID' => 0,
                        'order_ID' => $order->order_ID,
                        'order_customer_ID' => $order->order_customer_ID,
                        'order_customer_name' => $order->order_customer_name,
                        'order_vendor_ID' => $order->order_vendor_ID,
                        'order_vendor_name' => $order->order_vendor_name,
                        'vendor_purchase_ID' => $order->onway_vndr_prchs_ids,
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
                        'created_at_allocation' => $order->created_at,
                        'onway_vndr_prchs_ids' => $order->onway_vndr_prchs_ids,
                        'onway_cstmr_prchs_ids' => $order->onway_cstmr_prchs_ids,
                        'order_wear_date' => $order->order_wear_date,
                        'user_flag' => 'admin'
                    ]);

                    $this->cnfrm_order_count += 1;
                } catch (\Exception $e) {
                    $this->errors[] = $style . ',' . $color . ',' . $size . ',' . $customerName . ',' . $purchaseId;
                }
            }
        });
    }
}
