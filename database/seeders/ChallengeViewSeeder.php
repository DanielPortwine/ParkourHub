<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeView;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChallengeViewSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Challenge::inRandomOrder()->limit(25)->pluck('id') as $challenge) {
                ChallengeView::factory()->create(['challenge_id' => $challenge, 'user_id' => $user]);
            }
        }
    }
}
