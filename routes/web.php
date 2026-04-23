<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AdminKasirController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOutletController;
use App\Http\Controllers\KasirDashboardController;
use App\Http\Controllers\KasirPesananController;

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
    Route::post('/pesanan/{orderCode}/upload-proof', [PesananController::class, 'uploadProof'])->name('pesanan.upload-proof');
    Route::get('/pesanan/user', [PesananController::class, 'userOrders'])->name('pesanan.user');
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
    // Rute Web untuk Dashboard & Halaman Admin (Return HTML/View)
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/pesanan', [\App\Http\Controllers\AdminPesananController::class, 'index'])->name('admin.pesanan');
    Route::get('/admin/produk', [\App\Http\Controllers\AdminProdukController::class, 'index'])->name('admin.produk');
    Route::get('/admin/outlet', [AdminOutletController::class, 'index'])->name('admin.outlet');
    Route::post('/admin/outlet', [AdminOutletController::class, 'store'])->name('admin.outlet.store');
    Route::put('/admin/outlet/{outlet}', [AdminOutletController::class, 'update'])->name('admin.outlet.update');
    Route::delete('/admin/outlet/{outlet}', [AdminOutletController::class, 'destroy'])->name('admin.outlet.destroy');
    Route::get('/admin/kasir', [AdminKasirController::class, 'index'])->name('admin.kasir');
    Route::post('/admin/kasir', [AdminKasirController::class, 'store'])->name('admin.kasir.store');
    Route::put('/admin/kasir/{kasir}', [AdminKasirController::class, 'update'])->name('admin.kasir.update');
    Route::delete('/admin/kasir/{kasir}', [AdminKasirController::class, 'destroy'])->name('admin.kasir.destroy');
    Route::get('/admin/pelanggan', [\App\Http\Controllers\AdminPelangganController::class, 'index'])->name('admin.pelanggan');
    Route::get('/admin/laporan', [\App\Http\Controllers\AdminLaporanController::class, 'index'])->name('admin.laporan');

    // Rute Produk API
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/admin/banners', [\App\Http\Controllers\BannerController::class, 'index']);
    Route::post('/admin/banners', [\App\Http\Controllers\BannerController::class, 'store']);
    Route::delete('/admin/banners/{id}', [\App\Http\Controllers\BannerController::class, 'destroy']);
    Route::get('/admin/settings', [SettingController::class, 'getSettings']);
    Route::post('/admin/settings', [SettingController::class, 'update']);
});

// ========================================================================
// KASIR (perlu login + role kasir)
// ========================================================================
Route::middleware(['auth', 'kasir'])->group(function () {
    Route::get('/kasir/dashboard', [KasirDashboardController::class, 'index'])->name('kasir.dashboard');
    Route::get('/kasir/pesanan', [KasirPesananController::class, 'index'])->name('kasir.pesanan');
    Route::put('/kasir/pesanan/status', [KasirPesananController::class, 'updateStatus'])->name('kasir.pesanan.status');
    Route::put('/kasir/pesanan/confirm-payment', [KasirPesananController::class, 'confirmPayment'])->name('kasir.pesanan.confirm-payment');
});
