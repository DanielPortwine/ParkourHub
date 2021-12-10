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
        if (empty($event->attendees()->where('user_id', $request['user'])->first())) {
            $event->attendees()->attach($request['user'], ['accepted' => $event->accept_method === 'none']);

            // notify the challenge creator that someone enters a challenge
            /*$creator = User::where('id', $entry->challenge->user_id)->first();
            if ($creator->id != Auth::id() && in_array(setting('notifications_entry', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
                $creator->notify(new ChallengeEntered($entry));
            }*/

            return redirect()->back()->with('status', 'Successfully attending event');
        }

        return redirect()->back()->with('status', 'You are already attending this event');
    }
    public function update(Request $request)
    {
        $event = Event::with(['attendees'])->where('id', $request['event'])->first();
        if (empty($event->attendees()->where('user_id', $request['user'])->first())) {
            $event->attendees()->attach($request['user'], ['accepted' => $request['accepted'], 'comment' => $request['comment']]);

            // notify the challenge creator that someone enters a challenge
            /*$creator = User::where('id', $entry->challenge->user_id)->first();
            if ($creator->id != Auth::id() && in_array(setting('notifications_entry', 'on-site', $creator->id), ['on-site', 'email', 'email-site'])) {
                $creator->notify(new ChallengeEntered($entry));
            }*/

            return redirect()->back()->with('status', 'Successfully attending event');
        }

        return redirect()->back()->with('status', 'You are already attending this event');
    }
}
