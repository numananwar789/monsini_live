<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MigrationController extends Controller
{
    public function migrateCustomers()
    {
        $customers = Customer::whereNull('user_id')->get();
        $count = 0;

        foreach ($customers as $customer) {
            // Skip if username or email is missing
            if (!$customer->cust_username) {
                continue;
            }

            if(!$customer->cust_email){
                $customer->cust_email = $customer->cust_username . "@gmail.com";
            }

            // Check for existing user with same email or username
            $existing = User::where('email', $customer->cust_email)
                            ->orWhere('user_name', $customer->cust_username)
                            ->first();
            if ($existing) {
                $customer->user_id = $existing->id;
                $customer->save();
                continue;
            }

           
            $user = User::create([
                'name' => $customer->f_name . ' ' . $customer->l_name,
                'email' => $customer->cust_email,
                'user_name' => $customer->cust_username,
                'password' => Hash::make($customer->cust_password),
                'admin_role' => 'customer',
                'admin_status' => "allow",
            ]);

            $customer->user_id = $user->id;
            $customer->save();
            $count++;
        }

        return "✅ Migrated {$count} customers.";
    }
}
