<?php

use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class SpotViewSeeder extends Seeder
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
                factory(App\SpotView::class)->create(['spot_id' => $spot, 'user_id' => $user]);
            }
        }
    }
}
