<?php

namespace App\Http\Controllers;

use App\Follower;
use App\Http\Requests\AddWorkoutToPlan;
use App\Workout;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutPlanController extends Controller
{
    public function index(Request $request)
    {
        $monthDate = $request['month'] ?: date('Y-m');
        $month = Carbon::parse($monthDate);
        $monthStart = $month->firstOfMonth()->format('Y-m-d');
        $monthEnd = $month->endOfMonth()->format('Y-m-d');

        $workouts = Auth::user()->planWorkouts()->wherePivot('date', '>=', $monthStart)->wherePivot('date', '<=', $monthEnd)->get();

        $weeks = [];
        for ($day = 1; $day <= $month->endOfMonth()->format('d'); $day++) {
            $dayDate = $month->year . '-' . str_pad($month->month, 2, '0', STR_PAD_LEFT) . '-' . $day;
            $carbonDayDate = Carbon::parse($dayDate);
            $weeks[$carbonDayDate->weekNumberInMonth][$carbonDayDate->dayOfWeek] = ['day' => $carbonDayDate->day, 'workouts' => []];
        }
        foreach ($workouts as $workout) {
            $date = Carbon::parse($workout->pivot->date);
            $weeks[$date->weekNumberInMonth][$date->dayOfWeek]['workouts'][] = $workout;
        }
        for ($day = (array_keys($weeks[1])[0] - 1 >= 0 ? array_keys($weeks[1])[0] - 1 : 6); $day >= 1; $day--) {
            $weeks[1] = [$day => null] + $weeks[1];
        }
        end($weeks);
        $lastWeekKey = key($weeks);
        while (count($weeks[$lastWeekKey]) < 7) {
            $weeks[$lastWeekKey][] = null;
        }

        $addableWorkouts = Workout::get();

        return view('workouts.plan.index', [
            'weeks' => $weeks,
            'date' => $monthDate,
            'addableWorkouts' => $addableWorkouts,
        ]);
    }

    public function addWorkout(AddWorkoutToPlan $request)
    {
        Auth::user()->planWorkouts()->attach($request['workout'], ['date' => $request['date']]);

        if (!empty($request['repeat_frequency']) && !empty($request['repeat_until'])) {
            $firstDate = strtotime($request['date']);
            $maxDate = strtotime($request['repeat_until']);
            switch ($request['repeat_frequency']) {
                case 'weekly':
                    $increment = 604800;
                    break;
                case 'other':
                    $increment = 172800;
                    break;
                case 'daily':
                    $increment = 86400;
                    break;
            }

            for ($day = $firstDate + $increment; $day <= $maxDate; $day += $increment) {
                Auth::user()->planWorkouts()->attach($request['workout'], ['date' => date('Y-m-d', $day)]);
            }
        }

        return back()->with('status', 'Successfully added workout to plan');
    }

    public function removeWorkout($id)
    {
        $planWorkout = Auth::user()->planWorkouts()->wherePivot('id', $id)->first()->pivot;

        if ($planWorkout->user_id === Auth::id()) {
            $planWorkout->delete();
        }

        return back()->with('status', 'Successfully removed workout from plan');
    }

    public function getUserWorkouts(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $workouts = Workout::with('planUsers')
            ->where('user_id', Auth::id())
            ->orWhereHas('planUsers', function($q) {
                $q->where('users.id', Auth::id());
            })
            ->orderByDesc('name')
            ->get();

        $results = [];
        foreach ($workouts as $workout) {
            $results[] = [
                'id' => $workout->id,
                'text' => $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)),
            ];
        }

        return $results;
    }
}
