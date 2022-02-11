<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEvent;
use App\Http\Requests\UpdateEvent;
use App\Models\Event;
use App\Models\Spot;
use App\Models\User;
use App\Notifications\ContentCopyrighted;
use App\Notifications\ContentUncopyrighted;
use App\Notifications\EventCreated;
use App\Notifications\EventInvite;
use App\Notifications\EventUpdated;
use App\Scopes\CopyrightScope;
use App\Scopes\LinkVisibilityScope;
use App\Scopes\VisibilityScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function listing(Request $request)
    {
        $defaultOrder = empty($request['historic']) ? 'asc' : 'desc';
        $sort = ['date_time', $defaultOrder];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'updated' => 'updated_at',
                'eventdate' => 'date_time',
                'attendees' => 'attendees_count',
                'spots' => 'spots_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $events = Event::withCount(
            [
                'attendees',
                'spots' => function ($q) {
                    return $q->withoutGlobalScope(VisibilityScope::class); // further investigation: why do the bindings for this count select pollute the aggregate query which doesn't have the count selects in?
                },
            ])
            ->with(['attendees', 'reports', 'user', 'spots'])
            ->attending(!empty($request['attending']))
            ->applied(!empty($request['applied']))
            ->historic(!empty($request['historic']))
            ->hometown(!empty($request['in_hometown']))
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->eventBetween([
                'from' => $request['event_date_from'] ?? null,
                'to' => $request['event_date_to'] ?? null
            ])
            ->following(!empty($request['following']))
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Events',
            'content' => $events,
            'component' => 'event',
            'create' => true,
        ]);
    }

    public function view(Request $request, $id, $tab = 'spots')
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('event_view', $id);
        }

        $event = Event::withTrashed()
            ->withoutGlobalScopes([VisibilityScope::class, CopyrightScope::class])
            ->withGlobalScope('linkVisibility', LinkVisibilityScope::class)
            ->with([
                'spots',
                'attendees',
                'reports',
            ])
            ->where('id', $id)
            ->first();

        if (empty($event) || ($event->deleted_at !== null && Auth::id() !== $event->user_id)) {
            abort(404);
        }

        $userAttendance = $event->attendees()->withPivot('accepted')->where('user_id', Auth::id())->first();
        $attendeesCount = count($event->attendees()->wherePivot('accepted', true)->get());
        $applicantsCount = count($event->attendees()->wherePivot('accepted', false)->get());

        switch ($tab) {
            case 'spots':
                $spots = $event->spots()
                    ->with(['reports', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
            case 'attendees':
                $attendees = $event->attendees()
                    ->where('accepted' , true)
                    ->orderByDesc('name')
                    ->paginate(20, ['*']);
                break;
            case 'comments':
                $comments = $event->comments()
                    ->with(['reports', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
            case 'applicants':
                $applicants = $event->attendees()
                    ->withPivot('comment')
                    ->where('accepted' , false)
                    ->orderByDesc('name')
                    ->paginate(20, ['*']);
                break;
        }

        return view('events.view', [
            'event' => $event,
            'tab' => $tab,
            'userAttendance' => $userAttendance,
            'attendeesCount' => $attendeesCount,
            'applicantsCount' => $applicantsCount,
            'spots' => $spots ?? null,
            'attendees' => $attendees ?? null,
            'comments' => $comments ?? null,
            'applicants' => $applicants ?? null,
        ]);
    }

    public function create()
    {
        $spots = Spot::where('visibility', 'public')->get();
        $users = User::whereNotNull('email_verified_at')->where('id', '!=', Auth::id())->get();

        return view('events.create', [
            'spots' => $spots,
            'users' => $users,
        ]);
    }

    public function store(CreateEvent $request)
    {
        $event = new Event;
        $event->user_id = Auth::id();
        $event->name = $request['name'];
        $event->description = $request['description'];
        $event->date_time = $request['date_time'];
        $event->visibility = $request['visibility'] ?: 'private';
        $event->link_access = $request['link_access'] ?? false;
        $event->accept_method = $request['accept_method'] ?: 'accept';
        if (!empty($request['youtube'])){
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $event->youtube = $youtube[0];
            $event->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            $video = $request->file('video');
            $event->video = Storage::url($video->store('videos/events', 'public'));
            $event->video_type = $video->extension();
        }
        $event->thumbnail = Storage::url($request->file('thumbnail')->store('images/events', 'public'));
        $event->save();

        $event->spots()->attach($request['spots']);
        if ($request['accept_method'] === 'invite' && !empty($request['users'])) {
            $event->attendees()->attach($request['users'], ['accepted' => false]);
        }

        // notify followers that user created an event
        $followers = Auth::user()->followers()->get();
        foreach ($followers as $follower) {
            if (in_array(setting('notifications_new_event', 'on-site', $follower->id), ['on-site', 'email', 'email-site'])) {
                $follower->notify(new EventCreated($event));
            }
        }

        // notify invitees of their invitation
        if ($event->accept_method === 'invite') {
            foreach ($event->attendees as $invitee) {
                if (in_array(setting('notifications_event_invite', 'on-site', $invitee->id), ['on-site', 'email', 'email-site'])) {
                    $invitee->notify(new EventInvite($event));
                }
            }
        }

        return redirect()->route('event_view', $event->id)->with('status', 'Successfully created event');
    }

    public function edit(Request $request, $id)
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('event_edit', $id);
        }

        $event = Event::withoutGlobalScope(CopyrightScope::class)
            ->with(['spots', 'attendees'])
            ->where('id', $id)
            ->first();

        if ($event->user_id !== Auth::id()) {
            return redirect()->route('event_view', $id);
        }

        $spots = Spot::where('visibility', 'public')->get();
        $users = User::whereNotNull('email_verified_at')->where('id', '!=', Auth::id())->get();
        $currentSpots = $event->spots()->pluck('id')->toArray();
        $attendees = $event->attendees()->pluck('id')->toArray();

        return view('events.edit', [
            'event' => $event,
            'spots' => $spots,
            'users' => $users,
            'currentSpots' => $currentSpots,
            'attendees' => $attendees,
        ]);
    }

    public function update(UpdateEvent $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $userId = Auth::id();
        $oldEvent = Event::withoutGlobalScopes([VisibilityScope::class, CopyrightScope::class])
            ->with(['spots', 'attendees'])
            ->where('id', $id)
            ->first();
        $event = Event::withoutGlobalScopes([VisibilityScope::class, CopyrightScope::class])
            ->with(['spots', 'attendees'])
            ->where('id', $id)
            ->first();
        if ($event->user_id != $userId) {
            return redirect()->route('event_view', $id);
        }

        $event->name = $request['name'];
        $event->description = $request['description'] ?: null;
        $event->date_time = $request['date_time'];
        $event->visibility = $request['visibility'] ?: 'private';
        $event->link_access = $request['link_access'] ?? false;
        $event->accept_method = $request['accept_method'] ?: 'accept';
        if (!empty($request['youtube'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $event->video));
            $event->video = null;
            $event->video_type = null;
            $youtube = explode('t=', str_replace(['https://youtu.be/', 'https://www.youtube.com/watch?v=', '&', '?'], '', $request['youtube']));
            $event->youtube = $youtube[0];
            $event->youtube_start = $youtube[1] ?? null;
        } else if (!empty($request['video'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $event->video));
            $event->youtube = null;
            $event->youtube_start = null;
            $video = $request->file('video');
            $event->video = Storage::url($video->store('videos/events', 'public'));
            $event->video_type = $video->extension();
        }
        if (!empty($request['thumbnail'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $event->thumbnail));
            $event->thumbnail = Storage::url($request->file('thumbnail')->store('images/events', 'public'));
        }
        $event->save();

        $event->spots()->sync($request['spots']);
        $attendees = $event->attendees;
        if ($request['accept_method'] === 'invite' && !empty($request['users'])) {
            foreach (User::whereIn('id', $request['users'])->get() as $user) {
                if (!in_array($user->id, $attendees->pluck('id')->toArray())) {
                    $event->attendees()->attach($user->id);
                    if (in_array(setting('notifications_event_invite', 'on-site', $user->id), ['on-site', 'email', 'email-site'])) {
                        $user->notify(new EventInvite($event));
                    }
                }
            }
        }

        // notify followers that user updated an event
        if ($event->visibility !== 'private' &&
            (
                $event->name !== $oldEvent->name ||
                $event->description !== $oldEvent->description ||
                $event->date_time !== $oldEvent->date_time ||
                $event->spots != $oldEvent->spots
            )
        ) {
            $followers = Auth::user()->followers()->get();
            foreach ($followers as $follower) {
                if (in_array(setting('notifications_event_updated', 'on-site', $follower->id), ['on-site', 'email', 'email-site'])) {
                    $follower->notify(new EventUpdated($event));
                }
            }
        }

        return back()->with([
            'status' => 'Successfully updated event',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $event = Event::withoutGlobalScope(CopyrightScope::class)
            ->where('id', $id)
            ->first();

        if ($event->user_id === Auth::id()) {
            $event->delete();
        } else {
            return redirect()->route('event_view', $event->id);
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted event');
        }

        return back()->with('status', 'Successfully deleted event');
    }

    public function recover($id)
    {
        $event = Event::withoutGlobalScope(CopyrightScope::class)
            ->onlyTrashed()
            ->where('id', $id)
            ->first();

        if (empty($event) ||  $event->user_id !== Auth::id()) {
            return back();
        }

        $event->restore();

        return back()->with('status', 'Successfully recovered event');
    }

    public function remove($id)
    {
        $event = Event::withoutGlobalScope(CopyrightScope::class)
            ->withTrashed()
            ->where('id', $id)
            ->first();

        if ($event->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($event->thumbnail)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $event->thumbnail));
        }

        if (!empty($event->video)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $event->video));
        }

        $event->forceDelete();

        return back()->with('status', 'Successfully removed event forever');
    }

    public function report($id)
    {
        $event = Event::where('id', $id)->first();

        $event->report();

        return back()->with('status', 'Successfully reported event');
    }

    public function discardReports($id)
    {
        $event = Event::where('id', $id)->first();

        if (!Auth::user()->hasPermissionTo('manage reports') || $event->user_id === Auth::id()) {
            return back();
        }

        $event->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function setCopyright($id)
    {
        if (!Auth::user()->hasPermissionTo('manage copyright')) {
            return back();
        }

        $event = Event::withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();
        $event->copyright_infringed_at = now();
        $event->save();

        $event->user->notify(new ContentCopyrighted('event', $event));

        return back()->with('status', 'Successfully marked content as a copyright infringement');
    }

    public function removeCopyright($id)
    {
        if (!Auth::user()->hasPermissionTo('manage copyright')) {
            return back();
        }

        $event = Event::withoutGlobalScopes([VisibilityScope::class, CopyrightScope::class])->where('id', $id)->first();
        $event->copyright_infringed_at = null;
        $event->save();

        $event->user->notify(new ContentUncopyrighted('event', $event));

        return back()->with('status', 'Successfully marked content as no longer a copyright infringement');
    }
}
