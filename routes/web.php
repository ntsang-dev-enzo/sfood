<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
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

Route::get('/language/{lang}', function ($lang) {
    if (in_array($lang, ['vi', 'en'])) {
        session()->put('locale', $lang);
    }
    return back();
})->name('lang.switch');

Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/lien-he', 'pages.contact')->name('contact');
Route::get('/ma-giam-gia', [\App\Http\Controllers\PromoController::class, 'index'])->name('promo.index');
Route::post('/ma-giam-gia/luu/{coupon}', [\App\Http\Controllers\PromoController::class, 'save'])->name('promo.save');
Route::post('/coupon/validate', [App\Http\Controllers\CouponController::class, 'validateForCart'])->name('coupon.validate');
Route::post('/coupon/remove', [App\Http\Controllers\CouponController::class, 'remove'])->name('coupon.remove');

Route::post('/search/ai-global', [SearchController::class, 'aiSearchGlobal'])->name('search.ai-global');
Route::get('/goi-y', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::post('/lien-he', [PageController::class, 'sendContact'])->name('contact.send');
Route::get('/hoi-dap', [PageController::class, 'faq'])->name('faq');
Route::get('/chinh-sach', [PageController::class, 'policy'])->name('policy');

Route::middleware('auth')->group(function () {
    Route::get('/chat-truc-tuyen/messages', [\App\Http\Controllers\ClientChatController::class, 'fetchMessages'])->name('live-chat.fetch');
    Route::post('/chat-truc-tuyen/send', [\App\Http\Controllers\ClientChatController::class, 'sendMessage'])->name('live-chat.send');
});

Route::post('/ai-chat', [\App\Http\Controllers\ChatController::class, 'send'])->name('ai.chat');
Route::post('/ai-chat/image', [\App\Http\Controllers\ChatController::class, 'chatImage'])->name('ai.chat.image');
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::post('/san-pham/ai-search', [ProductController::class, 'aiSearch'])->name('products.ai-search');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/suggestions', [ProductController::class, 'suggestions'])->name('products.suggestions');
Route::post('/san-pham/{product:slug}/cart', [ProductController::class, 'addToCart'])->name('products.add-to-cart');
Route::post('/san-pham/{product:slug}/favorite', [ProductController::class, 'toggleFavorite'])->middleware('auth')->name('products.favorite');

Route::get('/cong-thuc', [RecipeController::class, 'index'])->name('recipes.index');
Route::post('/cong-thuc/ai-search', [RecipeController::class, 'aiSearch'])->name('recipes.ai-search');
Route::get('/cong-thuc/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
Route::post('/cong-thuc/{recipe:slug}/mua-nhanh', [RecipeController::class, 'quickBuy'])->name('recipes.quick-buy');
Route::post('/cong-thuc/{recipe:slug}/favorite', [RecipeController::class, 'toggleFavorite'])->middleware('auth')->name('recipes.favorite');

Route::post('/gio-hang/cap-nhat', [CartController::class, 'update'])->name('cart.update');
Route::post('/gio-hang/xoa', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/gio-hang/xoa-tat-ca', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/gio-hang/toggle-select', [CartController::class, 'toggleSelect'])->name('cart.toggle_select');
Route::post('/gio-hang/toggle-select-all', [CartController::class, 'toggleSelectAll'])->name('cart.toggle_select_all');

Route::middleware('auth')->group(function () {
    Route::get('/yeu-thich', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::get('/yeu-thich-video', [FavoriteController::class, 'videos'])->name('favorites.videos');

    Route::get('/tram-khuyen-mai', [\App\Http\Controllers\VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers/{coupon}/collect', [\App\Http\Controllers\VoucherController::class, 'collect'])->name('vouchers.collect');

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/don-hang/{order}/hoan-tien', [OrderController::class, 'refund'])->name('orders.refund');

    Route::get('/thong-bao', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/thong-bao/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tai-khoan/mat-khau', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/tai-khoan/dia-chi', [ProfileController::class, 'address'])->name('profile.address');
    Route::get('/tai-khoan/vi-coupon', [ProfileController::class, 'coupons'])->name('profile.coupons');
    // Shipping addresses management
    Route::post('/tai-khoan/dia-chi', [ProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::put('/tai-khoan/dia-chi/{address}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/tai-khoan/dia-chi/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.address.destroy');
    Route::post('/tai-khoan/dia-chi/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('profile.address.default');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
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
