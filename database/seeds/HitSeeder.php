<?php

use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class HitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::pluck('id') as $user) {
            foreach (Spot::inRandomOrder()->limit(25)->pluck('id') as $spot) {
                factory(App\Hit::class)->create(['user_id' => $user, 'spot_id' => $spot]);
            }
        }
    }
}
