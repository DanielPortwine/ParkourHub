<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\Hit;
use App\Spot;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $recentSpots = Spot::where('user_id', Auth::id())->orderBy('created_at', 'desc')->limit(3)->get();
        $userStats = [
            'spotsCreated' => count(Spot::where('user_id', Auth::id())->get()),
            'challengesCreated' => count(Challenge::where('user_id', Auth::id())->get()),
            'uncompletedHits' => count(Hit::where('user_id', Auth::id())->whereNull('completed_at')->get()),
            'completedHits' => count(Hit::where('user_id', Auth::id())->whereNotNull('completed_at')->get()),
            'age' => Carbon::parse(User::where('id', Auth::id())->pluck('created_at')[0])->diffInDays(Carbon::now()),
        ];
        $recentChallenges = Challenge::where('user_id', Auth::id())->orderBy('created_at', 'desc')->limit(3)->get();
        $hits = Hit::where('user_id', Auth::id())->whereNull('completed_at')->inRandomOrder()->limit(3)->pluck('spot_id')->toArray();
        $hitlist = Spot::whereIn('id', $hits)->get();

        return view('home', [
            'recentSpots' => $recentSpots,
            'userStats' => $userStats,
            'recentChallenges' => $recentChallenges,
            'hitlist' => $hitlist,
        ]);
    }
}
