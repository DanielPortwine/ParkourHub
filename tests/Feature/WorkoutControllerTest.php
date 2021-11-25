<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\RecordedWorkout;
use App\Models\Spot;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkoutControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

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
        $response = $this->get(route('workout_listing'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('workout_listing'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function listing_premium_user_can_view_public_workouts()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(1, $viewWorkout);
                $this->assertSame($workout->id, $viewWorkout->first()->id);
                $this->assertSame($workout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_follower_workouts_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(1, $viewWorkout);
                $this->assertSame($workout->id, $viewWorkout->first()->id);
                $this->assertSame($workout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_workouts_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_own_private_workouts()
    {
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(1, $viewWorkout);
                $this->assertSame($workout->id, $viewWorkout->first()->id);
                $this->assertSame($workout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_private_workouts_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_workouts()
    {
        $workout = Workout::factory()->create();
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_deleted_public_workouts_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_workouts_between_two_dates()
    {
        $workout = Workout::factory()->create(['created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(1, $viewWorkout);
                $this->assertSame($workout->id, $viewWorkout->first()->id);
                $this->assertSame($workout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_workouts_outside_two_dates()
    {
        $workout = Workout::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_workouts_matching_search_term()
    {
        $workout = Workout::factory()->create(['name' => 'keyboard']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing', ['search' => 'keyboard']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(1, $viewWorkout);
                $this->assertSame($workout->id, $viewWorkout->first()->id);
                $this->assertSame($workout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_workouts_not_matching_search_term()
    {
        $workout = Workout::factory()->create(['name' => 'keyboard']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing', ['search' => 'mouse']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($workout) {
                $this->assertCount(0, $viewWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_workouts_first()
    {
        $latestWorkout = Workout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestWorkout = Workout::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($latestWorkout) {
                $this->assertCount(2, $viewWorkout);
                $this->assertSame($latestWorkout->id, $viewWorkout->first()->id);
                $this->assertSame($latestWorkout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_workouts_first()
    {
        $latestWorkout = Workout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestWorkout = Workout::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkout) use ($oldestWorkout) {
                $this->assertCount(2, $viewWorkout);
                $this->assertSame($oldestWorkout->id, $viewWorkout->first()->id);
                $this->assertSame($oldestWorkout->name, $viewWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_view', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function view_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('workout_view', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function view_premium_user_can_view_follower_workout_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_workout_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_private_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_private_workout_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_deleted_workout()
    {
        $workout = Workout::factory()->create();
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_deleted_public_workout_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', $workout->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_paginated_workout_movements()
    {
        $workout = Workout::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movements = Movement::factory()->times(21)->create();
        foreach ($movements as $movement) {
            $movement = new WorkoutMovement([
                'user_id' => $this->premiumUser->id,
                'movement_id' => $movement->id,
                'workout_id' => $workout->id,
            ]);
            $movement->save();
        }

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', [$workout->id, 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            })
            ->assertViewHas('workoutMovements', function ($viewMovements) {
                $this->assertCount(1, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_workout_recorded_workouts()
    {
        $workout = Workout::factory()->create();
        $recordedWorkouts = RecordedWorkout::factory()->times(21)->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', [$workout->id, 'tab' => 'recorded', 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            })
            ->assertViewHas('recordedWorkouts', function ($viewRecordedWorkouts) {
                $this->assertCount(1, $viewRecordedWorkouts);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_workout_spots()
    {
        $workout = Workout::factory()->create();
        $spots = Spot::factory()->times(21)->create();
        $workout->spots()->attach($spots);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', [$workout->id, 'tab' => 'spots', 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            })
            ->assertViewHas('spots', function ($viewSpots) {
                $this->assertCount(1, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_workout_unlinked_linkable_spots()
    {
        $workout = Workout::factory()->create();
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', [$workout->id, 'tab' => 'spots']));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            })
            ->assertViewHas('linkableSpots', function ($viewSpots) {
                $this->assertCount(1, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_workout_linked_linkable_spots()
    {
        $workout = Workout::factory()->create();
        $spot = Spot::factory()->create();
        $workout->spots()->attach($spot->id);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_view', [$workout->id, 'tab' => 'spots']));

        $response->assertOk()
            ->assertViewIs('workouts.view')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                return true;
            })
            ->assertViewHas('linkableSpots', function ($viewSpots) {
                $this->assertCount(0, $viewSpots);
                return true;
            });
    }
}
