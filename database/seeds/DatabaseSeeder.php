<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(FollowerSeeder::class);
        $this->call(SpotSeeder::class);
        $this->call(SpotViewSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(SpotCommentSeeder::class);
        $this->call(SpotCommentLikeSeeder::class);
        $this->call(HitSeeder::class);
        $this->call(ChallengeSeeder::class);
        $this->call(ChallengeViewSeeder::class);
        $this->call(ChallengeEntrySeeder::class);
        $this->call(MovementTypeSeeder::class);
        $this->call(MovementCategorySeeder::class);
        $this->call(MovementFieldSeeder::class);
        $this->call(MovementSeeder::class);
    }
}
