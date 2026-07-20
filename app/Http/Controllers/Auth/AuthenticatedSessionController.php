<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.custom-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
          $customer = \App\Models\Customer::where('user_id', auth()->id())->first();

    // ⭐ Block login if customer is NOT allowed
    if ($customer && $customer->cust_status !== 'allow') {
        Auth::logout();
        return redirect()->route('login')->withErrors([
            'email' => 'Your account is not approved by admin yet.',
        ]);
    }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        // dd(auth()->user()->admin_role);
        if (auth()->user()->admin_role != 'customer') {
            return redirect()->intended(route('products.index', absolute: false));
        }

        return redirect()->intended('customer/products');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }


    public function login(Request $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->forget('url.intended');
        // dd(auth()->user()->admin_role);
        if (auth()->user()->admin_role != 'customer') {
            return redirect()->intended(route('products.index', absolute: false));
        }

        return redirect()->intended('customer/products');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
