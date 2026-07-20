<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
class CustomerImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip if required fields are missing
            if (!isset($row['store_name'])) continue;

            $username = $row['user_name'] ?? Str::random(8);
            
            // Check if customer already exists
            if (!Customer::where('cust_username', $username)->exists()) {


                $password = Hash::make($row['password'] ?? Str::random(10));
                $user = User::create([
                    'name' => $row['f_name'] ?? 'NA' . ' ' .$row['l_name'] ?? 'NA',
                    'email' =>  $row['email'] ?? $username."@gmail.com",
                    'password' =>$password,
                    'user_name' =>  $username,
                    'admin_role' => 'customer', // or whatever default role you want
                    'admin_status' => 'allow',
                ]);

                Customer::create([
                    'user_id' => $user->id,
                    'f_name' => $row['f_name'] ?? 'NA',
                    'l_name' => $row['l_name'] ?? 'NA',
                    'cust_username' => $username,
                    'cust_password' => $row['password'],
                    'cust_comp_name' => $row['store_name'] ?? 'NA',
                    'cust_address' => $row['address'] ?? 'NA',
                    'country' => $row['country'] ?? 'NA',
                    'zip' => is_numeric($row['zip']) ? (int)$row['zip'] : 0,
                    'cust_phone' => $row['phone'] ?? 'NA',
                    'cust_email' => $row['email'] ?? 'NA',
                    'cust_fax' => $row['fax'] ?? 'NA',
                    'cust_sales_rep' => $row['sales_rep'] ?? 'NA',
                    'cust_status' => 1 // Active by default
                ]);
            }
        }
    }
}