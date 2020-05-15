<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\ChallengeView;
use App\Http\Requests\CreateChallenge;
use App\Http\Requests\EnterChallenge;
use App\Http\Requests\UpdateChallenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{
    public function view($id)
    {
        $challenge = Challenge::with(['spot', 'user', 'entries'])->where('id', $id)->first();
        $entered = !empty(
            ChallengeEntry::where('challenge_id', $id)->where('user_id', Auth::id())->first()
        );
        $winner = ChallengeEntry::with(['user'])->where('challenge_id', $id)->where('winner', true)->first();
        $usersViewed = ChallengeView::where('challenge_id', $id)->pluck('user_id')->toArray();
        if (!in_array(Auth::id(), $usersViewed) && Auth::id() !== $challenge->user_id) {
            $view = new ChallengeView;
            $view->challenge_id = $id;
            $view->user_id = Auth::id();
            $view->save();
        }

        return view('challenges.view', [
            'challenge' => $challenge,
            'entered' => $entered,
            'winner' => $winner,
        ]);
    }

    public function create(Request $request)
    {
        return view('challenges.create', ['spot' => $request['spot']]);
    }

    public function save(CreateChallenge $request)
    {
        $challenge = new Challenge;
        $challenge->spot_id = $request['spot'];
        $challenge->user_id = Auth::id();
        $challenge->name = $request['name'];
        $challenge->description = $request['description'];
        if (!empty($request['youtube'])){
            $challenge->youtube = substr($request->youtube, -11);
        } else if (!empty($request['video'])) {
            $challenge->video = Storage::url($request->file('video')->store('videos/challenges', 'public'));
        }
        $challenge->save();

        return redirect()->route('spot_view', $challenge->spot_id);
    }

    public function edit($id)
    {
        $challenge = Challenge::where('id', $id)->first();

        return view('challenges.edit', ['challenge' => $challenge]);
    }

    public function update(UpdateChallenge $request, $id)
    {
        $challenge = Challenge::where('id', $id)->first();
        $challenge->name = $request['name'];
        $challenge->description = $request['description'];
        if (!empty($request['youtube'])){
            $challenge->youtube = substr($request->youtube, -11);
            $challenge->video = null;
        } else if (!empty($request['video'])) {
            $challenge->video = Storage::url($request->file('video')->store('videos/challenges', 'public'));
            $challenge->youtube = null;
        } else if (empty($challenge->video) && empty($challenge->youtube)) {
            return back()->withErrors(['youtube' => 'You must provide either a Youtube link or video file', 'video' => 'You must provide either a video file or Youtube link']);
        }
        $challenge->save();

        return back()->with('status', 'Successfully updated challenge');
    }

    public function delete($id)
    {
        $challenge = Challenge::where('id', $id)->first();
        if ($challenge->user_id === Auth()::id()) {
            $challenge->delete();
        }

        return redirect()->route('home');
    }

    public function enter(EnterChallenge $request, $id)
    {
        if (empty(ChallengeEntry::where('challenge_id', $id)->where('user_id', Auth::id())->first())) {
            $entry = new ChallengeEntry;
            $entry->challenge_id = $id;
            $entry->user_id = Auth::id();
            if (!empty($request['youtube'])) {
                $entry->youtube = substr($request->youtube, -11);
            } else if (!empty($request['video'])) {
                $entry->video = Storage::url($request->file('video')->store('videos/challenge_entries', 'public'));
            }
            $entry->save();

            return redirect()->route('challenge_view', $id);
        }

        return redirect()->route('home')->with('status', 'You have already entered this challenge');
    }

    public function win($id)
    {
        $entry = ChallengeEntry::where('id', $id)->first();
        if (empty($entry->winner)) {
            $entry->winner = true;
            $entry->save();

            $challenge = Challenge::where('id', $entry->challenge_id)->first();
            $challenge->won = true;
            $challenge->save();

            return back()->with('status', 'Successfully appointed the winner of this challenge');
        }

        return redirect()->route('home')->with('status', 'This challenge has already been won');
    }
}
