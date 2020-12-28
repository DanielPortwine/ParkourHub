<?php

namespace Database\Seeders;

use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class SpotSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            Spot::factory()->times(rand(0, 25))->create(['user_id' => $user]);
        }
    }
}
