<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'user_name' => 'required|string|max:255|unique:dt_cust,cust_username',
            'email' => 'required|string|email|max:255|unique:dt_cust,cust_email',
            'password' => 'required|string|min:8|confirmed',
            'store' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'fax' => 'nullable|string|max:20',
            'sales_rep' => 'required|string|max:255',
        ]);

 
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->f_name . ' ' . $request->l_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_name' => $request->user_name,
            'admin_role' => 'customer', // or whatever default role you want
            'admin_status' => 'allow',
        ]);

        // Create customer record
        $customer = Customer::create([
            'user_id' => $user->id,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'cust_username' => $request->user_name,
            'cust_comp_name' => $request->store,
            'cust_address' => $request->address,
            'country' => $request->country,
            'cust_password' => $request->password,
            'zip' => $request->zip,
            'cust_phone' => $request->phone,
            'cust_email' => $request->email,
            'cust_fax' => $request->fax ??"",
            'cust_sales_rep' => $request->sales_rep,
            'cust_status' => 'not_allow',
        ]);


        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }
}