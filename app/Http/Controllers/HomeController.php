<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Follower;
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
        $followedUsers = Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray();
        $followedSpots = Spot::whereIn('user_id', $followedUsers)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $followedChallenges = Challenge::whereIn('user_id', $followedUsers)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $hits = Hit::where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->inRandomOrder()
            ->limit(4)
            ->pluck('spot_id')
            ->toArray();
        $hitlist = Spot::whereIn('id', $hits)->limit(4)->get();
        $hometownBoundaries = explode(',', Auth::user()->hometown_bounding);
        $hometownName = explode(',', Auth::user()->hometown_name)[0];
        if (count($hometownBoundaries) !== 4) {
            $hometownBoundaries = [0, 0, 0, 0];
        }
        $hometownSpots = Spot::whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
            ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $userStats = [
            'spotsCreated' => count(Spot::where('user_id', Auth::id())->get()),
            'challengesCreated' => count(Challenge::where('user_id', Auth::id())->get()),
            'uncompletedHits' => count(Hit::where('user_id', Auth::id())->whereNull('completed_at')->get()),
            'completedHits' => count(Hit::where('user_id', Auth::id())->whereNotNull('completed_at')->get()),
            'age' => Carbon::parse(User::where('id', Auth::id())->pluck('created_at')[0])->diffInDays(Carbon::now()),
        ];
        $hometownChallenges = Challenge::with(['spot'])
            ->where('user_id', Auth::id())
            ->whereHas('spot', function($q) use ($hometownBoundaries) {
                $q->whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
                    ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]]);
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('home', [
            'followedSpots' => $followedSpots,
            'followedChallenges' => $followedChallenges,
            'hitlist' => $hitlist,
            'hometownName' => $hometownName,
            'hometownSpots' => $hometownSpots,
            'userStats' => $userStats,
            'hometownChallenges' => $hometownChallenges,
        ]);
    }
}
