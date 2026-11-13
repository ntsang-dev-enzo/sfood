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

Route::get('/goi-y', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('contact');
Route::post('/lien-he', [PageController::class, 'sendContact'])->name('contact.send');
Route::get('/hoi-dap', [PageController::class, 'faq'])->name('faq');
Route::get('/chinh-sach', [PageController::class, 'policy'])->name('policy');

Route::middleware('auth')->group(function () {
    Route::get('/chat-truc-tuyen/messages', [\App\Http\Controllers\ClientChatController::class, 'fetchMessages'])->name('live-chat.fetch');
    Route::get('/chat-truc-tuyen/goi-y', [\App\Http\Controllers\ClientChatController::class, 'suggestions'])->name('live-chat.suggestions');
    Route::post('/chat-truc-tuyen/send', [\App\Http\Controllers\ClientChatController::class, 'sendMessage'])->name('live-chat.send');
});

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/suggestions', [ProductController::class, 'suggestions'])->name('products.suggestions');
Route::post('/san-pham/{product:slug}/cart', [ProductController::class, 'addToCart'])->name('products.add-to-cart');
Route::post('/san-pham/{product:slug}/favorite', [ProductController::class, 'toggleFavorite'])->middleware('auth')->name('products.favorite');

Route::get('/cong-thuc', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/cong-thuc/{recipe:slug}', [RecipeController::class, 'show'])->name('recipes.show');
Route::post('/cong-thuc/{recipe:slug}/mua-nhanh', [RecipeController::class, 'quickBuy'])->name('recipes.quick-buy');
Route::post('/cong-thuc/{recipe:slug}/favorite', [RecipeController::class, 'toggleFavorite'])->middleware('auth')->name('recipes.favorite');

Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart', fn() => redirect()->route('cart.index'))->name('cart');
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
    Route::get('/don-hang/{order}/huy-thanh-toan-qr', [OrderController::class, 'paymentCancel'])->name('orders.payment-cancel');
    Route::post('/don-hang/{order}/doi-sang-cod', [OrderController::class, 'switchToCod'])->name('orders.switch-cod');
    Route::post('/don-hang/{order}/cap-nhat-dia-chi', [OrderController::class, 'updateAddress'])->name('orders.update-address');
    Route::get('/don-hang/{order}/tiep-tuc-thanh-toan-qr', [OrderController::class, 'repayQr'])->name('orders.repay-qr');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/don-hang/{order}/hoan-tien', [OrderController::class, 'refund'])->name('orders.refund');
    Route::post('/don-hang/{order}/mua-lai', [OrderController::class, 'reorder'])->name('orders.reorder');

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

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login']);
    Route::get('/dang-ky', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register']);

    Route::get('/quen-mat-khau', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/quen-mat-khau', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/dat-lai-mat-khau/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [PasswordResetController::class, 'resetPassword'])->name('password.store');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/payment/webhook/payos', [PaymentController::class, 'payosWebhook'])->name('payment.payos.webhook');

// Fallback phục vụ file public storage khi symlink public/storage trên host bị hỏng hoặc chưa chạy storage:link
Route::get('/storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    if (! file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.fallback');

// Fallback phục vụ assets build khi document root trên host trỏ vào thư mục gốc thay vì /public
Route::get('/build/{path}', function (string $path) {
    $fullPath = public_path('build/' . $path);
    if (! file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    $mime = str_ends_with($path, '.css') ? 'text/css' : (str_ends_with($path, '.js') ? 'application/javascript' : null);
    $headers = ['Cache-Control' => 'public, max-age=31536000'];
    if ($mime) {
        $headers['Content-Type'] = $mime;
    }
    return response()->file($fullPath, $headers);
})->where('path', '.*')->name('build.fallback');

// Route phục vụ favicon.ico đảm bảo luôn trả về icon dù host cấu hình thư mục nào
Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    if (! file_exists($path)) {
        $path = base_path('favicon.ico');
    }
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'image/x-icon',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
    abort(404);
});
