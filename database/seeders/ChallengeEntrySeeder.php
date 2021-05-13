<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChallengeEntrySeeder extends Seeder
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
                ChallengeEntry::factory()->create(['challenge_id' => $challenge, 'user_id' => $user]);
            }
        }
    }
}
