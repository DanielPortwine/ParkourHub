<?php

use App\User;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            factory(App\Challenge::class, rand(0, 50))->create(['user_id' => $user]);
        }
    }
}
