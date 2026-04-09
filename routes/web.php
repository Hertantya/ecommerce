<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController    as AdminDashboard;
use App\Http\Controllers\Admin\ProductController      as AdminProduct;
use App\Http\Controllers\Admin\CategoryController     as AdminCategory;
use App\Http\Controllers\Admin\OrderController        as AdminOrder;
use App\Http\Controllers\Admin\CustomerController     as AdminCustomer;
use App\Http\Controllers\Admin\ReportController       as AdminReport;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\CartController      as CustomerCart;
use App\Http\Controllers\Customer\OrderController     as CustomerOrder;

// ──────────────────────────────────────────
// Root redirect
// ──────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ──────────────────────────────────────────
// Auth routes (guest only)
// ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ──────────────────────────────────────────
// ADMIN routes  (must be authenticated + admin role)
// ──────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Categories
    Route::get('/categories',          [AdminCategory::class, 'index'])->name('categories.index');
    Route::get('/categories/create',   [AdminCategory::class, 'create'])->name('categories.create');
    Route::post('/categories',         [AdminCategory::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit',[AdminCategory::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}',     [AdminCategory::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}',  [AdminCategory::class, 'destroy'])->name('categories.destroy');

    // Products
    Route::get('/products',            [AdminProduct::class, 'index'])->name('products.index');
    Route::get('/products/create',     [AdminProduct::class, 'create'])->name('products.create');
    Route::post('/products',           [AdminProduct::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit',  [AdminProduct::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}',       [AdminProduct::class, 'update'])->name('products.update');
    Route::delete('/products/{id}',    [AdminProduct::class, 'destroy'])->name('products.destroy');

    // Orders
    Route::get('/orders',                        [AdminOrder::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}',                   [AdminOrder::class, 'show'])->name('orders.show');
    Route::patch('/orders/{id}/status',          [AdminOrder::class, 'updateStatus'])->name('orders.status');

    // Customers
    Route::get('/customers',        [AdminCustomer::class, 'index'])->name('customers.index');
    Route::delete('/customers/{id}',[AdminCustomer::class, 'destroy'])->name('customers.destroy');

    // Reports
    Route::get('/reports/daily', [AdminReport::class, 'daily'])->name('reports.daily');
});

// ──────────────────────────────────────────
// CUSTOMER routes  (must be authenticated + customer role)
// ──────────────────────────────────────────
Route::prefix('customer')->name('customer.')->middleware(['auth', 'customer'])->group(function () {

    // Product catalogue / dashboard
    Route::get('/dashboard',   [CustomerDashboard::class, 'index'])->name('dashboard');

    // Cart
    Route::post('/cart/add',   [CustomerCart::class, 'add'])->name('cart.add');
    Route::post('/cart/remove',[CustomerCart::class, 'remove'])->name('cart.remove');
    Route::get('/cart',        [CustomerCart::class, 'index'])->name('cart.index');

    // Checkout & Orders
    Route::get('/checkout',    [CustomerOrder::class, 'checkout'])->name('checkout');
    Route::post('/order/place',[CustomerOrder::class, 'place'])->name('order.place');
    Route::get('/orders',      [CustomerOrder::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerOrder::class, 'show'])->name('orders.show');
});
