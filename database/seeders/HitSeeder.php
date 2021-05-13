<?php

namespace Database\Seeders;

use App\Models\Hit;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Database\Seeder;

class HitSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(25)->pluck('id') as $spot) {
                Hit::factory()->create(['user_id' => $user, 'spot_id' => $spot]);
            }
        }
    }
}
