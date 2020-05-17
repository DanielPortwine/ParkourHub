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
        if (count($boundaries) !== 4) {
            return redirect()->route('user_manage')->with('status', 'You must have a hometown to view Hometown spots');
        }

        $spots = Spot::where('user_id', Auth::id())
            ->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
            ->whereBetween('longitude', [$boundaries[2], $boundaries[3]])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('content_listings', [
            'page' => 'Spots In ' . $name,
            'title' => 'Spots In ' . $name,
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function challenges()
    {
        $boundaries = explode(',', Auth::user()->hometown_bounding);
        $name = explode(',', Auth::user()->hometown_name)[0];
        if (count($boundaries) !== 4) {
            return redirect()->route('user_manage')->with('status', 'You must have a hometown to view Hometown challenges');
        }

        $challenges = Challenge::with(['spot'])
            ->where('user_id', Auth::id())
            ->whereHas('spot', function($q) use ($boundaries) {
                $q->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                    ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('content_listings', [
            'page' => 'Challenges In ' . $name,
            'title' => 'Challenges In ' . $name,
            'content' => $challenges,
            'component' => 'challenge',
        ]);
    }
}
