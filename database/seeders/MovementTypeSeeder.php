<?php

namespace Database\Seeders;

use App\Models\MovementType;
use Illuminate\Database\Seeder;

class MovementTypeSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        MovementType::factory()->create(['name' => 'Move']);
        MovementType::factory()->create(['name' => 'Exercise']);
    }
}
