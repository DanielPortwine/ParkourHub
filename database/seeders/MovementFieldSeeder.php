<?php

namespace Database\Seeders;

use App\MovementField;
use Illuminate\Database\Seeder;

class MovementFieldSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        MovementField::factory()->create([
            'name' => 'reps',
            'input_type' => 'number',
            'label' => 'Reps',
            'unit' => 'reps',
            'small_text' => 'Number of times the movement was completed',
        ]);
        MovementField::factory()->create([
            'name' => 'sticks',
            'input_type' => 'number',
            'label' => 'Sticks',
            'unit' => 'sticks',
            'small_text' => 'Number of reps that were stuck',
        ]);
        MovementField::factory()->create([
            'name' => 'weight',
            'input_type' => 'number',
            'label' => 'Weight',
            'unit' => 'kg',
            'small_text' => 'Additional weight used during the movement',
        ]);
        MovementField::factory()->create([
            'name' => 'duration',
            'input_type' => 'number',
            'label' => 'Duration',
            'unit' => 's',
            'small_text' => 'Number of seconds the movement lasted for',
        ]);
        MovementField::factory()->create([
            'name' => 'distance',
            'input_type' => 'number',
            'label' => 'Distance',
            'unit' => 'cm',
            'small_text' => 'Distance travelled from the start to end of the movement',
        ]);
        MovementField::factory()->create([
            'name' => 'height',
            'input_type' => 'number',
            'label' => 'Height',
            'unit' => 'cm',
            'small_text' => 'The height difference from the start to end of the movement',
        ]);
    }
}
