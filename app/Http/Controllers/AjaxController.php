<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AjaxController extends Controller
{
    public function searchAddress(Request $request, $search)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = Cache::remember('address_' . $search, 2419200, function() use($search) {
            return Http::get('https://nominatim.openstreetmap.org/search?q=' . $search . '&format=json&addressdetails=1&limit=20')->json();
        });

        return $results;
    }
    public function searchHometown(Request $request, $hometown)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = Cache::remember('address_' . $hometown, 2419200, function() use($hometown) {
            return Http::get('https://nominatim.openstreetmap.org/search?q=' . $hometown . '&format=json&limit=10')->json();
        });

        return $results;
    }

    public function isVerifiedLoggedIn(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        if (Auth::check() && !empty(Auth::user()->email_verified_at)) {
            return true;
        } else {
            return false;
        }
    }
}
