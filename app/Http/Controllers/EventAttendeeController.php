<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventAttendeeController extends Controller
{
    public function store(Request $request)
    {
        $event = Event::with(['attendees'])->where('id', $request['event'])->first();

        if ($request['user'] != Auth::id() && $event->user_id !== Auth::id()) {
            return back();
        }

        if (empty($event->attendees()->where('user_id', $request['user'])->first())) {
            $event->attendees()->attach($request['user'], ['accepted' => $event->accept_method === 'none' || $event->user_id == $request['user'], 'comment' => $request['comment']]);

            return redirect()->back()->with('status', 'Successfully attending event');
        }

        return back()->with('status', 'You are already attending this event');
    }

    public function update(Request $request, $id)
    {
        $event = Event::with(['attendees'])->where('id', $id)->first();

        if ($request['user'] != Auth::id() && $event->user_id !== Auth::id()) {
            return back();
        }

        if (!empty($event->attendees()->where('user_id', $request['user'])->first())) {
            $event->attendees()->updateExistingPivot($request['user'], ['accepted' => $request['accepted'] === 'true', 'comment' => $request['comment']], false);

            return redirect()->back()->with('status', 'Successfully attending event');
        }

        return back()->with('status', 'You are already attending this event');
    }

    public function delete($event, $user)
    {
        $event = Event::with(['attendees'])->where('id', $event)->first();

        if ($user !== Auth::id() && $event->user_id !== Auth::id()) {
            return back();
        }

        $event->attendees()->detach($user);

        return back()->with('status', 'Successfully cancelled attendance');
    }
}
