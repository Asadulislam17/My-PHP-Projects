<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/student', [StudentController::class, 'index']);


Route::resource('students', StudentController::class);


Route::resource('products', ProductController::class);


Route::get('notification', [NotificationController::class, 'index']);
Route::get('notification/{type}', [NotificationController::class, 'notification'])->name("notification");

