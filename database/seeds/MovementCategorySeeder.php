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
            'colour' => 'green',
            'description' => 'Jumping movements',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Vaults',
            'colour' => 'pink',
            'description' => 'Movements to get over an obstacle',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Trick',
            'colour' => 'blue',
            'description' => 'Fancy movements',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Climb',
            'colour' => 'orange',
            'description' => 'Movements to get onto a higher level',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Slide',
            'colour' => 'yellow',
            'description' => 'Movements that involve sliding on a surface',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Bars',
            'colour' => 'cyan',
            'description' => 'Movements that involve bars',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Upper Body',
            'colour' => 'green',
            'description' => 'Movements that work the upper body',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Core',
            'colour' => 'pink',
            'description' => 'Movements that work the core',
        ]);
        factory(MovementCategory::class)->create([
            'name' => 'Lower Body',
            'colour' => 'blue',
            'description' => 'Movements that work the lower body',
        ]);
    }
}
