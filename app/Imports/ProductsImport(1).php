<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Get all vendors upfront to reduce queries
        $vendors = Vendor::all()->mapWithKeys(function ($vendor) {
            return [Str::lower($vendor->vendor_comp_name) => $vendor];
        });
        // dd($vendors);

        foreach ($rows as $row) {
            // Skip if required fields are missing
            if (!isset($row['style']) || !isset($row['color'])) {
                continue;
            }

            // Normalize data
            $style = Str::lower(trim($row['style']));
            $color = Str::lower(trim($row['color']));
            $vendorName = isset($row['vendor_name']) ? Str::lower(trim($row['vendor_name'])) : 'NA';

            // Find vendor
            $vendor = $vendors->get($vendorName);
            $vendorId = $vendor ? $vendor->vendor_ID : null;
            $vendorName = $vendor ? $vendor->vendor_comp_name : 'NA';

            // Check if product already exists
            $existingProduct = Product::where('product_style', $style)
                ->where('product_color', $color)
                ->first();

            $subProductsRaw = $row['sub_products'] ?? null;
            $subProducts = [];

            if (!empty($subProductsRaw)) {
                $subProductsArray = array_map('trim', explode(',', $subProductsRaw));
                $subProductsArray = array_filter($subProductsArray);
                $subProducts = $subProductsArray;
            }


            if (!$existingProduct) {
                Product::create([
                    'product_style' => $style,
                    'product_color' => $color,
                    'product_size_range' => $row['size_range'] ?? '0-28',
                    'product_cost' => $row['cost'] ?? 0,
                    'product_wholesale_price' => $row['wholesale_price'] ?? 0,
                    'product_vendor_ID' => $vendorId,
                    'product_vendor_name' => $vendorName,
                    'product_link' => $row['link'] ?? 'NA',
                    'product_image' => $row['image'] ?? 'NA',
                    'factory_style' => $row['factory_style'] ?? '',
                    'sub_products' => $subProducts,
                    'product_status' => 1 // Assuming active by default
                ]);
            }
        }
    }
}
