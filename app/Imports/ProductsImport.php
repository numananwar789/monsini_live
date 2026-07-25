<?php
namespace App\Imports;

use App\Models\Product;
use App\Models\SubProduct;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;
use App\Exceptions\ImportValidationException;

class ProductsImport implements ToCollection, WithHeadingRow
{
    /**
     * Columns that MUST have a value for every row.
     * Adjust this list to match your actual requirements.
     */
    protected array $requiredFields = [
        'style',
        'color',
        'size_range',
        'cost',
        'wholesale_price',
        'vendor_name',
        'link',
        'image',
        'version_year',
    ];

    /**
     * Map of lowercase/trimmed sub_product_name => canonical sub_product_name.
     * Built once per import run.
     */
    protected Collection $validSubProductsMap;

    public function __construct()
    {
        $this->validSubProductsMap = SubProduct::pluck('sub_product_name')
            ->mapWithKeys(fn($name) => [Str::lower(trim($name)) => $name]);
    }

    public function collection(Collection $rows)
    {
        // ---- PASS 1: Validate every row before touching the database ----
        $errors = [];
        // Store normalized sub_products per row index so Pass 2 doesn't redo the work
        $normalizedSubProducts = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 = header row + 0-based index offset
            $missing = [];
            $rowIssues = []; // collect all issue phrases for this row, combined into one line

            foreach ($this->requiredFields as $field) {
                $value = $row[$field] ?? null;
                if ($value === null || trim((string) $value) === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                $rowIssues[] = sprintf('missing [%s]', implode(', ', $missing));
            }

            // Validate sub_products against the master SubProduct list
            $subProductsRaw = $row['sub_products'] ?? null;
            $normalized = [];
            $invalid = [];

            if (!empty($subProductsRaw)) {
                $rawValues = array_filter(array_map('trim', explode(',', $subProductsRaw)));

                foreach ($rawValues as $value) {
                    $key = Str::lower($value);
                    if ($this->validSubProductsMap->has($key)) {
                        $normalized[] = $this->validSubProductsMap->get($key);
                    } else {
                        $invalid[] = $value;
                    }
                }
            }

            if (!empty($invalid)) {
                $rowIssues[] = sprintf('invalid sub product(s) [%s]', implode(', ', $invalid));
            }

            if (!empty($rowIssues)) {
                $errors[] = sprintf('Row %d: %s', $rowNumber, implode('; ', $rowIssues));
            }

            $normalizedSubProducts[$index] = array_values(array_unique($normalized));
        }

        // If any row is invalid, abort the entire import — nothing gets inserted
        if (!empty($errors)) {
            $validList = $this->validSubProductsMap->values()->sort()->implode(', ');
            $errors[] = 'Allowed sub product values are: ' . $validList;

            // Fallback message for anywhere that only reads getMessage() (e.g. logs)
            $flatMessage = "Import failed due to invalid data:\n" . implode("\n", $errors);

            throw new ImportValidationException($flatMessage, $errors);
        }

        // ---- PASS 2: All rows valid — safe to insert, wrapped in a transaction ----
        DB::transaction(function () use ($rows, $normalizedSubProducts) {
            $vendors = Vendor::all()->mapWithKeys(function ($vendor) {
                return [Str::lower($vendor->vendor_comp_name) => $vendor];
            });

            foreach ($rows as $index => $row) {
                $style = Str::lower(trim($row['style']));
                $color = Str::lower(trim($row['color']));
                $vendorName = Str::lower(trim($row['vendor_name']));

                $vendor = $vendors->get($vendorName);
                $vendorId = $vendor ? $vendor->vendor_ID : null;
                $vendorName = $vendor ? $vendor->vendor_comp_name : 'NA';

                $existingProduct = Product::where('product_style', $style)
                    ->where('product_color', $color)
                    ->first();

                $subProducts = $normalizedSubProducts[$index] ?? [];

                if (!$existingProduct) {
                    Product::create([
                        'product_style' => $style,
                        'product_color' => $color,
                        'product_size_range' => $row['size_range'],
                        'product_cost' => $row['cost'],
                        'product_wholesale_price' => $row['wholesale_price'],
                        'product_vendor_ID' => $vendorId,
                        'product_vendor_name' => $vendorName,
                        'product_link' => $row['link'],
                        'product_image' => $row['image'],
                        'factory_style' => $row['factory_style'],
                        'sub_products' => $subProducts,
                        'version_year' => $row['version_year'],
                        'product_status' => 1,
                    ]);
                }
            }
        });
    }
}
