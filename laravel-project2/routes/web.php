<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\StudentController; 
use App\Http\Controllers\Backend\DistrictController;
use App\Http\Controllers\Backend\SubjectController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update'); 
Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

Route::get('/districts', [DistrictController::class, 'index'])->name('districts.index');    
Route::post('/districts', [DistrictController::class, 'store'])->name('districts.store');
Route::put('/districts/{id}', [DistrictController::class, 'update'])->name('districts.update');
Route::delete('/districts/{id}', [DistrictController::class, 'destroy'])->name('districts.destroy');


Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');


