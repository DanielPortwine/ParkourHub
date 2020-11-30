<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Spot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HometownController extends Controller
{
    public function spots(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
                'views' => 'views_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $boundaries = explode(',', Auth::user()->hometown_bounding);
        $name = explode(',', Auth::user()->hometown_name)[0];
        if (count($boundaries) !== 4) {
            return redirect()->route('user_manage')->with('status', 'You must have a hometown to view hometown spots');
        }

        $spots = Spot::withCount('views')
            ->where('user_id', Auth::id())
            ->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
            ->whereBetween('longitude', [$boundaries[2], $boundaries[3]])
            ->hitlist(!empty($request['on_hitlist']) ? true : false)
            ->ticked(!empty($request['ticked_hitlist']) ? true : false)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Spots In ' . $name,
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function challenges(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'difficulty' => 'difficulty',
                'entries' => 'entries_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $boundaries = explode(',', Auth::user()->hometown_bounding);
        $name = explode(',', Auth::user()->hometown_name)[0];
        if (count($boundaries) !== 4) {
            return redirect()->route('user_manage')->with('status', 'You must have a hometown to view hometown challenges');
        }

        $challenges = Challenge::withCount('entries')
            ->where('user_id', Auth::id())
            ->whereHas('spot', function($q) use ($boundaries) {
                $q->whereBetween('latitude', [$boundaries[0], $boundaries[1]])
                    ->whereBetween('longitude', [$boundaries[2], $boundaries[3]]);
            })
            ->entered(!empty($request['entered']) ? true : false)
            ->difficulty($request['difficulty'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Challenges In ' . $name,
            'content' => $challenges,
            'component' => 'challenge',
        ]);
    }
}
