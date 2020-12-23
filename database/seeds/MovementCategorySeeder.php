<?php

use App\MovementCategory;
use Illuminate\Database\Seeder;

class MovementCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(MovementCategory::class)->create([
            'name' => 'Jumps',
            'type_id' => 1,
            'colour' => 'green',
            'description' => 'Jumping movements',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Vaults',
            'type_id' => 1,
            'colour' => 'pink',
            'description' => 'Movements to get over an obstacle',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Trick',
            'type_id' => 1,
            'colour' => 'blue',
            'description' => 'Fancy movements',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Climb',
            'type_id' => 1,
            'colour' => 'orange',
            'description' => 'Movements to get onto a higher level',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Slide',
            'type_id' => 1,
            'colour' => 'yellow',
            'description' => 'Movements that involve sliding on a surface',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Bars',
            'type_id' => 1,
            'colour' => 'cyan',
            'description' => 'Movements that involve bars',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Upper Body',
            'type_id' => 2,
            'colour' => 'green',
            'description' => 'Movements that work the upper body',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Core',
            'type_id' => 2,
            'colour' => 'pink',
            'description' => 'Movements that work the core',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Lower Body',
            'type_id' => 2,
            'colour' => 'blue',
            'description' => 'Movements that work the lower body',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Cardio',
            'type_id' => 2,
            'colour' => 'orange',
            'description' => 'Exercises that improve cardiovascular endurance'
        ]);
    }
}
