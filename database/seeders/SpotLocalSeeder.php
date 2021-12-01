<?php

namespace Database\Seeders;

use App\Models\Spot;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Seeder;

class SpotLocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Spot::withoutGlobalScope(VisibilityScope::class)->get() as $spot) {
            for ($x = 0; $x <= rand(1, 3); $x++) {
                $spot->locals()->attach(User::inRandomOrder()->first()->id);
            }
        }
    }
}
