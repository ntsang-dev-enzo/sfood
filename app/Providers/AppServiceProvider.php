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

        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });
    }
}
