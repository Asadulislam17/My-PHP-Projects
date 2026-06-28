<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;


Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/dashboard', function () {
    
    return view('dashboards.customer'); 
})->middleware(['auth', 'verified', 'role:customer'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified', 'role:admin'])->group(function(){
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});


Route::middleware(['auth', 'verified', 'role:agent'])->group(function(){
    Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
});


Route::get('/customers/details', function () {
    return view('customers.details'); 
})->middleware(['auth', 'role:customer'])->name('customers.details');


require __DIR__.'/auth.php';
