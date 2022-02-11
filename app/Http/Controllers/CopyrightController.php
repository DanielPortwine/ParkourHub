<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Equipment;
use App\Models\Event;
use App\Models\Movement;
use App\Models\Spot;
use App\Models\Workout;
use App\Scopes\CopyrightScope;
use Illuminate\Http\Request;

class CopyrightController extends Controller
{
    public function index(Request $request, $tab = 'spots')
    {
        $spots = $events = $challenges = $entries = $movements = $equipment = $workouts = null;
        switch ($tab) {
            case 'spots':
                $spots = Spot::withoutGlobalScope(CopyrightScope::class)
                    ->with(['hits', 'reviews', 'reports', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'events':
                $events = Event::withoutGlobalScope(CopyrightScope::class)
                    ->with(['reports', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'challenges':
                $challenges = Challenge::withoutGlobalScope(CopyrightScope::class)
                    ->withCount('entries')
                    ->with(['entries', 'reports', 'spot', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->whereHas('spot')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'entries':
                $entries = ChallengeEntry::withoutGlobalScope(CopyrightScope::class)
                    ->with(['challenge', 'reports', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->whereHas('challenge')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'movements':
                $movements = Movement::withoutGlobalScope(CopyrightScope::class)
                    ->with(['reports', 'moves', 'user', 'spots'])
                    ->whereNotNull('copyright_infringed_at')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'equipment':
                $equipment = Equipment::withoutGlobalScope(CopyrightScope::class)
                    ->withCount(['movements'])
                    ->with(['movements', 'reports', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
            case 'workouts':
                $workouts = Workout::withoutGlobalScope(CopyrightScope::class)
                    ->withCount(['movements'])
                    ->with(['movements', 'user'])
                    ->whereNotNull('copyright_infringed_at')
                    ->orderByDesc('copyright_infringed_at')
                    ->paginate(20);
                break;
        }

        return view('copyright_infringements', [
            'request' => $request,
            'spots' => $spots,
            'linkSpotOnComment' => $linkSpotOnComment ?? false,
            'events' => $events,
            'challenges' => $challenges,
            'entries' => $entries,
            'movements' => $movements,
            'equipments' => $equipment,
            'workouts' => $workouts,
            'tab' => $tab,
        ]);
    }
}
