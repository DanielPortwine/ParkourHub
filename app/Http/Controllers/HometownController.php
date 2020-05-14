<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Spot;
use Illuminate\Support\Facades\Auth;

class HometownController extends Controller
{
    public function spots()
    {
        $boundaries = explode(',', Auth::user()->hometown_bounding);
        $name = explode(',', Auth::user()->hometown_name)[0];

        $spots = Spot::where('user_id', Auth::id())
            ->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
            ->whereBetween('longitude', [$boundaries[2], $boundaries[3]])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('hometown.spots', ['name' => $name, 'spots' => $spots]);
    }

    public function challenges()
    {
        $boundaries = explode(',', Auth::user()->hometown_bounding);
        $name = explode(',', Auth::user()->hometown_name)[0];

        $challenges = Challenge::with(['spot'])
            ->where('user_id', Auth::id())
            ->whereHas('spot', function($q) use ($boundaries) {
                $q->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                    ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('hometown.challenges', ['name' => $name, 'challenges' => $challenges]);
    }
}
