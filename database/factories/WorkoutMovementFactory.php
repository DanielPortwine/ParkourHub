<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\RecordedWorkout;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutMovementFactory extends Factory
{
    protected $model = WorkoutMovement::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'movement_id' => Movement::inRandomOrder()->first()->id,
            'workout_id' => Workout::inRandomOrder()->first()->id,
            'recorded_workout_id' => RecordedWorkout::first()->id,
        ];
    }
}
