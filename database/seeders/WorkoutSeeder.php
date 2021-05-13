<?php

namespace Database\Seeders;

use App\Models\Movement;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
use Illuminate\Database\Seeder;

class WorkoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            $workouts = Workout::factory()->times(rand(0, 5))->create(['user_id' => $user]);
            foreach ($workouts as $workout) {
                foreach (Movement::inRandomOrder()->limit(3)->get() as $movement) {
                    $workoutMovement = new WorkoutMovement;
                    $workoutMovement->user_id = $user;
                    $workoutMovement->movement_id = $movement->id;
                    $workoutMovement->workout_id = $workout->id;
                    $workoutMovement->save();

                    foreach ($movement->fields as $field) {
                        $workoutMovementField = new WorkoutMovementField;
                        $workoutMovementField->movement_field_id = $field->id;
                        $workoutMovementField->workout_movement_id = $workoutMovement->id;
                        $workoutMovementField->value = rand(0, 200);
                        $workoutMovementField->save();
                    }
                }
            }
        }
    }
}
