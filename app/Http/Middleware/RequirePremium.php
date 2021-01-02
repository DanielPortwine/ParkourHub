<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RequirePremium
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isPremium = Cache::remember('premium_' . Auth::id(), 3600, function() {
            return (Auth::check() && Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) ? true : false;
        });
        if (!$isPremium) {
            return redirect('premium');
        }

        return $next($request);
    }
}
