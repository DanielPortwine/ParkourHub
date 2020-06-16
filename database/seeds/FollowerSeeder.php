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
            foreach (User::where('id', '!=', $follower)->inRandomOrder()->limit(25)->get() as $user) {
                factory(App\Follower::class)->create(['user_id' => $user->id, 'follower_id' => $follower]);

                $followers = $user->followers()->count();
                $user->followers_quantified = quantify_number($followers);
                $user->save();
            }
        }
    }
}
