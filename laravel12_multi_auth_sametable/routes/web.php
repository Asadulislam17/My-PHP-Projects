<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {

   
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');

    });

  
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    });

    
    Route::middleware(['role:student'])->prefix('student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    });

    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
