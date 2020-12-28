<?php

namespace Database\Seeders;

use App\MovementCategory;
use Illuminate\Database\Seeder;

class MovementCategorySeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        MovementCategory::factory()->create([
            'name' => 'Jumps',
            'type_id' => 1,
            'colour' => 'green',
            'description' => 'Jumping movements',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Vaults',
            'type_id' => 1,
            'colour' => 'pink',
            'description' => 'Movements to get over an obstacle',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Trick',
            'type_id' => 1,
            'colour' => 'blue',
            'description' => 'Fancy movements',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Climb',
            'type_id' => 1,
            'colour' => 'orange',
            'description' => 'Movements to get onto a higher level',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Slide',
            'type_id' => 1,
            'colour' => 'yellow',
            'description' => 'Movements that involve sliding on a surface',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Bars',
            'type_id' => 1,
            'colour' => 'cyan',
            'description' => 'Movements that involve bars',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Upper Body',
            'type_id' => 2,
            'colour' => 'green',
            'description' => 'Movements that work the upper body',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Core',
            'type_id' => 2,
            'colour' => 'pink',
            'description' => 'Movements that work the core',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Lower Body',
            'type_id' => 2,
            'colour' => 'blue',
            'description' => 'Movements that work the lower body',
        ]);
        MovementCategory::factory()->create([
            'name' => 'Cardio',
            'type_id' => 2,
            'colour' => 'orange',
            'description' => 'Exercises that improve cardiovascular endurance'
        ]);
    }
}
