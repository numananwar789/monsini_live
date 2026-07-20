<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VendorsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Vendor([
            'name' => $row['name'] ?? $row['vendor_name'] ?? null,
            'company_name' => $row['company_name'] ?? $row['vendor_comp_name'] ?? null,
            'address' => $row['address'] ?? $row['vendor_address'] ?? null,
            'phone' => $row['phone'] ?? $row['vendor_phone'] ?? null,
            'email' => $row['email'] ?? $row['vendor_email'] ?? null,
            'fax' => $row['fax'] ?? $row['vendor_fax'] ?? null,
            'agent' => $row['agent'] ?? $row['vendor_agent'] ?? null,
            'days' => $row['days'] ?? $row['vendor_days'] ?? null,
            'days_stock' => $row['days_stock'] ?? $row['vendor_days_stock'] ?? null,
        ]);
    }
}