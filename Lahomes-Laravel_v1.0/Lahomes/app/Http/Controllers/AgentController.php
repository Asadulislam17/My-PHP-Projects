<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function dashboard()
    {
        // এটি agent ফোল্ডারের ভেতরের dashboard.blade.php ফাইলকে দেখাবে
        return view('agent.dashboard'); 
    }
}
