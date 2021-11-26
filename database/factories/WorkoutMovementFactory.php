<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\RecordedWorkout;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutMovementFactory extends Factory
{
    protected $model = WorkoutMovement::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'movement_id' => Movement::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
            'workout_id' => Workout::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->first()->id,
        ];
    }
}
