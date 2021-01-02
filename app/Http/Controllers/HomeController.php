<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Follower;
use App\Hit;
use App\Spot;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
        $followedUsers = Cache::remember('home_followed_users_' . Auth::id(), 60, function() {
            return Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray();
        });
        $followedSpots = Cache::remember('home_followed_spots_' . Auth::id(), 60, function() use($followedUsers) {
            return Spot::withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->whereIn('user_id', $followedUsers)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        });
        $followedChallenges = Cache::remember('home_followed_challenges_' . Auth::id(), 60, function() use($followedUsers) {
            return Challenge::withCount('entries')
                ->with(['entries', 'reports', 'user'])
                ->whereIn('user_id', $followedUsers)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        });
        $hits = Cache::remember('home_hits_' . Auth::id(), 60, function() {
            return Hit::with(['spot', 'user'])
                ->where('user_id', Auth::id())
                ->whereHas('spot')
                ->whereNull('completed_at')
                ->inRandomOrder()
                ->limit(4)
                ->pluck('spot_id')
                ->toArray();
        });
        $hitlist = Cache::remember('home_hitlist_' . Auth::id(), 60, function() use($hits) {
            return Spot::withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->whereIn('id', $hits)
                ->limit(4)
                ->get();
        });
        $hometownBoundaries = explode(',', Auth::user()->hometown_bounding);
        $hometownName = explode(',', Auth::user()->hometown_name)[0];
        if (count($hometownBoundaries) !== 4) {
            $hometownBoundaries = [0, 0, 0, 0];
        }
        $hometownSpots = Cache::remember('home_hometown_spots_' . Auth::id(), 60, function() use($hometownBoundaries) {
            return Spot::withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
                ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]])
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        });
        $userStats = [];
        /*$userStats = Cache::remember('user_home_stats', 600, function() {
            return [
                'spotsCreated' => count(Spot::where('user_id', Auth::id())->get()),
                'challengesCreated' => count(Challenge::where('user_id', Auth::id())->get()),
                'uncompletedHits' => count(Hit::where('user_id', Auth::id())->whereNull('completed_at')->get()),
                'completedHits' => count(Hit::where('user_id', Auth::id())->whereNotNull('completed_at')->get()),
                'followers' => Auth::user()->followers_quantified,
                'following' => count(Auth::user()->following),
                'age' => Carbon::parse(User::where('id', Auth::id())->pluck('created_at')[0])->diffInDays(Carbon::now()),
            ];
        });*/
        $hometownChallenges = Cache::remember('home_hometown_challenges_' . Auth::id(), 60, function() use($hometownBoundaries) {
            return Challenge::withCount('entries')
                ->with(['entries', 'reports', 'user'])
                ->where('user_id', Auth::id())
                ->whereHas('spot', function ($q) use ($hometownBoundaries) {
                    $q->whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
                        ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]]);
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        });

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
