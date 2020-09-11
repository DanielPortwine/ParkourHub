<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWorkout;
use App\Movement;
use App\Workout;
use App\WorkoutMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->where('user_id', Auth::id())
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Workout',
            'content' => $workouts,
            'component' => 'workout',
            'create' => true,
        ]);
    }

    public function view($id)
    {
        $workout = Workout::with(['movements', 'user'])->where('id', $id)->first();

        return view('workouts.view', [
            'workout' => $workout,
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
        $workout->save();

        foreach ($request['movements'] as $movementRequest) {
            $movement = new WorkoutMovement;
            $movement->user_id = $userId;
            $movement->movement_id = $movementRequest['movement'];
            $movement->workout_id = $workout->id;
            $movement->reps = isset($movementRequest['reps']) ? $movementRequest['reps'] : null;
            $movement->weight = isset($movementRequest['weight']) ? $movementRequest['weight'] : null;
            $movement->duration = isset($movementRequest['duration']) ? $movementRequest['duration'] : null;
            $movement->distance = isset($movementRequest['distance']) ? $movementRequest['distance'] : null;
            $movement->height = isset($movementRequest['height']) ? $movementRequest['height'] : null;
            $movement->feeling = isset($movementRequest['feeling']) ? $movementRequest['feeling'] : null;
            $movement->save();
        }

        return redirect()->route('workout_view', $workout->id)->with('status', 'Successfully created workout');
    }

    public function edit($id)
    {
        $workout = Workout::with('movements')->where('id', $id)->first();

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
        $workout->save();

        foreach ($request['movements'] as $movementRequest) {
            if (count($movementRequest) === 1) {
                continue;
            }
            if (!empty($movementRequest['id'])) {
                $movement = WorkoutMovement::where('id', $movementRequest['id'])->first();
            } else {
                $movement = new WorkoutMovement;
                $movement->user_id = $userId;
                $movement->movement_id = $movementRequest['movement'];
                $movement->workout_id = $workout->id;
            }
            $movement->reps = isset($movementRequest['reps']) ? $movementRequest['reps'] : null;
            $movement->weight = isset($movementRequest['weight']) ? $movementRequest['weight'] : null;
            $movement->duration = isset($movementRequest['duration']) ? $movementRequest['duration'] : null;
            $movement->distance = isset($movementRequest['distance']) ? $movementRequest['distance'] : null;
            $movement->height = isset($movementRequest['height']) ? $movementRequest['height'] : null;
            $movement->feeling = isset($movementRequest['feeling']) ? $movementRequest['feeling'] : null;
            $movement->save();
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

        foreach ($movement->fields as $field) {
            $fields[] = [
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
}
