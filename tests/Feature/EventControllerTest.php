<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Event;
use App\Models\Spot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $accessPremium;
    protected $premiumUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->accessPremium = Permission::create(['name' => 'access premium']);
        $this->premiumUser = User::factory()->create()->givePermissionTo($this->accessPremium);
    }

    /** @test */
    public function listing_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_listing'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_can_view_public_upcoming_events()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'date_time' => Carbon::parse('+1 day'), 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_follower_upcoming_events_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'date_time' => Carbon::parse('+1 day'), 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_upcoming_events_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'date_time' => Carbon::parse('+1 day'), 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) {
                $this->assertCount(0, $viewEvents);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_own_private_upcoming_events()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_private_upcoming_events_of_different_user()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'date_time' => Carbon::parse('+1 day'), 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) {
                $this->assertCount(0, $viewEvents);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_upcoming_events()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'visibility' => 'public']);
        $event->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(0, $viewEvents);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_upcoming_events_between_two_dates()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'created_at' => Carbon::parse('+3 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['date_from' => Carbon::now()->format('H-m-d'), 'date_to' => Carbon::parse('+5 day')->format('Y-m-d')]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_upcoming_events_outside_two_dates()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'created_at' => Carbon::parse('+3 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['date_from' => Carbon::parse('+10 day')->format('Y-m-d'), 'date_to' => Carbon::parse('+15 day')->format('Y-m-d')]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) {
                $this->assertCount(0, $viewEvents);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_upcoming_events_matching_search_term()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'name' => 'apple']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['search' => 'apple']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_upcoming_events_not_matching_search_term()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'name' => 'apple']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['search' => 'pear']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) {
                $this->assertCount(0, $viewEvents);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_upcoming_events_they_are_attending()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $event->attendees()->attach($this->premiumUser, ['accepted' => true]);
        $event1 = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['attending' => 'true']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_upcoming_events_they_are_invited_or_applied_to()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $event->attendees()->attach($this->premiumUser);
        $event1 = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['applied' => 'true']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_historic_events()
    {
        $event = Event::factory()->create(['date_time' => Carbon::parse('-1 day')]);
        $event1 = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['historic' => 'true']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_upcoming_events_in_hometown()
    {
        $this->premiumUser->hometown_bounding = '54.7358637,54.793347,-1.6058428,-1.553796';
        $this->premiumUser->hometown_name = 'City of Durham, Durham, County Durham, North East England, England, United Kingdom';
        $this->premiumUser->save();
        $spot = Spot::factory()->create(['coordinates' => '-175488.83450786362,7318062.638891663', 'latitude' => 54.773666, 'longitude' => -1.576443]);
        $event = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $event->spots()->attach($spot);
        $spot1 = Spot::factory()->create(['coordinates' => '14234610.972397,-4061363.7890414', 'latitude' => -34.242153, 'longitude' => 127.871686]);
        $event1 = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $event1->spots()->attach($spot1);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['in_hometown' => 'true']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_upcoming_events_of_users_they_are_following()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser, ['accepted' => true]);
        $event = Event::factory()->create(['user_id' => $user->id, 'date_time' => Carbon::parse('+1 day'), 'visibility' => 'public']);
        $event1 = Event::factory()->create(['user_id' => $this->premiumUser->id, 'date_time' => Carbon::parse('+1 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['following' => 'true']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($event) {
                $this->assertCount(1, $viewEvents);
                $this->assertSame($event->id, $viewEvents->first()->id);
                $this->assertSame($event->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_soonest_upcoming_events_first()
    {
        $soonestEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $furthestEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($soonestEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($soonestEvent->id, $viewEvents->first()->id);
                $this->assertSame($soonestEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_furthest_upcoming_events_first()
    {
        $soonestEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $furthestEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'eventdate_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($furthestEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($furthestEvent->id, $viewEvents->first()->id);
                $this->assertSame($furthestEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_upcoming_events_first()
    {
        $latestEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'created_at' => '2021-05-31 19:30:00']);
        $oldestEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day'), 'created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'date_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($latestEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($latestEvent->id, $viewEvents->first()->id);
                $this->assertSame($latestEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_upcoming_events_first()
    {
        $latestEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day'), 'created_at' => '2021-05-31 19:30:00']);
        $oldestEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day'), 'created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($oldestEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($oldestEvent->id, $viewEvents->first()->id);
                $this->assertSame($oldestEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_most_attended_upcoming_events_first()
    {
        $mostEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $leastEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);
        $mostEvent->attendees()->attach($this->premiumUser, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'attendees_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($mostEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($mostEvent->id, $viewEvents->first()->id);
                $this->assertSame($mostEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_least_attended_upcoming_events_first()
    {
        $mostEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $leastEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);
        $mostEvent->attendees()->attach($this->premiumUser, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'attendees_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($leastEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($leastEvent->id, $viewEvents->first()->id);
                $this->assertSame($leastEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_upcoming_events_with_most_spots_first()
    {
        $spot = Spot::factory()->create();
        $mostEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $leastEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);
        $mostEvent->spots()->attach($spot);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'spots_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($mostEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($mostEvent->id, $viewEvents->first()->id);
                $this->assertSame($mostEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_upcoming_events_with_least_spots_first()
    {
        $spot = Spot::factory()->create();
        $mostEvent = Event::factory()->create(['date_time' => Carbon::parse('+1 day')]);
        $leastEvent = Event::factory()->create(['date_time' => Carbon::parse('+3 day')]);
        $mostEvent->spots()->attach($spot);

        $response = $this->actingAs($this->premiumUser)->get(route('event_listing', ['sort' => 'spots_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEvents) use ($leastEvent) {
                $this->assertCount(2, $viewEvents);
                $this->assertSame($leastEvent->id, $viewEvents->first()->id);
                $this->assertSame($leastEvent->name, $viewEvents->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_view', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function view_non_premium_user_can_view_public_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('event_view', $event->id));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_follower_event_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user, ['accepted' => true]);
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('event_view', $event->id));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_event_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', $event->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_private_event()
    {
        $event = Event::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', $event->id));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_private_event_of_different_user()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', $event->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_deleted_event()
    {
        $event = Event::factory()->create();
        $event->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', $event->id));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_deleted_public_event_of_different_user()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $event->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', $event->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_paginated_event_spots()
    {
        $event = Event::factory()->create();
        $spots = Spot::factory()->times(21)->create();
        $event->spots()->attach($spots);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', [$event->id, 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            })
            ->assertViewHas('spots', function ($viewSpots) {
                $this->assertCount(1, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_event_attendees()
    {
        $event = Event::factory()->create();
        $attendees = User::factory()->times(21)->create();
        $event->attendees()->attach($attendees, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', [$event->id, 'tab' => 'attendees', 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            })
            ->assertViewHas('attendees', function ($viewAttendees) {
                $this->assertCount(1, $viewAttendees);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_event_comments()
    {
        $event = Event::factory()->create();
        $comments = Comment::factory()->times(21)->create(['commentable_type' => Event::class, 'commentable_id' => $event->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_view', [$event->id, 'tab' => 'comments', 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('events.view')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->id);
                $this->assertSame($event->name, $viewEvent->name);
                return true;
            })
            ->assertViewHas('comments', function ($viewComments) {
                $this->assertCount(1, $viewComments);
                return true;
            });
    }

    /** @test */
    public function create_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_create'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function create_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('event_create'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function create_premium_user_can_view_create()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('event_create'));

        $response->assertOk()
            ->assertViewIs('events.create')
            ->assertViewHas('spots', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                $this->assertSame($spot->name, $viewSpots->first()->name);
                return true;
            })
            ->assertViewHas('users', function ($viewUsers) use ($user) {
                $this->assertCount(1, $viewUsers);
                $this->assertSame($user->id, $viewUsers->first()->id);
                $this->assertSame($user->name, $viewUsers->first()->name);
                return true;
            });
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('event_store', []));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('event_store', []));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function store_premium_user_can_store_event_with_valid_data_and_notify_followers_and_invitees()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $follower = User::factory()->create();
        $this->premiumUser->followers()->attach($follower->id, ['accepted' => true]);
        $invitee = User::factory()->create();
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'users' => [
                $invitee->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseHas('events', [
                'name' => 'Test Event',
                'description' => 'This is a test event',
            ])
            ->assertDatabaseCount('spots_events', 1)
            ->assertDatabaseHas('spots_events', [
                'spot_id' => $spot->id,
            ])
            ->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'user_id' => $invitee->id,
                'accepted' => false,
            ])
            ->assertDatabaseCount('notifications', 2);

        Storage::disk('public')->assertExists('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            // name missing
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_long_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'This name is far too long for the validation',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_array_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => ['name' => 'Test Event'],
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_date_time()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            // missing date_time
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_invalid_date_time_format()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i:s'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_thumbnail()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            // thumbnail missing
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_spots()
    {
        Storage::fake('public');

        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            // spots missing
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_invalid_spots()
    {
        Storage::fake('public');

        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                987,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_visibility()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            // visibility missing
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_invalid_visibility()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'invalid',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_without_accept_method()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            // accept_method missing
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_invalid_accept_method()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invalid',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_string_users()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
            'users' => 'string',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_event_with_invalid_users()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('event_store'), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
            'users' => [
                987,
            ],
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('event_edit', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_edit', $event->id));

        $response->assertRedirect(route('event_view', $event->id));
    }

    /** @test */
    public function edit_premium_user_can_view_edit()
    {
        $event = Event::factory()->create();
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('event_edit', $event->id));

        $response->assertOk()
            ->assertViewIs('events.edit')
            ->assertViewHas('event', function ($viewEvent) use ($event) {
                $this->assertSame($event->id, $viewEvent->first()->id);
                $this->assertSame($event->name, $viewEvent->first()->name);
                return true;
            })
            ->assertViewHas('spots', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                $this->assertSame($spot->name, $viewSpots->first()->name);
                return true;
            })
            ->assertViewHas('users', function ($viewUsers) use ($user) {
                $this->assertCount(1, $viewUsers);
                $this->assertSame($user->id, $viewUsers->first()->id);
                $this->assertSame($user->name, $viewUsers->first()->name);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('event_update', 1), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('event_update', 1), []);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Test Event',
            'description' => 'This is a test event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $response->assertRedirect(route('event_view', $event->id));

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_update_event_and_notify_followers()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);
        $follower = User::factory()->create();
        $this->premiumUser->followers()->attach($follower, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseHas('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 1)
            ->assertDatabaseHas('spots_events', [
                'spot_id' => $spot->id,
            ])
            ->assertDatabaseCount('notifications', 1);

        Storage::disk('public')->assertExists('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_without_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);
        $follower = User::factory()->create();
        $this->premiumUser->followers()->attach($follower, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            // name missing
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_long_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'This name is far too long for the validation',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'This name is far too long for the validation',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_array_name()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => ['name' => 'Updated Event'],
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_without_date_time()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            // date_time missing
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_invalid_date_time_format()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i:s'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_without_spots()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            // spots missing
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_invalid_spots()
    {
        Storage::fake('public');

        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                987,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_without_visibility()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            // visibility missing
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_invalid_visibility()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'invalid',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_without_accept_method()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            // accept_method missing
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_invalid_accept_method()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invalid',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_string_users()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
            'users' => 'string',
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_event_with_invalid_users()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'invite',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
            'users' => [
                987,
            ],
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseMissing('events', [
                'name' => 'Updated Event',
                'description' => 'This is an updated event',
            ])
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_delete_event_and_redirect()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create(['visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);
        $event = Event::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_update', $event->id), [
            'name' => 'Updated Event',
            'description' => 'This is an updated event',
            'date_time' => Carbon::now()->format('Y-m-d\TH:i'),
            'link_access' => false,
            'accept_method' => 'none',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
            'spots' => [
                $spot->id,
            ],
            'visibility' => 'public',
            'delete' => true,
            'redirect' => route('event_view', $event->id),
        ]);

        $this->assertDatabaseCount('events', 1)
            ->assertSoftDeleted($event)
            ->assertDatabaseCount('spots_events', 0)
            ->assertDatabaseCount('notifications', 0);

        Storage::disk('public')->assertMissing('images/events/' . $thumbnail->hashName());
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('event_delete', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_delete', $event->id));

        $response->assertRedirect(route('event_view', $event->id));
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_event()
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('event_delete', $event->id));

        $this->assertDatabaseCount('events', 1)
            ->assertSoftDeleted($event);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('event_recover', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $event->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('event_recover', $event->id));

        $this->assertDatabaseCount('events', 1)
            ->assertSoftDeleted($event);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_workout()
    {
        $event = Event::factory()->create();
        $event->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('event_recover', $event->id));

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseHas('events', [
                'name' => $event->name,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('event_remove', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_remove', $event->id));

        $this->assertDatabaseCount('events', 1)
            ->assertDatabaseHas('events', [
                'name' => $event->name,
            ]);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_event()
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('event_remove', $event->id));

        $this->assertDatabaseCount('events', 0);
    }

    /** @test */
    public function remove_random_premium_user_with_remove_content_permission_can_remove_event()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $this->premiumUser->givePermissionTo($removeContent);
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_remove', $event->id));

        $this->assertDatabaseCount('events', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_report', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_random_non_premium_user_can_report_visible_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('event_report', $event->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $event->id,
                'reportable_type' => Event::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function report_random_premium_user_can_not_report_invisible_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('event_report', $event->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_report_non_logged_in_user_redirects_to_login()
    {
        $event = Event::factory()->create();

        $response = $this->get(route('event_report_discard', $event->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_reports_random_non_premium_user_can_not_discard_event_reports()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$event->id, Event::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_report_discard', $event->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $event->id,
                'reportable_type' => Event::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_can_not_discard_event_reports()
    {
        $event = Event::factory()->create();
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$event->id, Event::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_report_discard', $event->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $event->id,
                'reportable_type' => Event::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_premium_user_with_manage_reports_permission_can_discard_event_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $event = Event::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$event->id, Event::class, $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_report_discard', $event->id));

        $this->assertDatabaseCount('reports', 0);
    }
}
