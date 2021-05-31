<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnterChallenge;
use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\User;
use App\Notifications\ChallengeEntered;
use App\Notifications\ChallengeWon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChallengeEntryController extends Controller
{
    public function create(EnterChallenge $request)
    {
        if (empty(ChallengeEntry::where('challenge_id', $request['challenge'])->where('user_id', Auth::id())->first())) {
            $entry = new ChallengeEntry;
            $entry->challenge_id = $request['challenge'];
            $entry->user_id = Auth::id();
            if (!empty($request['youtube'])) {
                $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
                $entry->youtube = $youtube[0];
                $entry->youtube_start = $youtube[1] ?? null;
            } else if (!empty($request['video'])) {
                $video = $request->file('video');
                $entry->video = Storage::url($video->store('videos/challenge_entries', 'public'));
                $entry->video_type = $video->extension();
            }
            $entry->save();

            // notify the challenge creator that someone enters a challenge
            $creator = User::where('id', $entry->challenge->user_id)->first();
            if ($creator->id != Auth::id() && in_array(setting('notifications_entry', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
                $creator->notify(new ChallengeEntered($entry));
            }

            return redirect()->back()->with('status', 'Successfully entered challenge ' . $entry->challenge->name);
        }

        return redirect()->back()->with('status', 'You have already entered this challenge');
    }

    public function win($id)
    {
        $entry = ChallengeEntry::where('id', $id)->first();
        if (empty($entry->winner)) {
            $entry->winner = true;
            $entry->save();

            // notify the challenge winner that they won
            $winner = User::where('id', $entry->user_id)->first();
            if ($winner->id != Auth::id() && in_array(setting('notifications_challenge_won', 'on-site', $winner->id), ['on-site', 'email', 'email-site'])) {
                $winner->notify(new ChallengeWon($entry));
            }

            $challenge = Challenge::where('id', $entry->challenge_id)->first();
            $challenge->won = true;
            $challenge->save();

            return back()->with('status', 'Successfully appointed the winner of this challenge');
        }

        return redirect()->back()->with('status', 'This challenge has already been won');
    }

    public function report(ChallengeEntry $challengeEntry)
    {
        $challengeEntry->report();

        return back()->with('status', 'Successfully reported challenge entry');
    }

    public function discardReports(ChallengeEntry $challengeEntry)
    {
        $challengeEntry->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function delete(ChallengeEntry $challengeEntry)
    {
        if ($challengeEntry->user_id === Auth::id()) {
            $challengeEntry->delete();
        }

        return back()->with('status', 'Successfully deleted challenge entry');
    }

    public function recover(Request $request, $id)
    {
        $entry = ChallengeEntry::onlyTrashed()->where('id', $id)->first();

        if (empty($entry) || $entry->user_id !== Auth::id()) {
            return back();
        }

        $entry->restore();

        return back()->with('status', 'Successfully recovered entry.');
    }

    public function remove(Request $request, $id)
    {
        $entry = ChallengeEntry::withTrashed()->where('id', $id)->first();

        if ($entry->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($entry->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $entry->video));
        }

        $entry->forceDelete();

        return back()->with('status', 'Successfully removed entry forever.');
    }
}
