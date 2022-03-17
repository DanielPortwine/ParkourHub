<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('premium', function () {
            return Auth::check() && Auth::user()->isPremium();
        });

        if ($stripeApiBase = env('STRIPE_API_BASE')) {
            \Stripe\Stripe::$apiBase = $stripeApiBase;
        }

        Http::macro('convertkit', function () {
            return Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->baseUrl('https://api.convertkit.com/v3/');
        });

        Paginator::useBootstrap();
    }
}
