<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Notifications\PasswordResetOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomPasswordResetController extends Controller
{
    // Show email request form
    public function showRequestForm()
    {
        return view('auth.custom-passwords.email');
    }

    // Send OTP to email
    public function sendResetEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'This email is not registered']);
        }
        
        // Generate OTP
        $otp = rand(10000, 99999);
        
        // Store in session
        Session::put('reset_otp', $otp);
        Session::put('reset_email', $request->email);
        Session::put('reset_step', 2);
        
        // Send OTP email
        $user->notify(new PasswordResetOtp($otp));
        
        return redirect()->route('custom.password.otp');
    }

    // Show OTP verification form
    public function showOtpForm()
    {
        if (Session::get('reset_step') !== 2) {
            return redirect()->route('custom.password.request');
        }
        
        return view('auth.custom-passwords.otp');
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:5']);
        
        if ($request->otp != Session::get('reset_otp')) {
            return back()->withErrors(['otp' => 'OTP is not correct']);
        }
        
        Session::put('reset_step', 3);
        
        return view('auth.custom-passwords.reset');
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);
        
        $email = Session::get('reset_email');
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->setRememberToken(Str::random(60));
            $user->save();
            
            Customer::where('user_id', $user->id)->update([
                'cust_password' => $request->password
            ]);
            
            // Clear session
            Session::forget(['reset_otp', 'reset_email', 'reset_step']);
            
            return redirect()->route('login')->with('status', 'Password reset successfully!');
        }
        
        return back()->withErrors(['email' => 'User not found']);
    }
}