<?php

namespace Database\Seeders;

use App\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Equipment::factory()->create([
            'user_id' => 1,
            'name' => 'Dumbbells',
            'description' => 'A pair of weights that can be held one in each hand.',
            'visibility' => 'public',
        ]);

        Equipment::factory()->create([
            'user_id' => 1,
            'name' => 'Barbell',
            'description' => 'A long bar with weight plates on each end.',
            'visibility' => 'public',
        ]);
    }
}
