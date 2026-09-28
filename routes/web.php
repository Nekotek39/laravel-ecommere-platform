<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Shop;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop (available to everyone)
|--------------------------------------------------------------------------
*/

Route::get('/', [Shop\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [Shop\ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [Shop\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [Shop\CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [Shop\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [Shop\CartController::class, 'destroy'])->name('cart.destroy');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/register', [Auth\RegisterController::class, 'store']);

    Route::get('/login', [Auth\LoginController::class, 'create'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'store']);
});

Route::post('/logout', [Auth\LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Logged-in customer: checkout and order history
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [Shop\CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [Shop\CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/my-orders', [Account\OrderController::class, 'index'])->name('account.orders.index');
    Route::get('/my-orders/{order}', [Account\OrderController::class, 'show'])->name('account.orders.show');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Moderator and administrator: product management
    Route::middleware('role:admin,moderator')->group(function () {
        Route::resource('products', Admin\ProductController::class)->except('show');
    });

    // Administrator only
    Route::middleware('role:admin')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::resource('users', Admin\UserController::class);

        Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    });
});
