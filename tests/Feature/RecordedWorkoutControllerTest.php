<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Models\RecordedWorkout;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use App\Models\WorkoutMovementField;
use Database\Seeders\MovementTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecordedWorkoutControllerTest extends TestCase
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
        $response = $this->get(route('recorded_workout_listing'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recorded_workout_listing'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function listing_premium_user_can_view_their_recorded_workouts()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($recordedWorkout) {
                $this->assertCount(1, $viewRecordedWorkout);
                $this->assertSame($recordedWorkout->id, $viewRecordedWorkout->first()->id);
                $this->assertSame($recordedWorkout->name, $viewRecordedWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_recorded_workout_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) {
                $this->assertCount(0, $viewRecordedWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();
        $recordedWorkout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) {
                $this->assertCount(0, $viewRecordedWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_recorded_workout_between_two_dates()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($recordedWorkout) {
                $this->assertCount(1, $viewRecordedWorkout);
                $this->assertSame($recordedWorkout->id, $viewRecordedWorkout->first()->id);
                $this->assertSame($recordedWorkout->name, $viewRecordedWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_recorded_workout_outside_two_dates()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($recordedWorkout) {
                $this->assertCount(0, $viewRecordedWorkout);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_recorded_workout_first()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $latestRecordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestRecordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($latestRecordedWorkout) {
                $this->assertCount(2, $viewRecordedWorkout);
                $this->assertSame($latestRecordedWorkout->id, $viewRecordedWorkout->first()->id);
                $this->assertSame($latestRecordedWorkout->name, $viewRecordedWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_recorded_workout_first()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $latestRecordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestRecordedWorkout = RecordedWorkout::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($oldestRecordedWorkout) {
                $this->assertCount(2, $viewRecordedWorkout);
                $this->assertSame($oldestRecordedWorkout->id, $viewRecordedWorkout->first()->id);
                $this->assertSame($oldestRecordedWorkout->name, $viewRecordedWorkout->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_paginated_recorded_workouts()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkouts = RecordedWorkout::factory()->times(21)->create();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_listing', ['page' => 2]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewRecordedWorkout) use ($recordedWorkouts) {
                $this->assertCount(1, $viewRecordedWorkout);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('recorded_workout_view', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function view_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recorded_workout_view', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function view_premium_user_can_not_view_recorded_workout_of_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_view', $recordedWorkout->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_view', $recordedWorkout->id));

        $response->assertOk()
            ->assertViewIs('workouts.recorded.view')
            ->assertViewHas('recordedWorkout', function ($viewRecordedWorkout) use ($recordedWorkout) {
                $this->assertSame($recordedWorkout->id, $viewRecordedWorkout->id);
                $this->assertSame($recordedWorkout->name, $viewRecordedWorkout->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_their_own_deleted_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();
        $recordedWorkout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_view', $recordedWorkout->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_non_existent_recorded_workout()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_view', 999));

        $response->assertNotFound();
    }

    /** @test */
    public function create_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('recorded_workout_create', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function create_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recorded_workout_create', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function create_premium_user_can_view_page()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_create', $workout->id));

        $response->assertOk()
            ->assertViewIs('workouts.recorded.create')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->description, $viewWorkout->description);
                return true;
            });
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('recorded_workout_store', 1), [
            'movements' => [
                [
                    'movement' => 1,
                    'fields' => [
                        1 => 25,
                    ]
                ],
            ],
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recorded_workout_store', 1), [
            'movements' => [
                [
                    'movement' => 1,
                    'fields' => [
                        1 => 25,
                    ]
                ],
            ],
        ]);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function store_premium_user_can_store_valid_recorded_workout_and_redirects_to_view()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $this->seed(MovementTypeSeeder::class);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $field = MovementField::factory()->create();
        $workout->planUsers()->attach($this->premiumUser->id, ['date' => now()]);

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_store', $workout->id), [
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 25,
                    ]
                ],
            ],
        ]);

        $recordedWorkout = RecordedWorkout::first();

        $this->assertDatabaseCount('recorded_workouts', 1)
            ->assertDatabaseHas('recorded_workouts', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 1)
            ->assertDatabaseHas('workout_movement_fields', [
                'value' => 25,
            ])
            ->assertDatabaseCount('workout_plans', 1)
            ->assertDatabaseHas('workout_plans', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
                'recorded_workout_id' => $recordedWorkout->id,
            ]);

        $response->assertRedirect(route('recorded_workout_view', $recordedWorkout->id));
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $this->seed(MovementTypeSeeder::class);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $field = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_store', $workout->id), []);

        $this->assertDatabaseCount('recorded_workouts', 0);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('recorded_workout_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recorded_workout_edit', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_edit', $recordedWorkout->id));

        $response->assertRedirect(route('recorded_workout_view', $recordedWorkout->id));
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_edit', $recordedWorkout->id));

        $response->assertOk()
            ->assertViewIs('workouts.recorded.edit')
            ->assertViewHas('recordedWorkout', function ($viewRecordedWorkout) use ($recordedWorkout) {
                $this->assertSame($recordedWorkout->id, $viewRecordedWorkout->id);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('recorded_workout_update', 1), [
            'fields' => [
                1 => 25,
            ],
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recorded_workout_update', 1), [
            'fields' => [
                1 => 25,
            ],
        ]);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_update', $recordedWorkout->id), [
            'fields' => [
                1 => 25,
            ],
        ]);

        $response->assertRedirect(route('recorded_workout_view', $recordedWorkout->id));
    }

    /** @test */
    public function update_owner_premium_user_can_update_recorded_workout_with_valid_data()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $this->seed(MovementTypeSeeder::class);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $field = MovementField::factory()->create();
        $recordedWorkout = RecordedWorkout::factory()->create();
        $workoutMovement = WorkoutMovement::factory()->create();
        $workoutMovementField = WorkoutMovementField::factory()->create(['value' => 20]);

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_update', $recordedWorkout->id), [
            'fields' => [
                $workoutMovementField->id => 25,
            ],
        ]);

        $this->assertDatabaseCount('recorded_workouts', 1)
            ->assertDatabaseHas('recorded_workouts', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 1)
            ->assertDatabaseHas('workout_movement_fields', [
                'value' => 25,
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_recorded_workout_with_invalid_data()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $this->seed(MovementTypeSeeder::class);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $field = MovementField::factory()->create();
        $recordedWorkout = RecordedWorkout::factory()->create();
        $workoutMovement = WorkoutMovement::factory()->create();
        $workoutMovementField = WorkoutMovementField::factory()->create(['value' => 20]);

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_update', $recordedWorkout->id), []);

        $this->assertDatabaseCount('recorded_workouts', 1)
            ->assertDatabaseHas('recorded_workouts', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 1)
            ->assertDatabaseHas('workout_movement_fields', [
                'value' => 20,
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_delete_recorded_workout_and_redirect()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $this->seed(MovementTypeSeeder::class);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $field = MovementField::factory()->create();
        $recordedWorkout = RecordedWorkout::factory()->create();
        $workoutMovement = WorkoutMovement::factory()->create();
        $workoutMovementField = WorkoutMovementField::factory()->create(['value' => 20]);

        $response = $this->actingAs($this->premiumUser)->post(route('recorded_workout_update', $recordedWorkout->id), [
            'fields' => [
                $workoutMovementField->id => 25,
            ],
            'delete' => true,
            'redirect' => route('workout_view', $workout->id),
        ]);

        $this->assertDatabaseCount('recorded_workouts', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);

        $response->assertRedirect(route('workout_view', $workout->id));
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('recorded_workout_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recorded_workout_delete', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_delete', $recordedWorkout->id));

        $response->assertRedirect(route('recorded_workout_view', $recordedWorkout->id));
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_recorded_workout()
    {
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('recorded_workout_delete', $recordedWorkout->id));

        $this->assertDatabaseCount('recorded_workouts', 0);
    }
}
