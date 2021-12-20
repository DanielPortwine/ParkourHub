<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateEvent;
use App\Models\Event;
use App\Models\Spot;
use App\Models\User;
use App\Notifications\EventCreated;
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
                'eventdate' => 'date_time',
                'attendees' => 'attendees_count',
                'spots' => 'spots_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $events = Event::withCount(['attendees', 'spots'])
            ->with(['attendees', 'reports', 'user', 'spots'])
            ->attending(!empty($request['attending']) ? true : false)
            ->applied($request['applied'] ?? null)
            ->historic($request['historic'] ? true : false)
            ->hometown(!empty($request['in_hometown']) ? true : false)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->eventBetween([
                'from' => $request['event_date_from'] ?? null,
                'to' => $request['event_date_to'] ?? null
            ])
            ->following(!empty($request['following']) ? true : false)
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

        $event = Event::withoutGlobalScope(VisibilityScope::class)
            ->withTrashed()
            ->linkVisibility()
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
        }

        return view('events.view', [
            'event' => $event,
            'tab' => $tab,
            'userAttendance' => $userAttendance,
            'spots' => $spots ?? null,
            'attendees' => $attendees ?? null,
            'comments' => $comments ?? null,
        ]);
    }

    public function create()
    {
        $spots = Spot::where('visibility', 'public')->get();
        $users = User::whereNotNull('email_verified_at')->get();

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
        $event->link_access = $request['link_access'] ? true : false;
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

        return redirect()->route('event_view', $event->id)->with('status', 'Successfully created event');
    }
}
