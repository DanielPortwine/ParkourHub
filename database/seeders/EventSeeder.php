<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Spot;
use App\Models\User;
use App\Scopes\VisibilityScope;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::get() as $user) {
            $events = Event::factory()->times(rand(1, 3))->create(['user_id' => $user->id]);
            foreach ($events as $event) {
                $spots = Spot::withoutGlobalScope(VisibilityScope::class)->inRandomOrder()->limit(rand(1, 3))->get();
                $attendees = User::inRandomOrder()->limit(rand(2, 5))->get();
                $applicants = User::whereNotIn('id', $attendees->pluck('id')->toArray())->inRandomOrder()->limit(rand(2, 5))->get();
                $event->spots()->attach($spots->pluck('id')->toArray());
                $event->attendees()->attach($attendees->pluck('id')->toArray(), ['accepted' => true]);
                $event->attendees()->attach($applicants->pluck('id')->toArray(), ['comment' => 'Please accept my attendance request']);
            }
        }
    }
}
