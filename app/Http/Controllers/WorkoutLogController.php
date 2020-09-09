<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWorkoutEntry;
use App\Movement;
use App\WorkoutEntry;
use App\WorkoutMovementEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutLogController extends Controller
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

        $workouts = WorkoutEntry::withCount('movementEntries')
            ->where('user_id', Auth::id())
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Workout Log',
            'content' => $workouts,
            'component' => 'workout_log',
            'create' => true,
        ]);
    }

    public function view($id)
    {
        $workout = WorkoutEntry::with(['movementEntries', 'user'])->where('id', $id)->first();

        return view('workouts.view_logged_workout', [
            'workout' => $workout,
        ]);
    }

    public function create()
    {
        return view('workouts.log_workout');
    }

    public function store(CreateWorkoutEntry $request)
    {
        $userId = Auth::id();
        $workoutEntry = new WorkoutEntry;
        $workoutEntry->user_id = $userId;
        $workoutEntry->name = $request['name'] ?: null;
        $workoutEntry->description = $request['description'] ?: null;
        $workoutEntry->save();

        foreach ($request['movementEntries'] as $movementEntryRequest) {
            $movementEntry = new WorkoutMovementEntry;
            $movementEntry->user_id = $userId;
            $movementEntry->movement_id = $movementEntryRequest['movement'];
            $movementEntry->workout_entry_id = $workoutEntry->id;
            $movementEntry->reps = isset($movementEntryRequest['reps']) ? $movementEntryRequest['reps'] : null;
            $movementEntry->weight = isset($movementEntryRequest['weight']) ? $movementEntryRequest['weight'] : null;
            $movementEntry->duration = isset($movementEntryRequest['duration']) ? $movementEntryRequest['duration'] : null;
            $movementEntry->distance = isset($movementEntryRequest['distance']) ? $movementEntryRequest['distance'] : null;
            $movementEntry->height = isset($movementEntryRequest['height']) ? $movementEntryRequest['height'] : null;
            $movementEntry->feeling = isset($movementEntryRequest['feeling']) ? $movementEntryRequest['feeling'] : null;
            $movementEntry->save();
        }

        return redirect()->route('workout_log_view', $workoutEntry->id)->with('status', 'Successfully logged workout');
    }

    public function edit($id)
    {
        $workout = WorkoutEntry::with('movementEntries')->where('id', $id)->first();

        return view('workouts.edit_logged_workout', [
            'workout' => $workout,
        ]);
    }

    public function update(CreateWorkoutEntry $request, $id)
    {
        $userId = Auth::id();
        $workoutEntry = WorkoutEntry::where('id', $id)->first();
        $workoutEntry->name = $request['name'] ?: null;
        $workoutEntry->description = $request['description'] ?: null;
        $workoutEntry->save();

        foreach ($request['movementEntries'] as $movementEntryRequest) {
            if (count($movementEntryRequest) === 1) {
                continue;
            }
            if (!empty($movementEntryRequest['id'])) {
                $movementEntry = WorkoutMovementEntry::where('id', $movementEntryRequest['id'])->first();
            } else {
                $movementEntry = new WorkoutMovementEntry;
                $movementEntry->user_id = $userId;
                $movementEntry->movement_id = $movementEntryRequest['movement'];
                $movementEntry->workout_entry_id = $workoutEntry->id;
            }
            $movementEntry->reps = isset($movementEntryRequest['reps']) ? $movementEntryRequest['reps'] : null;
            $movementEntry->weight = isset($movementEntryRequest['weight']) ? $movementEntryRequest['weight'] : null;
            $movementEntry->duration = isset($movementEntryRequest['duration']) ? $movementEntryRequest['duration'] : null;
            $movementEntry->distance = isset($movementEntryRequest['distance']) ? $movementEntryRequest['distance'] : null;
            $movementEntry->height = isset($movementEntryRequest['height']) ? $movementEntryRequest['height'] : null;
            $movementEntry->feeling = isset($movementEntryRequest['feeling']) ? $movementEntryRequest['feeling'] : null;
            $movementEntry->save();
        }

        return redirect()->route('workout_log_view', $workoutEntry->id)->with('status', 'Successfully updated workout');
    }

    public function delete($id)
    {
        $workout = WorkoutEntry::where('id', $id)->first();

        if ($workout->user_id === Auth::id()) {
            $workout->delete();
        }

        return redirect()->route('workout_log_listing')->with('status', 'Successfully deleted workout entry');
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

    public function deleteMovementEntry($id)
    {
        $movementEntry = WorkoutMovementEntry::where('id', $id)->first();

        if ($movementEntry->user_id === Auth::id()) {
            $movementEntry->delete();
        }

        return back()->with('status', 'Successfully deleted movement entry');
    }
}
