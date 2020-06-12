<?php

use App\Challenge;
use App\User;
use Illuminate\Database\Seeder;

class ChallengeViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Challenge::inRandomOrder()->limit(50)->pluck('id') as $challenge) {
                factory(App\ChallengeView::class)->create(['challenge_id' => $challenge, 'user_id' => $user]);
            }
        }
    }
}
