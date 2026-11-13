<?php

namespace App\Providers;

use App\Listeners\MergeGuestCart;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch::configureUsing(function (\BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch $switch) {
            $switch->locales(['vi', 'en']);
        });

        Event::listen(Login::class, MergeGuestCart::class);

        // Tự động xóa file public/hot nếu được đưa lên môi trường hosting/domain thực tế
        if (file_exists(public_path('hot')) && ! in_array(request()->getHost(), ['localhost', '127.0.0.1', '::1'])) {
            @unlink(public_path('hot'));
        }

        View::composer('layouts.app', function ($view) {
            $view->with([
                'cartCount' => app(CartService::class)->count(),
                'allNavCategories' => \App\Models\Category::where('is_active', true)
                    ->orderBy('sort_order')
                    ->withCount('products')
                    ->get(),
            ]);
        });
    }
}
