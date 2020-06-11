<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\ChallengeView;
use App\Http\Requests\CreateChallenge;
use App\Http\Requests\EnterChallenge;
use App\Http\Requests\UpdateChallenge;
use App\Notifications\ChallengeCreated;
use App\Notifications\ChallengeEntered;
use App\Notifications\ChallengeWon;
use App\Notifications\SpotChallenged;
use App\Report;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{
    public function listing(Request $request)
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

        $challenges = Challenge::withCount('entries')
            ->entered(!empty($request['entered']) ? true : false)
            ->difficulty($request['difficulty'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->following(!empty($request['following']) ? true : false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Challenges',
            'content' => $challenges,
            'component' => 'challenge',
        ]);
    }

    public function view(Request $request, $id)
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('challenge_view', $id);
        }
        $challenge = Challenge::where('id', $id)->first();
        $entries = $challenge->entries()->orderByDesc('created_at')->paginate(10, ['*'], 'entries')->fragment('entries');
        $entered = !empty(
            ChallengeEntry::where('challenge_id', $id)->where('user_id', Auth::id())->first()
        );
        $winner = ChallengeEntry::where('challenge_id', $id)->where('winner', true)->first();
        $usersViewed = ChallengeView::where('challenge_id', $id)->pluck('user_id')->toArray();
        if (!in_array(Auth::id(), $usersViewed) && Auth::id() !== $challenge->user_id) {
            $view = new ChallengeView;
            $view->challenge_id = $id;
            $view->user_id = Auth::id();
            $view->save();
        }

        return view('challenges.view', [
            'challenge' => $challenge,
            'entries' => $entries,
            'entered' => $entered,
            'winner' => $winner,
        ]);
    }

    public function create(CreateChallenge $request)
    {
        $challenge = new Challenge;
        $challenge->spot_id = $request['spot'];
        $challenge->user_id = Auth::id();
        $challenge->name = $request['name'];
        $challenge->description = $request['description'];
        $challenge->difficulty = empty($request['difficulty']) ? '0' : $request['difficulty'];
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $challenge->youtube = $youtube[0];
            $challenge->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $challenge->video = Storage::url($video->store('videos/challenges', 'public'));
            $challenge->video_type = $video->extension();
        }
        $challenge->thumbnail = Storage::url($request->file('thumbnail')->store('images/challenges', 'public'));
        $challenge->save();

        // notify the spot creator that someone created a challenge
        $creator = User::where('id', $challenge->spot->user_id)->first();
        if ($creator->id != Auth::id() && in_array(setting('notifications_challenge', null, $creator->id), ['on-site', 'email', 'email-site'])) {
            $creator->notify(new SpotChallenged($challenge));
        }

        // notify followers that user created a challenge
        $followers = Auth::user()->followers()->get();
        foreach ($followers as $follower) {
            if (in_array(setting('notifications_new_challenge', null, $follower->id), ['on-site', 'email', 'email-site'])) {
                $follower->notify(new ChallengeCreated($challenge));
            }
        }

        return redirect()->back()->with('status', 'Successfully created challenge');
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
        $challenge->difficulty = empty($request['difficulty']) ? '3' : $request['difficulty'];
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
            $challenge->youtube = $youtube[0];
            $challenge->youtube_start = $youtube[1] ?? null;
            $challenge->video = null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $challenge->video = Storage::url($video->store('videos/challenges', 'public'));
            $challenge->video_type = $video->extension();
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
        if ($challenge->user_id === Auth::id()) {
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
                $youtube = explode('t=', str_replace('https://youtu.be/?', '', str_replace('&', '', str_replace('https://www.youtube.com/watch?v=', '', $request['youtube']))));
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
            if ($creator->id != Auth::id() && in_array(setting('notifications_entry', null, $creator->id), ['on-site', 'email', 'email-site'])) {
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
            if ($winner->id != Auth::id() && in_array(setting('notifications_challenge_won', null, $winner->id), ['on-site', 'email', 'email-site'])) {
                $winner->notify(new ChallengeWon($entry));
            }

            $challenge = Challenge::where('id', $entry->challenge_id)->first();
            $challenge->won = true;
            $challenge->save();

            return back()->with('status', 'Successfully appointed the winner of this challenge');
        }

        return redirect()->back()->with('status', 'This challenge has already been won');
    }

    public function report($id)
    {
        $report = new Report;
        $report->reportable_id = $id;
        $report->reportable_type = 'App\Challenge';
        $report->user_id = Auth::id();
        $report->save();

        return back()->with('status', 'Successfully reported Challenge.');
    }

    public function reportEntry($id)
    {
        $report = new Report;
        $report->reportable_id = $id;
        $report->reportable_type = 'App\ChallengeEntry';
        $report->user_id = Auth::id();
        $report->save();

        return back()->with('status', 'Successfully reported Challenge Entry.');
    }

    public function deleteReported($id)
    {
        Challenge::where('id', $id)->first()->forceDelete();

        return redirect()->route('challenge_listing')->with('status', 'Successfully deleted Challenge and its Entries.');
    }

    public function deleteReportedEntry($id)
    {
        ChallengeEntry::where('id', $id)->first()->forceDelete();

        return redirect()->route('challenge_listing')->with('status', 'Successfully deleted Entry.');
    }
}
