<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login.form');
Route::post('admin/login', [AdminAuthController::class, 'login'])->name('admin.login');
Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');



Route::middleware('auth:admin')->group(function () {
    Route::get('admin/dashboard', function () {
        return view('admin.dashboard'); 
    })->name('admin.dashboard');
    
    // ভবিষ্যতে এডমিনের নতুন রাউট এখানে সহজে যোগ করতে পারবেন, বারবার মিডলওয়্যার লেখা লাগবে না
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
