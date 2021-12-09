<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

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
}
