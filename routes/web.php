<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;

// ========================================================================
// HALAMAN UTAMA
// ========================================================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');

// ========================================================================
// AUTHENTICATION (Login, Register, Logout)
// ========================================================================
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/api/user', [AuthController::class, 'user'])->name('api.user');
Route::put('/api/user/update', [AuthController::class, 'updateProfile'])->middleware('auth')->name('api.user.update');

// ========================================================================
// PESANAN (perlu login)
// ========================================================================
Route::middleware('auth')->group(function () {
    Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// ========================================================================
// REVIEW (public read)
// ========================================================================
Route::get('/reviews/{productId}', [ReviewController::class, 'show'])->name('reviews.show');

// ========================================================================
// ADMIN (perlu login + role admin)
// ========================================================================
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/pesanan', [PesananController::class, 'index'])->name('admin.pesanan');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
});
