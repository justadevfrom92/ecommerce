<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountOrderController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');
Route::get('/search', [CatalogController::class, 'search'])->name('search');
Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
Route::get('/categories/{category}', [CatalogController::class, 'category'])->name('categories.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::post('/checkout/{order}/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer account
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'edit'])->name('edit');
    Route::put('/', [AccountController::class, 'update'])->name('update');
    Route::get('/password', [AccountController::class, 'editPassword'])->name('password.edit');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Admin — every route needs admin.access plus its own section permission.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'can:admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    // Catalog
    Route::get('/products', [Admin\ProductController::class, 'index'])->middleware('can:products.view')->name('products.index');
    Route::resource('products', Admin\ProductController::class)->except(['index', 'show'])->middleware('can:products.manage');
    Route::resource('departments', Admin\DepartmentController::class)->except('show')->middleware('can:departments.manage');
    Route::resource('categories', Admin\CategoryController::class)->except('show')->middleware('can:categories.manage');

    // Sales
    Route::get('/orders', [Admin\OrderController::class, 'index'])->middleware('can:orders.view')->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->middleware('can:orders.view')->name('orders.show');
    Route::patch('/orders/{order}', [Admin\OrderController::class, 'update'])->middleware('can:orders.manage')->name('orders.update');

    // People
    Route::get('/users', [Admin\UserController::class, 'index'])->middleware('can:users.view')->name('users.index');
    Route::resource('users', Admin\UserController::class)->except(['index', 'show'])->middleware('can:users.manage');
    Route::resource('roles', Admin\RoleController::class)->except('show')->middleware('can:roles.manage');

    // Settings
    Route::middleware('can:settings.manage')->group(function () {
        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::resource('sliders', Admin\HomeSliderController::class)->except('show');
    });
});
