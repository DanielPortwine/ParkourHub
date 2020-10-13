<?php

use App\MovementField;
use Illuminate\Database\Seeder;

class MovementFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(MovementField::class)->create([
            'name' => 'reps',
            'input_type' => 'number',
            'label' => 'Reps',
            'unit' => 'reps',
            'small_text' => 'Number of times the movement was completed',
        ]);
        factory(MovementField::class)->create([
            'name' => 'sticks',
            'input_type' => 'number',
            'label' => 'Sticks',
            'unit' => 'sticks',
            'small_text' => 'Number of reps that were stuck',
        ]);
        factory(MovementField::class)->create([
            'name' => 'weight',
            'input_type' => 'number',
            'label' => 'Weight',
            'unit' => 'kg',
            'small_text' => 'Additional weight used during the movement',
        ]);
        factory(MovementField::class)->create([
            'name' => 'duration',
            'input_type' => 'number',
            'label' => 'Duration',
            'unit' => 's',
            'small_text' => 'Number of seconds the movement lasted for',
        ]);
        factory(MovementField::class)->create([
            'name' => 'distance',
            'input_type' => 'number',
            'label' => 'Distance',
            'unit' => 'cm',
            'small_text' => 'Distance travelled from the start to end of the movement',
        ]);
        factory(MovementField::class)->create([
            'name' => 'height',
            'input_type' => 'number',
            'label' => 'Height',
            'unit' => 'cm',
            'small_text' => 'The height difference from the start to end of the movement',
        ]);
    }
}
