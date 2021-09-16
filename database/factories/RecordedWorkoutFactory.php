<?php

namespace Database\Factories;

use App\Models\RecordedWorkout;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordedWorkoutFactory extends Factory
{
    protected $model = RecordedWorkout::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'workout_id' => Workout::inRandomOrder()->first()->id,
        ];
    }
}
