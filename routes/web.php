<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourtsController;
use App\Http\Controllers\BookingsController;

// Customer
Route::get('/', [BookingsController::class, 'index'])
    ->name('customer.index');

Route::get('/customer', [BookingsController::class, 'index'])
    ->name('customer.index');

Route::post('/checkout', [BookingsController::class, 'store'])
    ->name('customer.checkout');

// Admin
