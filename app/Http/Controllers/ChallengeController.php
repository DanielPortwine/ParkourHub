<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\ChallengeView;
use App\Models\Follower;
use App\Http\Requests\CreateChallenge;
use App\Http\Requests\EnterChallenge;
use App\Http\Requests\UpdateChallenge;
use App\Notifications\ChallengeCreated;
use App\Notifications\ChallengeEntered;
use App\Notifications\ChallengeWon;
use App\Notifications\SpotChallenged;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            ->with(['entries', 'reports', 'user', 'spot'])
            ->whereHas('spot')
            ->entered(!empty($request['entered']) ? true : false)
            ->difficulty($request['difficulty'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->following(!empty($request['following']) ? true : false)
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

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

        $challenge = Challenge::withTrashed()
            ->with([
                'entries',
                'views',
                'spot',
                'reports',
                'user'
            ])
            ->where('id', $id)
            ->first();

        if (empty($challenge) || ($challenge->deleted_at !== null && Auth::id() !== $challenge->user_id)) {
            abort(404);
        }

        $entries = $challenge->entries()->with(['challenge', 'reports', 'user'])->orderByDesc('created_at')->paginate(10, ['*'], 'entries');
        $entered = !empty($challenge->entries->where('user_id', Auth::id())->first());
        $winner = $challenge->entries->where('winner', true)->first();
        $usersViewed = $challenge->views->pluck('user_id')->toArray();
        if (Auth::check() && !in_array(Auth::id(), $usersViewed) && Auth::id() !== $challenge->user_id) {
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

    public function store(CreateChallenge $request)
    {
        $challenge = new Challenge;
        $challenge->spot_id = $request['spot'];
        $challenge->user_id = Auth::id();
        $challenge->name = $request['name'];
        $challenge->description = $request['description'];
        $challenge->difficulty = empty($request['difficulty']) ? '0' : $request['difficulty'];
        $challenge->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
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
        if ($creator->id != Auth::id() && in_array(setting('notifications_challenge', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
            $creator->notify(new SpotChallenged($challenge));
        }

        // notify followers that user created a challenge
        $followers = Auth::user()->followers()->get();
        foreach ($followers as $follower) {
            if (in_array(setting('notifications_new_challenge', 'on-site', $follower->id), ['on-site', 'email', 'email-site'])) {
                $follower->notify(new ChallengeCreated($challenge));
            }
        }

        return redirect()->route('challenge_view', $challenge->id)->with('status', 'Successfully created challenge');
    }

    public function edit($id)
    {
        $challenge = Challenge::where('id', $id)->first();
        if ($challenge->user_id !== Auth::id()) {
            return redirect()->route('challenge_view', $id);
        }

        return view('challenges.edit', ['challenge' => $challenge]);
    }

    public function update(UpdateChallenge $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $challenge = Challenge::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        if ($challenge->user_id !== Auth::id()) {
            return redirect()->route('challenge_view', $id);
        }
        $challenge->name = $request['name'];
        $challenge->description = $request['description'];
        $challenge->difficulty = empty($request['difficulty']) ? '3' : $request['difficulty'];
        $challenge->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $challenge->youtube = $youtube[0];
            $challenge->youtube_start = $youtube[1] ?? null;
            $challenge->video = null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $challenge->video = Storage::url($video->store('videos/challenges', 'public'));
            $challenge->video_type = $video->extension();
            $challenge->youtube = null;
            $challenge->youtube_start = null;
        }
        $challenge->save();

        return back()->with([
            'status' => 'Successfully updated challenge',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $challenge = Challenge::where('id', $id)->first();
        if ($challenge->user_id === Auth::id()) {
            $challenge->delete();
        } else {
            return redirect()->route('challenge_view', $challenge->id);
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted challenge');
        }

        return back()->with('status', 'Successfully deleted challenge');
    }

    public function recover(Request $request, $id)
    {
        $challenge = Challenge::onlyTrashed()->where('id', $id)->first();

        if (empty($challenge) || $challenge->user_id !== Auth::id()) {
            return back();
        }

        $challenge->restore();

        return back()->with('status', 'Successfully recovered challenge.');
    }

    public function remove(Request $request, $id)
    {
        $challenge = Challenge::withTrashed()->withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();

        if ($challenge->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($challenge->thumbnail)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $challenge->thumbnail));
        }
        if (!empty($challenge->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $challenge->video));
        }

        $challenge->forceDelete();

        return back()->with('status', 'Successfully removed challenge forever.');
    }

    public function report($id)
    {
        $challenge = Challenge::where('id', $id)->first();
        $challenge->report();

        return back()->with('status', 'Successfully reported challenge');
    }

    public function discardReports($id)
    {
        if (!Auth::user()->hasPermissionTo('manage reports')) {
            return back();
        }

        $challenge = Challenge::withTrashed()->withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        $challenge->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }
}
