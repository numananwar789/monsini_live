<?php

namespace App\Http\Controllers;

use App\Models\EmailBody;
use Illuminate\Http\Request;

class EmailBodyController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->admin_role === 'customer') {
            return redirect()->intended('customer/products');
        }

        // Ensure templates exist
        $vendorTemplate = EmailBody::getTemplate('vendor');
        $customerTemplate = EmailBody::getTemplate('customer');
        $stockCustomerTemplate = EmailBody::getTemplate('stock_customer');

        return view('email-templates.index', compact(
            'vendorTemplate',
            'customerTemplate',
            'stockCustomerTemplate'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'email_body' => 'required|string',
            'email_role' => 'required|in:vendor,customer,stock_customer'
        ]);

        $template = EmailBody::where('email_role', $validated['email_role'])->firstOrFail();
        // $template->update(['email_body' => nl2br($validated['email_body'])]);
        $template->update(['email_body' => $validated['email_body']]);

        return back()->with('success', 'Email template updated successfully');
    }
}
