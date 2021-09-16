<?php

namespace Database\Factories;

use App\Models\MovementField;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkoutMovementFieldFactory extends Factory
{
    protected $model = WorkoutMovementField::class;

    public function definition()
    {
        return [
            'movement_field_id' => MovementField::inRandomOrder()->first()->id,
            'workout_movement_id' => WorkoutMovement::inRandomOrder()->first()->id,
            'value' => $this->faker->numberBetween(1, 500),
        ];
    }
}
