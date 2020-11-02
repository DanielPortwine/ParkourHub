<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWorkout;
use App\Movement;
use App\Notifications\NewWorkout;
use App\Notifications\WorkoutUpdated;
use App\RecordedWorkout;
use App\Spot;
use App\Workout;
use App\WorkoutMovement;
use App\WorkoutMovementField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class WorkoutController extends Controller
{
    public function index(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $workouts = Cache::remember('workouts_listing_' . implode('_', $request->toArray()), 30, function() use($request, $sort) {
            return Workout::withCount('movements')
                ->where('public', true)
                ->dateBetween([
                    'from' => $request['date_from'] ?? null,
                    'to' => $request['date_to'] ?? null
                ])
                ->orderBy($sort[0], $sort[1])
                ->paginate(20);
        });

        return view('content_listings', [
            'title' => 'Workouts',
            'content' => $workouts,
            'component' => 'workout',
            'create' => true,
        ]);
    }

    public function myWorkouts(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $workouts = Cache::remember('user_workouts_listing_' . $userID . '_' . implode('_', $request->toArray()), 60, function() use($request, $userID, $sort) {
        return Workout::withCount('movements')
            ->where('user_id', $userID)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);
        });

        return view('content_listings', [
            'title' => 'Workouts',
            'content' => $workouts,
            'component' => 'workout',
            'create' => true,
        ]);
    }

    public function view(Request $request, $id, $tab = null)
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('workout_view', $id);
        }

        $workout = Cache::remember('workout_view_' . $id, 30, function() use($id) {
            return Workout::with([
                'user',
                'movements' => function ($q) {
                    $q->where('recorded_workout_id', null);
                },
                'movements.fields',
                'movements.fields.field',
            ])
                ->where('id', $id)
                ->first();
        });

        $recordedWorkouts = RecordedWorkout::where('workout_id', $id)->where('user_id', Auth::id())->paginate(10);
        $spots = Workout::where('id', $id)->first()->spots()->orderByDesc('created_at')->paginate(20);

        return view('workouts.view', [
            'workout' => $workout,
            'recordedWorkouts' => $recordedWorkouts,
            'spots' => $spots,
            'request' => $request,
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        return view('workouts.create');
    }

    public function store(CreateWorkout $request)
    {
        $userId = Auth::id();
        $workout = new Workout;
        $workout->user_id = $userId;
        $workout->name = $request['name'] ?: null;
        $workout->description = $request['description'] ?: null;
        $workout->public = $request['public'] ?: 0;
        $workout->save();

        foreach ($request['movements'] as $movementRequest) {
            if (count($movementRequest) === 1) {
                continue;
            }
            $movement = new WorkoutMovement;
            $movement->user_id = $userId;
            $movement->movement_id = $movementRequest['movement'];
            $movement->workout_id = $workout->id;
            $movement->save();

            foreach ($movementRequest['fields'] as $id => $value) {
                $field = new WorkoutMovementField;
                $field->movement_field_id = $id;
                $field->workout_movement_id = $movement->id;
                $field->value = $value;
                $field->save();
            }
        }

        if (!empty($request['create-record'])) {
            $recordedWorkout = new RecordedWorkout;
            $recordedWorkout->user_id = $userId;
            $recordedWorkout->workout_id = $workout->id;
            $recordedWorkout->save();

            foreach ($request['movements'] as $movementRequest) {
                if (count($movementRequest) === 1) {
                    continue;
                }
                $movement = new WorkoutMovement;
                $movement->user_id = $userId;
                $movement->movement_id = $movementRequest['movement'];
                $movement->workout_id = $workout->id;
                if (!empty($request['create-record'])) {
                    $movement->recorded_workout_id = $recordedWorkout->id;
                }
                $movement->save();

                foreach ($movementRequest['fields'] as $id => $value) {
                    $field = new WorkoutMovementField;
                    $field->movement_field_id = $id;
                    $field->workout_movement_id = $movement->id;
                    $field->value = $value;
                    $field->save();
                }
            }
        }

        // notify followers that user created a workout
        if ($workout->public) {
            $followers = Auth::user()->followers()->get();
            foreach ($followers as $follower) {
                if (in_array(setting('notifications_new_workout', null, $follower->id), ['on-site', 'email', 'email-site']) && $follower->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
                    $follower->notify(new NewWorkout($workout));
                }
            }
        }

        return redirect()->route('workout_view', $workout->id)->with('status', 'Successfully created workout');
    }

    public function edit($id)
    {
        $workout = Workout::with([
                'movements' => function($q) {
                    $q->where('recorded_workout_id', null);
                },
                'movements.fields',
            ])
            ->where('id', $id)
            ->first();

        return view('workouts.edit', [
            'workout' => $workout,
        ]);
    }

    public function update(CreateWorkout $request, $id)
    {
        $userId = Auth::id();
        $workout = Workout::where('id', $id)->first();
        $workout->name = $request['name'] ?: null;
        $workout->description = $request['description'] ?: null;
        $workout->public = $request['public'] ?: 0;
        $workout->save();

        foreach ($request['movements'] as $movementRequest) {
            if (count($movementRequest) === 1) {
                continue;
            }
            if (!empty($movementRequest['id'])) {
                foreach ($movementRequest['fields'] as $id => $value) {
                    $field = WorkoutMovementField::where('id', $id)->first();
                    $field->value = $value;
                    $field->save();
                }
            } else {
                $movement = new WorkoutMovement;
                $movement->user_id = $userId;
                $movement->movement_id = $movementRequest['movement'];
                $movement->workout_id = $workout->id;
                $movement->save();

                foreach ($movementRequest['fields'] as $id => $value) {
                    $field = new WorkoutMovementField;
                    $field->movement_field_id = $id;
                    $field->workout_movement_id = $movement->id;
                    $field->value = $value;
                    $field->save();
                }
            }
        }

        // notify bookmarkers that user updated a workout
        if ($workout->public) {
            $bookmarkers = $workout->bookmarks;
            foreach ($bookmarkers as $bookmarker) {
                if (in_array(setting('notifications_workout_updated', null, $bookmarker->id), ['on-site', 'email', 'email-site']) && $bookmarker->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
                    $bookmarker->notify(new WorkoutUpdated($workout));
                }
            }
        }

        return redirect()->route('workout_view', $workout->id)->with('status', 'Successfully updated workout');
    }

    public function delete($id)
    {
        $workout = Workout::where('id', $id)->first();

        if ($workout->user_id === Auth::id()) {
            $workout->delete();
        }

        return redirect()->route('workout_listing')->with('status', 'Successfully deleted workout');
    }

    public function bookmarks(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $workouts = Auth::user()
            ->bookmarks()
            ->withCount('movements')
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Bookmarked Workouts',
            'content' => $workouts,
            'component' => 'workout',
        ]);
    }

    public function bookmark($id)
    {
        $workout = Workout::where('id', $id)->first();
        $workout->bookmarks()->attach(Auth::id());

        return back()->with('status', 'Successfully bookmarked workout');
    }

    public function unbookmark($id)
    {
        $workout = Workout::where('id', $id)->first();
        $workout->bookmarks()->detach(Auth::id());

        return back()->with('status', 'Successfully removed bookmark');
    }

    public function getMovementFields(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        if ($request['movement'] <= 0) {
            return false;
        }

        $fields = [];
        $movement = Movement::with('fields')->where('id', $request['movement'])->first();

        foreach ($movement->fields()->orderBy('id')->get() as $field) {
            $fields[] = [
                'id' => $field->id,
                'name' => $field->name,
                'type' => $field->input_type,
                'label' => $field->label,
                'unit' => $field->unit,
                'smallText' => $field->small_text,
            ];
        }

        return $fields;
    }

    public function deleteMovement($id)
    {
        $movement = WorkoutMovement::where('id', $id)->first();

        if ($movement->user_id === Auth::id()) {
            $movement->delete();
        }

        return back()->with('status', 'Successfully deleted workout movement');
    }

    public function getWorkouts(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $spotWorkouts = Spot::where('id', $request['spot'])->first()->workouts()->pluck('workouts.id')->toArray();
        $workouts = Workout::whereNotIn('id', $spotWorkouts)->get();

        foreach ($workouts as $workout) {
            $results[] = [
                'id' => $workout->id,
                'text' => $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)),
            ];
        }

        return $results;
    }
}
