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
        $followedUsers = Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray();
        $followedSpots = Spot::withCount('views')
            ->with(['reviews', 'reports', 'hits', 'user'])
            ->whereIn('user_id', $followedUsers)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $followedChallenges = Challenge::withCount('entries')
            ->with(['entries', 'reports', 'user'])
            ->whereIn('user_id', $followedUsers)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $hits = Hit::with(['spot', 'user'])
                ->where('user_id', Auth::id())
                ->whereHas('spot')
                ->whereNull('completed_at')
                ->inRandomOrder()
                ->limit(4)
                ->pluck('spot_id')
                ->toArray();
        $hitlist = Spot::withCount('views')
            ->with(['reviews', 'reports', 'hits', 'user'])
            ->whereIn('id', $hits)
            ->limit(4)
            ->get();
        $hometownBoundaries = explode(',', Auth::user()->hometown_bounding);
        $hometownName = explode(',', Auth::user()->hometown_name)[0];
        if (count($hometownBoundaries) !== 4) {
            $hometownBoundaries = [0, 0, 0, 0];
        }
        $hometownSpots = Spot::withCount('views')
            ->with(['reviews', 'reports', 'hits', 'user'])
            ->whereBetween('latitude', [$hometownBoundaries[0], $hometownBoundaries[1]])
            ->whereBetween('longitude', [$hometownBoundaries[2], $hometownBoundaries[3]])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $userStats = [];
        /*$userStats = [
            'spotsCreated' => count(Spot::where('user_id', Auth::id())->get()),
            'challengesCreated' => count(Challenge::where('user_id', Auth::id())->get()),
            'uncompletedHits' => count(Hit::where('user_id', Auth::id())->whereNull('completed_at')->get()),
            'completedHits' => count(Hit::where('user_id', Auth::id())->whereNotNull('completed_at')->get()),
            'followers' => Auth::user()->followers_quantified,
            'following' => count(Auth::user()->following),
            'age' => Carbon::parse(User::where('id', Auth::id())->pluck('created_at')[0])->diffInDays(Carbon::now()),
        ];*/
        $hometownChallenges = Challenge::withCount('entries')
            ->with(['entries', 'reports', 'user'])
            ->where('user_id', Auth::id())
            ->whereHas('spot', function ($q) use ($hometownBoundaries) {
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
