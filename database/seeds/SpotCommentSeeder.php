<?php

use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class SpotCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(50)->pluck('id') as $spot) {
                factory(App\SpotComment::class, rand(0, 3))->create(['spot_id' => $spot, 'user_id' => $user]);
            }
        }
    }
}
