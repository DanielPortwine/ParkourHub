<?php

use App\MovementType;
use Illuminate\Database\Seeder;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(MovementType::class)->create(['name' => 'Move']);
        factory(MovementType::class)->create(['name' => 'Exercise']);
    }
}
