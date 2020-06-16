<?php

use App\User;
use Illuminate\Database\Seeder;

class SpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            factory(App\Spot::class, rand(0, 25))->create(['user_id' => $user]);
        }
    }
}
