<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\CourtsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingsController::class, 'index'])->name('customer.index');
Route::get('/checkout', fn () => redirect()->route('customer.index'));
Route::post('/checkout', [BookingsController::class, 'store'])->middleware('throttle:20,1')->name('customer.checkout');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [BookingsController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/admin/dashboard', [BookingsController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::patch('/admin/bookings/{id}/status', [BookingsController::class, 'updateStatus'])->name('admin.bookings.updateStatus');
    Route::resource('/admin/courts', CourtsController::class)->except('show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
