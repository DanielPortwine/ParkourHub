<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'profile_image' => str_replace('public', '', $this->faker->image('public/storage/images/users/profile', 640, 480, null, true)),
            'cover_image' => str_replace('public', '', $this->faker->image('public/storage/images/users/cover', 640, 480, null, true)),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // 'password'
            'remember_token' => Str::random(10),
            'settings' => '{"notifications_review":"email-site","notifications_comment":"email-site","notifications_challenge":"email-site","notifications_entry":"email-site","notifications_challenge_won":"email-site","notifications_follower":"email-site","notifications_new_spot":"email-site","notifications_new_challenge":"email-site","notifications_new_event":"email-site","notifications_event_updated":"email-site","notifications_event_invite":"email-site","notifications_new_workout":"email-site","notifications_workout_updated":"email-site"}',
        ];
    }
}
