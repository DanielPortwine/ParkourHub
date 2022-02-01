<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRecordedWorkout;
use App\Http\Requests\UpdateRecordedWorkout;
use App\Models\RecordedWorkout;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RecordedWorkoutController extends Controller
{
    public function index(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'updated' => 'updated_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $workouts = RecordedWorkout::with('workout')
            ->where('user_id', Auth::id())
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Recorded Workouts',
            'content' => $workouts,
            'component' => 'recorded_workout',
        ]);
    }

    public function view($id)
    {
        $recordedWorkout = RecordedWorkout::with([
            'workout',
            'user',
            'workout.movements' => function ($q) use ($id) {
                $q->where('recorded_workout_id', $id);
            },
            'workout.movements.fields',
            'workout.movements.fields.field',
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (empty($recordedWorkout)) {
            abort(404);
        }

        return view('workouts.recorded.view', [
            'recordedWorkout' => $recordedWorkout,
        ]);
    }

    public function create($id)
    {
        $workout = Workout::with([
                'movements' => function($q) {
                    $q->where('recorded_workout_id', null);
                },
                'movements.fields',
            ])
            ->where('id', $id)
            ->first();

        return view('workouts.recorded.create', [
            'workout' => $workout,
        ]);
    }

    public function store(CreateRecordedWorkout $request, $id)
    {
        $userId = Auth::id();
        $workoutId = $id;
        $recordedWorkout = new RecordedWorkout;
        $recordedWorkout->user_id = $userId;
        $recordedWorkout->workout_id = $workoutId;
        $recordedWorkout->time = $request['time'];
        $recordedWorkout->save();

        $todaysWorkout = Auth::user()->planWorkouts()
            ->withPivot(['workout_id', 'recorded_workout_id', 'date'])
            ->where('workout_id', $workoutId)
            ->whereNull('recorded_workout_id')
            ->where('date', Carbon::now()->format('Y-m-d'))
            ->first();
        if(!empty($todaysWorkout)) {
            $todaysWorkout->pivot->recorded_workout_id = $recordedWorkout->id;
            $todaysWorkout->pivot->save();
        }

        foreach ($request['movements'] as $movementRequest) {
            if (count($movementRequest) === 1) {
                continue;
            }
            $movement = new WorkoutMovement;
            $movement->user_id = $userId;
            $movement->movement_id = $movementRequest['movement'];
            $movement->workout_id = $workoutId;
            $movement->recorded_workout_id = $recordedWorkout->id;
            $movement->save();

            foreach ($movementRequest['fields'] as $id => $value) {
                $field = new WorkoutMovementField;
                $field->movement_field_id = $id;
                $field->workout_movement_id = $movement->id;
                $field->value = $value;
                $field->save();
            }
        }

        return redirect()->route('recorded_workout_view', $recordedWorkout->id)->with('status', 'Successfully recorded workout');
    }

    public function edit($id)
    {
        $recordedWorkout = RecordedWorkout::with([
                'movements' => function($q) use ($id) {
                    $q->where('recorded_workout_id', $id);
                },
                'movements.fields',
            ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if(empty($recordedWorkout)) {
            return redirect()->route('recorded_workout_view', $id);
        }

        return view('workouts.recorded.edit', [
            'recordedWorkout' => $recordedWorkout,
        ]);
    }

    public function update(UpdateRecordedWorkout $request, $id)
    {
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $recordedWorkout = RecordedWorkout::where('id', $id)->where('user_id', Auth::id())->first();

        if(empty($recordedWorkout)) {
            return redirect()->route('recorded_workout_view', $id);
        }

        $recordedWorkout->time = $request['time'];
        $recordedWorkout->save();

        $fields = WorkoutMovementField::whereIn('id', array_keys($request['fields']))
            ->whereHas('workoutMovement', function($q) use($recordedWorkout) {
                return $q->where('recorded_workout_id', $recordedWorkout->id);
            })
            ->get();

        foreach ($fields as $field) {
            $field->value = $request['fields'][$field->id];
            $field->save();
        }

        return back()->with([
            'status' => 'Successfully updated recorded workout',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $recordedWorkout = RecordedWorkout::where('id', $id)->where('user_id', Auth::id())->first();

        if (empty($recordedWorkout)) {
            return redirect()->route('recorded_workout_view', $id);
        }

        if ($recordedWorkout->user_id === Auth::id()) {
            $recordedWorkout->delete();
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted recorded workout');
        }

        return back()->with('status', 'Successfully deleted recorded workout');
    }
}
