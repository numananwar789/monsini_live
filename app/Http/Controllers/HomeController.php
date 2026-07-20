<?php

namespace App\Http\Controllers;

use App\Models\EmailBody;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            if (auth()->user()->admin_role != 'customer') {
                return redirect()->route('products.index');
            }
            return redirect()->route('customer.products.index');
        } else {
            return redirect()->route('login');
        }
    }
}
