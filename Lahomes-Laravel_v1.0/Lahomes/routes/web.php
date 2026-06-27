<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;

// ১. পাবলিক হোমপেজ
Route::get('/', function () {
    return redirect()->route('login');
});

// ২. সাধারণ কাস্টমার ড্যাশবোর্ড (Role: customer)
Route::get('/dashboard', function () {
    // ফোল্ডারের নাম dashboards এবং ফাইলের নাম customer.blade.php
    return view('dashboards.customer'); 
})->middleware(['auth', 'verified', 'role:customer'])->name('dashboard');

// ৩. কমন প্রোফাইল রাউট (সব লগইন করা ইউজারদের জন্য)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ৪. অ্যাডমিন ড্যাশবোর্ড রাউট (Role: admin)
Route::middleware(['auth', 'verified', 'role:admin'])->group(function(){
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// ৫. এজেন্ট ড্যাশবোর্ড রাউট (Role: agent)
Route::middleware(['auth', 'verified', 'role:agent'])->group(function(){
    Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
});

// এই নতুন রাউটটি যোগ করুন (কাস্টমার ডিটেইলস পেজের জন্য)
Route::get('/customers/details', function () {
    return view('customers.details'); // আপনার views/customers/details.blade.php ফাইল
})->middleware(['auth', 'role:customer'])->name('customers.details');

// Breeze এর নিজস্ব অথেন্টিকেশন রাউট ফাইল
require __DIR__.'/auth.php';
