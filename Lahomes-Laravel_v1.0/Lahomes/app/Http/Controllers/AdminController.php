<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // এটি admin ফোল্ডারের ভেতরের dashboard.blade.php ফাইলকে দেখাবে
        return view('dashboards.analytics'); 
    }
}
