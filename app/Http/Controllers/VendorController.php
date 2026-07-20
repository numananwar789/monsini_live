<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Imports\VendorsImport;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {

        
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }
        
        $vendors = Vendor::all();
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'fax' => 'nullable|string|max:20',
            'agent' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        // Check if vendor already exists
        $existingVendor = Vendor::where('vendor_name', strtolower($request->name))
            ->where('vendor_comp_name', strtolower($request->company_name))
            ->where('vendor_email', $request->email)
            ->first();

        if ($existingVendor) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vendor with these details already exists');
        }

        // Create new vendor
        Vendor::create([
            'vendor_name' => strtolower($request->name),
            'vendor_comp_name' => strtolower($request->company_name),
            'vendor_address' => $request->address,
            'vendor_phone' => $request->phone ?? "",
            'vendor_email' => $request->email,
            'vendor_fax' => $request->fax ?? "",
            'vendor_agent' => $request->agent ?? "",
            'message' => $request->message ?? "",
        ]);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor added successfully');
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                // Rule::unique('dt_vendor', 'vendor_email')->ignore($vendor->vendor_ID, 'vendor_ID')
            ],
            'fax' => 'nullable|string|max:20',
            'agent' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'days' => 'nullable|string|max:255',
            'days_stock' => 'nullable|string|max:255',
        ]);

        // Check if vendor already exists with same details (except current one)
        $existingVendor = Vendor::where('vendor_name', strtolower($request->name))
            ->where('vendor_comp_name', strtolower($request->company_name))
            ->where('vendor_email', $request->email)
            ->where('vendor_ID', '!=', $vendor->vendor_ID)
            ->first();

        if ($existingVendor) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Another vendor with these details already exists');
        }

        // Update vendor
        $vendor->update([
            'vendor_name' => strtolower($request->name),
            'vendor_comp_name' => strtolower($request->company_name),
            'vendor_address' => $request->address,
            'vendor_phone' => $request->phone ?? "",
            'vendor_email' => $request->email,
            'vendor_fax' => $request->fax ?? "",
            'vendor_agent' => $request->agent ?? "",
            'message' => $request->message ?? "",
            'vendor_days' => $request->days,
            'vendor_days_stock' => $request->days_stock,
        ]);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully');
    }

    public function import(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|mimes:xls,xlsx'
        // ]);

        // Excel::import(new VendorsImport, $request->file('file'));

        // return redirect()->back()->with('success', 'Vendors imported successfully');
    }
}
