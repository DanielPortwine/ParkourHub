<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Follower;
use App\Scopes\VisibilityScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
}
