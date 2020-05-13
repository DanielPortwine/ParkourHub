<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AjaxController extends Controller
{
    public function searchHometown(Request $request, $hometown)
    {
        if (!$request->ajax()) {
            return back();
        }

        if (Cache::has('hometown_' . $hometown)) {
            $results = Cache::get('hometown_' . $hometown);
        } else {
            $response = Http::get('https://nominatim.openstreetmap.org/search?q=' . $hometown . '&format=json&limit=10');
            $results = $response->json();
            Cache::add('hometown_' . $hometown, $results, 2419200); // cache results for 28 days
        }

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
