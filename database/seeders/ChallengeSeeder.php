<?php

namespace Database\Seeders;

use App\Challenge;
use App\User;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            Challenge::factory()->times(rand(0, 25))->create(['user_id' => $user]);
        }
    }
}
