<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Cashier\ProductController;
use App\Http\Controllers\Cashier\SummaryController;
use App\Http\Controllers\CustomerController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Beranda: arahkan sesuai role
Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return redirect()->route($user->role === 'kasir' ? 'kasir.orders.index' : 'menu');
})->name('home');

// --- Login & daftar ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// --- Akun PELANGGAN ---
Route::middleware(['auth', EnsureRole::class . ':pelanggan'])->group(function () {
    Route::get('/menu', [CustomerController::class, 'menu'])->name('menu');
    Route::post('/pesanan', [CustomerController::class, 'store'])->name('orders.store');
    Route::get('/pesanan-saya', [CustomerController::class, 'mine'])->name('orders.mine');
});

// --- Akun KASIR ---
Route::middleware(['auth', EnsureRole::class . ':kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::get('/pesanan', [CashierOrderController::class, 'index'])->name('orders.index');
        Route::patch('/pesanan/{order}/status', [CashierOrderController::class, 'updateStatus'])->name('orders.status');
        Route::delete('/pesanan/{order}', [CashierOrderController::class, 'destroy'])->name('orders.destroy');

        Route::resource('produk', ProductController::class)
            ->except(['show'])
            ->parameters(['produk' => 'product']);

        Route::get('/ringkasan', SummaryController::class)->name('summary');
    });
