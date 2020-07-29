<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('premium', function () {
            if (Auth::check()) {
                $condition = Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium');
            }
            return "<?php if ($condition) { ?>";
        });
    }
}
