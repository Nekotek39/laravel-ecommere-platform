<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Shop;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop
|--------------------------------------------------------------------------
*/

Route::get('/', Shop\HomeController::class)->name('home');

Route::get('/products', [Shop\ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [Shop\ProductController::class, 'show'])->name('products.show');
Route::get('/categories/{category}', [Shop\CategoryController::class, 'show'])->name('categories.show');

Route::prefix('cart')->name('cart.')->controller(Shop\CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/items', 'store')->name('items.store');
    Route::patch('/items/{product:id}', 'update')->name('items.update')->withTrashed();
    Route::delete('/items/{product:id}', 'destroy')->name('items.destroy')->withTrashed();
    Route::delete('/', 'clear')->name('clear');
    Route::post('/coupon', 'applyCoupon')->name('coupon.apply');
    Route::delete('/coupon', 'removeCoupon')->name('coupon.remove');
});

Route::prefix('checkout')->name('checkout.')->controller(Shop\CheckoutController::class)->group(function () {
    Route::get('/', 'create')->name('create');
    Route::post('/', 'store')->name('store')->middleware('throttle:10,1');
    Route::get('/thank-you/{order}', 'success')->name('success');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/register', [Auth\RegisterController::class, 'store'])->middleware('throttle:10,1');

    Route::get('/login', [Auth\LoginController::class, 'create'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'store']);

    Route::get('/forgot-password', [Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [Auth\PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:6,1');

    Route::get('/reset-password/{token}', [Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [Auth\NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [Auth\LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer account
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('dashboard');

    Route::get('/profile', [Account\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Account\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [Account\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [Account\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('addresses', Account\AddressController::class)
        ->except('show')
        ->parameters(['addresses' => 'address'])
        ->names('addresses');

    Route::get('/orders', [Account\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Account\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [Account\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/reorder', [Account\OrderController::class, 'reorder'])->name('orders.reorder');

    Route::get('/wishlist', [Shop\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product:id}', [Shop\WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

Route::post('/products/{product}/reviews', [Shop\ReviewController::class, 'store'])
    ->middleware(['auth', 'throttle:5,1'])
    ->name('products.reviews.store');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class)->except('show');

    Route::resource('products', Admin\ProductController::class)->except('show')->withTrashed(['edit', 'update']);
    Route::post('products/{product}/restore', [Admin\ProductController::class, 'restore'])->name('products.restore')->withTrashed();
    Route::delete('products/{product}/images/{image}', [Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy')->withTrashed();
    Route::patch('products/{product}/images', [Admin\ProductController::class, 'reorderImages'])->name('products.images.reorder')->withTrashed();

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');

    Route::resource('coupons', Admin\CouponController::class)->except('show');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');

    Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{review}/reject', [Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
});
