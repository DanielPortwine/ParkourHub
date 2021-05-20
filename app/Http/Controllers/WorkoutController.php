<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Http\Requests\CreateWorkout;
use App\Models\Movement;
use App\Notifications\NewWorkout;
use App\Notifications\WorkoutUpdated;
use App\Models\RecordedWorkout;
use App\Models\Spot;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
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

        $workouts = Workout::withCount('movements')
            ->with(['movements', 'bookmarks', 'user'])
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->search($request['search'] ?? '')
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

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
        $workouts = Workout::withCount('movements')
            ->with(['movements', 'bookmarks', 'user'])
            ->where('user_id', $userID)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

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

        $workout = Workout::withTrashed()
                ->with([
                    'user',
                    'movements',
                    'bookmarks',
                    'spots',
                ])
                ->where('id', $id)
                ->first();

        if (empty($workout) || ($workout->deleted_at !== null && Auth::id() !== $workout->user_id)) {
            return view('errors.404');
        }

        $linkableSpots = null;
        if ($tab === null || $tab === 'movements') {
            $workoutMovements = $workout->movements()
                ->with(['fields'])
                ->whereHas('movement')
                ->whereNull('recorded_workout_id')
                ->paginate(20);
        } else if ($tab === 'recorded') {
            $recordedWorkouts = RecordedWorkout::where('workout_id', $id)
                ->with(['workout', 'movements'])
                ->where('user_id', Auth::id())
                ->paginate(20);
        } else if ($tab === 'spots') {
            $spots = $workout->spots()
                ->withCount('views')
                ->with(['reviews', 'reports', 'hits', 'user'])
                ->orderByDesc('created_at')
                ->paginate(20);
            $linkableSpots = Spot::whereNotIn('id', $workout->spots()->pluck('spots.id')->toArray())->get();
        }

        $displayMovement = $workout->movements()
            ->with(['movement'])
            ->whereHas('movement')
            ->inRandomOrder()
            ->first()
            ->movement;

        return view('workouts.view', [
            'workout' => $workout,
            'workoutMovements' => $workoutMovements ?? null,
            'recordedWorkouts' => $recordedWorkouts ?? null,
            'spots' => $spots ?? null,
            'request' => $request,
            'tab' => $tab,
            'displayMovement' => $displayMovement,
            'linkableSpots' => $linkableSpots,
        ]);
    }

    public function create()
    {
        $movements = Movement::get();

        return view('workouts.create', [
            'movements' => $movements,
        ]);
    }

    public function store(CreateWorkout $request)
    {
        $userId = Auth::id();
        $workout = new Workout;
        $workout->user_id = $userId;
        $workout->name = $request['name'];
        $workout->description = $request['description'] ?: null;
        $workout->visibility = $request['visibility'] ?: 'private';
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
        if ($workout->visibility !== 'private') {
            $followers = Auth::user()->followers()->get();
            foreach ($followers as $follower) {
                if (in_array(setting('notifications_new_workout', 'on-site', $follower->id), ['on-site', 'email', 'email-site']) && $follower->isPremium()) {
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
                    $q->where('recorded_workout_id', null)
                        ->whereHas('movement');
                },
                'movements.fields',
            ])
            ->where('id', $id)
            ->first();

        $movements = Movement::get();

        return view('workouts.edit', [
            'workout' => $workout,
            'movements' => $movements,
        ]);
    }

    public function update(CreateWorkout $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $userId = Auth::id();
        $workout = Workout::where('id', $id)->first();
        $workout->name = $request['name'];
        $workout->description = $request['description'] ?: null;
        $workout->visibility = $request['visibility'] ?: 'private';
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
                if (in_array(setting('notifications_workout_updated', 'on-site', $bookmarker->id), ['on-site', 'email', 'email-site']) && $bookmarker->isPremium()) {
                    $bookmarker->notify(new WorkoutUpdated($workout));
                }
            }
        }

        return back()->with([
            'status' => 'Successfully updated workout',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $workout = Workout::where('id', $id)->first();

        if ($workout->user_id === Auth::id()) {
            $workout->delete();
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted workout');
        }

        return back()->with('status', 'Successfully deleted workout');
    }

    public function recover(Request $request, $id)
    {
        $workout = Workout::onlyTrashed()->where('id', $id)->first();

        if ($workout->user_id !== Auth::id()) {
            return back();
        }

        $workout->restore();

        return back()->with('status', 'Successfully recovered workout.');
    }

    public function remove(Request $request, $id)
    {
        $workout = Workout::onlyTrashed()->where('id', $id)->first();

        if ($workout->user_id !== Auth::id()) {
            return back();
        }

        $workout->forceDelete();

        return back()->with('status', 'Successfully removed workout forever.');
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
            ->with(['movements', 'bookmarks', 'user'])
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

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
                'text' => $workout->name,
            ];
        }

        return $results;
    }
}
