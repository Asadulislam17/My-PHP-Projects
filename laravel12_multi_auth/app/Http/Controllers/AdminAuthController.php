<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin_login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // এখানে 'admin' গার্ড দিয়ে লগইন চেক হচ্ছে
        if (Auth::guard('admin')->attempt($credentials)) {
            
            // লারাভেল ১২-এর নিয়মে সেশন সিকিউরিটি ফিক্স করার জন্য এটি আবশ্যক
            $request->session()->regenerate(); 

            
            
            return redirect('admin/dashboard');
        }

        return redirect('admin/login')->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('admin/login');
    }
}
