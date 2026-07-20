<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

use App\Imports\CustomerImport;
use Maatwebsite\Excel\Facades\Excel;


use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        // $this->middleware('redirect.customer');
    }

    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        $customers = Customer::all();
        return view('admin.customers.index', compact('customers'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'cust_username' => 'required|string|max:255|unique:dt_cust,cust_username,' . $customer->cust_ID . ',cust_ID',
            'cust_comp_name' => 'nullable|string|max:255',
            'cust_address' => 'nullable|string',
            'cust_phone' => 'nullable|string|max:20',
            'cust_email' => 'required|email|max:255|unique:dt_cust,cust_email,' . $customer->cust_ID . ',cust_ID',
            'cust_fax' => 'nullable|string|max:20',
            'cust_sales_rep' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $customer->update($validated);

        // dd($customer->user);
        if ($customer->user) {
            $customer->user->update([
                'user_name' => $validated['cust_username'],
            ]);
        }


        if ($request->filled('password')) {
            $hashedPassword = bcrypt($request->password);

            // Update customer table
            $customer->update([
                'cust_password' => $request->password,
            ]);

            // Update related user table
            if ($customer->user) {
                $customer->user->update([
                    'password' => $hashedPassword,
                ]);
            }
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
    }

    public function approve(Customer $customer)
    {
        $customer->update(['cust_status' => 'allow']);
        return redirect()->route('customers.index')
            ->with('success', 'Customer approved successfully');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ]);

        try {
            Excel::import(new CustomerImport, $request->file('file'));

            return redirect()->route('customers.index')
                ->with('success', 'Customers imported successfully!');
        } catch (\Exception $e) {

            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Error importing customers: ' . $e->getMessage());
        }
    }
}
