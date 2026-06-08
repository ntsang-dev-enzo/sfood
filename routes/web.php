<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/goi-y', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::post('/lien-he', [PageController::class, 'sendContact'])->name('contact.send');
Route::get('/hoi-dap', [PageController::class, 'faq'])->name('faq');
Route::get('/chinh-sach', [PageController::class, 'policy'])->name('policy');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::post('/san-pham/ai-search', [ProductController::class, 'aiSearch'])->name('products.ai-search');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/suggestions', [ProductController::class, 'suggestions'])->name('products.suggestions');
Route::post('/san-pham/{product:slug}/cart', [ProductController::class, 'addToCart'])->name('products.add-to-cart');
Route::post('/san-pham/{product:slug}/favorite', [ProductController::class, 'toggleFavorite'])->middleware('auth')->name('products.favorite');

Route::get('/cong-thuc', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/cong-thuc/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
Route::post('/cong-thuc/{recipe:slug}/mua-nhanh', [RecipeController::class, 'quickBuy'])->name('recipes.quick-buy');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart');
Route::post('/gio-hang/cap-nhat', [CartController::class, 'update'])->name('cart.update');
Route::post('/gio-hang/xoa', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/gio-hang/xoa-tat-ca', [CartController::class, 'clear'])->name('cart.clear');

Route::middleware('auth')->group(function () {
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tai-khoan/mat-khau', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
Route::post('/dang-nhap', [AuthController::class, 'login']);
Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
Route::post('/dang-ky', [AuthController::class, 'register']);
Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

Route::get('/quen-mat-khau', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/quen-mat-khau', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'resetPassword'])->name('password.store');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/payment/webhook/payos', [PaymentController::class, 'payosWebhook'])->name('payment.payos.webhook');
