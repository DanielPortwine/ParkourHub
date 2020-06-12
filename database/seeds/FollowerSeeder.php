<?php

use App\User;
use Illuminate\Database\Seeder;

class FollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $follower) {
            foreach (User::inRandomOrder()->limit(50)->pluck('id') as $user) {
                factory(App\Follower::class)->create(['user_id' => $user, 'follower_id' => $follower]);
            }
        }
    }
}
