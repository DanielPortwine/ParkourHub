<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRecordedWorkout;
use App\Http\Requests\UpdateRecordedWorkout;
use App\RecordedWorkout;
use App\Workout;
use App\WorkoutMovement;
use App\WorkoutMovementField;
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
            ->paginate(20);

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
            ->first();

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
            ->first();

        return view('workouts.recorded.edit', [
            'recordedWorkout' => $recordedWorkout,
        ]);
    }

    public function update(UpdateRecordedWorkout $request, $id)
    {
        $recordedWorkout = RecordedWorkout::where('id', $id)->first();

        $fields = WorkoutMovementField::whereIn('id', array_keys($request['fields']))
            ->whereHas('workoutMovement', function($q) use($recordedWorkout) {
                return $q->where('recorded_workout_id', $recordedWorkout->id);
            })
            ->get();

        foreach ($fields as $field) {
            $field->value = $request['fields'][$field->id];
            $field->save();
        }

        return back()->with('status', 'Successfully updated recorded workout');
    }

    public function delete($id)
    {
        $recordedWorkout = RecordedWorkout::where('id', $id)->first();

        if ($recordedWorkout->user_id === Auth::id()) {
            $recordedWorkout->delete();
        }

        return redirect()->route('recorded_workout_listing')->with('status', 'Successfully deleted recorded workout');
    }
}
