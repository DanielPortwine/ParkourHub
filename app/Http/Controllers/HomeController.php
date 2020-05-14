<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Hit;
use App\Spot;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $hits = Hit::where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->inRandomOrder()
            ->limit(2)
            ->pluck('spot_id')
            ->toArray();
        $hitlist = Spot::whereIn('id', $hits)->get();
        $hometownBoundaries = explode(',', Auth::user()->hometown_bounding);
        $hometownName = explode(',', Auth::user()->hometown_name)[0];
        if (count($hometownBoundaries) !== 4) {
            $hometownBoundaries = [0, 0, 0, 0];
        }
        $hometownSpots = Spot::whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
            ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]])
            ->orderBy('created_at', 'DESC')
            ->limit(4)
            ->get();
        $userStats = [
            'spotsCreated' => count(Spot::where('user_id', Auth::id())->get()),
            'challengesCreated' => count(Challenge::where('user_id', Auth::id())->get()),
            'uncompletedHits' => count(Hit::where('user_id', Auth::id())->whereNull('completed_at')->get()),
            'completedHits' => count(Hit::where('user_id', Auth::id())->whereNotNull('completed_at')->get()),
            'age' => Carbon::parse(User::where('id', Auth::id())->pluck('created_at')[0])->diffInDays(Carbon::now()),
        ];
        $recentChallenges = Challenge::with(['spot'])
            ->where('user_id', Auth::id())
            ->whereHas('spot', function($q) use ($hometownBoundaries) {
                $q->whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
                    ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]]);
            })
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('home', [
            'hitlist' => $hitlist,
            'hometownName' => $hometownName,
            'hometownSpots' => $hometownSpots,
            'userStats' => $userStats,
            'recentChallenges' => $recentChallenges,
        ]);
    }
}
