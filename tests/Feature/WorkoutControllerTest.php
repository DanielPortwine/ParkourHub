<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Models\MovementType;
use App\Models\RecordedWorkout;
use App\Models\Spot;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkoutControllerTest extends TestCase
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

    /** @test */
    public function create_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_create'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function create_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_create'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function create_premium_user_can_view_create()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_create'));

        $response->assertOk()
            ->assertViewIs('workouts.create')
            ->assertViewHas('movements', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('workout_store', []));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workout_store', []));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function store_premium_user_can_store_workout_with_valid_data_and_notify_premium_followers()
    {
        $user = User::factory()->create();
        $user->givePermissionTo($this->accessPremium);
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        setting()->set('notifications_new_workout', 'on-site', $user->id);
        setting()->save($user->id);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => 'Test Workout',
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseHas('workouts', [
                'name' => 'Test Workout',
                'description' => 'This is a test workout',
            ])
            ->assertDatabaseCount('workout_movements', 1)
            ->assertDatabaseHas('workout_movements', [
                'movement_id' => $movement->id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 1)
            ->assertDatabaseHas('workout_movement_fields', [
                'movement_field_id' => $movement->fields()->first()->id,
                'value' => 3,
            ])
            ->assertDatabaseCount('notifications', 1);
    }

    /** @test */
    public function store_premium_user_can_store_workout_with_valid_data_and_store_a_recorded_workout()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => 'Test Workout',
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
            'create-record' => true,
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseHas('workouts', [
                'name' => 'Test Workout',
                'description' => 'This is a test workout',
            ])
            ->assertDatabaseCount('workout_movements', 2)
            ->assertDatabaseHas('workout_movements', [
                'movement_id' => $movement->id,
                'recorded_workout_id' => null,
            ])
            ->assertDatabaseHas('workout_movements', [
                'movement_id' => $movement->id,
                'recorded_workout_id' => $movement->workouts()->whereNotNull('recorded_workout_id')->first()->recorded_workout_id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 2)
            ->assertDatabaseHas('workout_movement_fields', [
                'movement_field_id' => $movement->fields()->first()->id,
                'workout_movement_id' => $movement->workouts()->whereNull('recorded_workout_id')->first()->id,
                'value' => 3,
            ])
            ->assertDatabaseHas('workout_movement_fields', [
                'movement_field_id' => $movement->fields()->first()->id,
                'workout_movement_id' => $movement->workouts()->whereNotNull('recorded_workout_id')->first()->id,
                'value' => 3,
            ])
            ->assertDatabaseCount('recorded_workouts', 1);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_without_name()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            // name missing
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_with_long_name()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => 'This name is too long to be valid',
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_with_array_name()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_without_movements()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            // movements missing
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_without_movements_in_movements()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_with_nonexistent_movement()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => 987,
                    'fields' => [
                        $field->id => 3,
                    ]
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_without_movement_fields()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_with_empty_movement_fields()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => []
                ]
            ],
            'visibility' => 'public',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_without_visibility()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => []
                ]
            ],
            // visibility missing
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_workout_with_invalid_visibility()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_store'), [
            'name' => ['name' => 'Test Workout'],
            'description' => 'This is a test workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => []
                ]
            ],
            'visibility' => 'invalid',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('workouts', 0)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0);
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_edit', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_edit', $workout->id));

        $response->assertRedirect(route('workout_view', $workout->id));
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_workout()
    {
        $workout = Workout::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $workoutMovement = WorkoutMovement::factory()->create(['recorded_workout_id' => null]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_edit', $workout->id));

        $response->assertOk()
            ->assertViewIs('workouts.edit')
            ->assertViewHas('workout', function ($viewWorkout) use ($workout, $movement) {
                $this->assertSame($workout->id, $viewWorkout->id);
                $this->assertSame($workout->name, $viewWorkout->name);
                $this->assertSame($workout->movements()->first()->id, $viewWorkout->movements()->first()->id);
                return true;
            })
            ->assertViewHas('movements', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('workout_update', 1), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workout_update', 1), []);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [],
                ]
            ],
            'visibility' => 'follower',
        ]);

        $response->assertRedirect(route('workout_view', $workout->id));
    }

    /** @test */
    public function update_owner_premium_user_can_update_workout_with_valid_data_and_notify_premium_followers_and_bookmarkers_once()
    {
        $follower = User::factory()->create();
        $follower->givePermissionTo($this->accessPremium);
        $this->premiumUser->followers()->attach($follower->id, ['accepted' => true]);
        setting()->set('notifications_workout_updated', 'on-site', $follower->id);
        setting()->save($follower->id);
        $bookmarker = User::factory()->create();
        $bookmarker->givePermissionTo($this->accessPremium);
        $this->premiumUser->followers()->attach($bookmarker->id, ['accepted' => true]);
        setting()->set('notifications_workout_updated', 'on-site', $bookmarker->id);
        setting()->save($bookmarker->id);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        $workout->bookmarks()->attach($bookmarker->id);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseHas('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'follower',
            ])
            ->assertDatabaseCount('workout_movements', 1)
            ->assertDatabaseHas('workout_movements', [
                'movement_id' => $movement->id,
            ])
            ->assertDatabaseCount('workout_movement_fields', 1)
            ->assertDatabaseHas('workout_movement_fields', [
                'movement_field_id' => $movement->fields()->first()->id,
                'value' => 5,
            ])
            ->assertDatabaseCount('notifications', 2);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_without_a_name()
    {
        $follower = User::factory()->create();
        $follower->givePermissionTo($this->accessPremium);
        $this->premiumUser->followers()->attach($follower->id, ['accepted' => true]);
        setting()->set('notifications_workout_updated', 'on-site', $follower->id);
        setting()->save($follower->id);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            // name missing
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_with_a_long_name()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'This Workout Name Is Far Too Long',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_without_movements()
    {
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            // missing movements
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_without_movements_in_movements()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_with_non_existent_movements()
    {
        $field = MovementField::factory()->create();
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => 7584,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_without_movement_fields()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    // missing fields
                ]
            ],
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_workout_without_visibility()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => 'Updated Workout',
            'description' => 'This is an updated workout',
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            // missing visibility
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function update_owner_premium_user_can_delete_workout_and_redirect()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $field = MovementField::factory()->create();
        $movement->fields()->attach($field->id);
        $workout = Workout::factory()->create(['visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('workout_update', $workout->id), [
            'name' => $workout->name,
            'description' => $workout->description,
            'movements' => [
                [
                    'movement' => $movement->id,
                    'fields' => [
                        $field->id => 5,
                    ]
                ]
            ],
            'visibility' => 'follower',
            'delete' => true,
            'redirect' => route('workout_view', $workout->id),
        ]);

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseMissing('workouts', [
                'name' => 'Updated Workout',
                'description' => 'This is an updated workout',
                'visibility' => 'private',
            ])
            ->assertSoftDeleted($workout)
            ->assertDatabaseCount('workout_movements', 0)
            ->assertDatabaseCount('workout_movement_fields', 0)
            ->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_delete', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_delete', $workout->id));

        $response->assertRedirect(route('workout_view', $workout->id));
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_workout()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_delete', $workout->id));

        $this->assertDatabaseCount('workouts', 1)
            ->assertSoftDeleted($workout);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_recover', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_recover', $workout->id));

        $this->assertDatabaseCount('workouts', 1)
            ->assertSoftDeleted($workout);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_workout()
    {
        $workout = Workout::factory()->create();
        $workout->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_recover', $workout->id));

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseHas('workouts', [
                'name' => $workout->name,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_remove', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_remove', $workout->id));

        $this->assertDatabaseCount('workouts', 1)
            ->assertDatabaseHas('workouts', [
                'name' => $workout->name,
            ]);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_workout()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_remove', $workout->id));

        $this->assertDatabaseCount('workouts', 0);
    }

    /** @test */
    public function remove_random_premium_user_with_remove_content_permission_can_remove_workout()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $this->premiumUser->givePermissionTo($removeContent);
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_remove', $workout->id));

        $this->assertDatabaseCount('workouts', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_report', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_report', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function report_random_premium_user_can_report_visible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_report', $workout->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $workout->id,
                'reportable_type' => Workout::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function report_random_premium_user_can_not_report_invisible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_report', $workout->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_report_non_logged_in_user_redirects_to_login()
    {
        $workout = Workout::factory()->create();

        $response = $this->get(route('workout_report_discard', $workout->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_report_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_report_discard', $workout->id));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function discard_reports_random_premium_user_can_not_discard_workout_reports()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$workout->id, Workout::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_report_discard', $workout->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $workout->id,
                'reportable_type' => Workout::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_can_not_discard_workout_reports()
    {
        $workout = Workout::factory()->create();
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$workout->id, Workout::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_report_discard', $workout->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $workout->id,
                'reportable_type' => Workout::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_premium_user_with_manage_reports_permission_can_discard_workout_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$workout->id, Workout::class, $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_report_discard', $workout->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function bookmark_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_bookmark', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function bookmark_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_bookmark', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function bookmark_random_premium_user_can_bookmark_visible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_bookmark', $workout->id));

        $this->assertDatabaseCount('workout_bookmarks', 1)
            ->assertDatabaseHas('workout_bookmarks', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ]);
    }

    /** @test */
    public function bookmark_random_premium_user_can_bookmark_visible_workout_once()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout->bookmarks()->attach($this->premiumUser->id);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_bookmark', $workout->id));

        $this->assertDatabaseCount('workout_bookmarks', 1)
            ->assertDatabaseHas('workout_bookmarks', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ]);
    }

    /** @test */
    public function bookmark_random_premium_user_can_not_bookmark_invisible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_bookmark', $workout->id));

        $this->assertDatabaseCount('workout_bookmarks', 0);
    }

    /** @test */
    public function unbookmark_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_unbookmark', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function unbookmark_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_unbookmark', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function unbookmark_random_premium_user_can_unbookmark_visible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout->bookmarks()->attach($this->premiumUser->id);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_unbookmark', $workout->id));

        $this->assertDatabaseCount('workout_bookmarks', 0);
    }

    /** @test */
    public function unbookmark_random_premium_user_can_not_unbookmark_invisible_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $workout->bookmarks()->attach($this->premiumUser->id);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_unbookmark', $workout->id));

        $this->assertDatabaseCount('workout_bookmarks', 1)
            ->assertDatabaseHas('workout_bookmarks', [
                'user_id' => $this->premiumUser->id,
                'workout_id' => $workout->id,
            ]);
    }

    /** @test */
    public function delete_movement_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_movement_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_movement_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_movement_delete', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function delete_movement_random_premium_user_can_not_delete_workout_movement()
    {
        $user = User::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workoutMovement = WorkoutMovement::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_movement_delete', $workout->movements()->first()->id));

        $this->assertDatabaseCount('workout_movements', 1)
            ->assertDatabaseHas('workout_movements', [
                'workout_id' => $workout->id,
                'movement_id' => $movement->id,
            ]);
    }

    /** @test */
    public function delete_movement_owner_premium_user_can_delete_workout_movement()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $workout = Workout::factory()->create();
        $workoutMovement = WorkoutMovement::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('workout_movement_delete', $workout->movements()->first()->id));

        $this->assertDatabaseCount('workout_movements', 0);
    }
}
