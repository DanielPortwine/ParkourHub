<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
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

class MovementControllerTest extends TestCase
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
        $response = $this->get(route('movement_listing'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_listing'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function listing_premium_user_can_view_public_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_follower_movement_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_movement_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_own_private_movement()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_private_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_deleted_public_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_movement()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_movement_of_type()
    {
        $type = MovementType::factory()->create();
        $type1 = MovementType::factory()->create();
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'category_id' => $category->id, 'type_id' => $type->id]);
        $movement1 = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'category_id' => $category1->id, 'type_id' => $type1->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['type' => $type->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_movement_between_two_dates()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_movement_outside_two_dates()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_movement_of_category()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $category1 = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'category_id' => $category->id]);
        $movement1 = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'category_id' => $category1->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['category' => $category->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_movement_with_equipment()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $equipment = Equipment::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $movement1 = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $movement->equipment()->attach($equipment->id, ['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['equipment' => $equipment->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_movement_matching_search_term()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'name' => 'speaker']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['search' => 'speaker']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                $this->assertSame($movement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_movement_not_matching_search_term()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'name' => 'speaker']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['search' => 'whiteboard']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_movement_first()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $latestMovement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);
        $oldestMovement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($latestMovement) {
                $this->assertCount(2, $viewMovements);
                $this->assertSame($latestMovement->id, $viewMovements->first()->id);
                $this->assertSame($latestMovement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_movement_first()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $latestMovement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);
        $oldestMovement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($oldestMovement) {
                $this->assertCount(2, $viewMovements);
                $this->assertSame($oldestMovement->id, $viewMovements->first()->id);
                $this->assertSame($oldestMovement->name, $viewMovements->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_view', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function view_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/equipment/view/' . 1);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function view_premium_user_can_view_public_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('originalMovement', function ($viewMovement) use ($movement) {
                $this->assertSame($movement->id, $viewMovement->id);
                $this->assertSame($movement->name, $viewMovement->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_follower_movement_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('originalMovement', function ($viewMovement) use ($movement) {
                $this->assertSame($movement->id, $viewMovement->id);
                $this->assertSame($movement->name, $viewMovement->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_movement_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_private_movement()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('originalMovement', function ($viewMovement) use ($movement) {
                $this->assertSame($movement->id, $viewMovement->id);
                $this->assertSame($movement->name, $viewMovement->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_private_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_their_own_deleted_movement()
    {
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private', 'deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('originalMovement', function ($viewMovement) use ($movement) {
                $this->assertSame($movement->id, $viewMovement->id);
                $this->assertSame($movement->name, $viewMovement->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_deleted_public_movement_of_different_user()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create();
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', $movement->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_non_existent_movement()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', 1));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_spots_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $spots = Spot::factory()->times(5)->create();
        foreach ($spots as $spot) {
            $movement->spots()->attach([$spot->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'spots']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('spots', function ($viewSpots) {
                $this->assertCount(4, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_spots_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $spots = Spot::factory()->times(25)->create();
        foreach ($spots as $spot) {
            $movement->spots()->attach([$spot->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'spots', 'spots' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('spots', function ($viewSpots) {
                $this->assertCount(5, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_equipment_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipments = Equipment::factory()->times(5)->create();
        foreach ($equipments as $equipment) {
            $movement->equipment()->attach([$equipment->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'equipment']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('equipments', function ($viewEquipment) {
                $this->assertCount(4, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_equipment_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipments = Equipment::factory()->times(25)->create();
        foreach ($equipments as $equipment) {
            $movement->equipment()->attach([$equipment->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'equipment', 'equipment' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('equipments', function ($viewEquipment) {
                $this->assertCount(5, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_progressions_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $progressions = Movement::factory()->times(5)->create();
        foreach ($progressions as $progression) {
            $movement->progressions()->attach([$progression->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'progressions']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('progressions', function ($viewProgressions) {
                $this->assertCount(4, $viewProgressions);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_progressions_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $progressions = Movement::factory()->times(25)->create();
        foreach ($progressions as $progression) {
            $movement->progressions()->attach([$progression->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'progressions', 'progressions' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('progressions', function ($viewProgressions) {
                $this->assertCount(5, $viewProgressions);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_advancements_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $advancements = Movement::factory()->times(5)->create();
        foreach ($advancements as $advancement) {
            $movement->advancements()->attach([$advancement->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'advancements']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('advancements', function ($viewProgressions) {
                $this->assertCount(4, $viewProgressions);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_advancements_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $advancements = Movement::factory()->times(25)->create();
        foreach ($advancements as $advancement) {
            $movement->advancements()->attach([$advancement->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'advancements', 'advancements' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('advancements', function ($viewProgressions) {
                $this->assertCount(5, $viewProgressions);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_exercises_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $type1 = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $exercises = Movement::factory()->times(5)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);
        foreach ($exercises as $exercise) {
            $movement->exercises()->attach([$exercise->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'exercises']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('exercises', function ($viewExercises) {
                $this->assertCount(4, $viewExercises);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_exercises_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $type1 = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $exercises = Movement::factory()->times(25)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);
        foreach ($exercises as $exercise) {
            $movement->exercises()->attach([$exercise->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'exercises', 'exercises' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('exercises', function ($viewExercises) {
                $this->assertCount(5, $viewExercises);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_moves_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $type1 = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $moves = Movement::factory()->times(5)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);
        foreach ($moves as $move) {
            $movement->moves()->attach([$move->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'moves']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('moves', function ($viewMoves) {
                $this->assertCount(4, $viewMoves);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_moves_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $type1 = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $moves = Movement::factory()->times(25)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);
        foreach ($moves as $move) {
            $movement->moves()->attach([$move->id => ['user_id' => $this->premiumUser->id]]);
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'moves', 'moves' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('moves', function ($viewMoves) {
                $this->assertCount(5, $viewMoves);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_their_workouts_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();
        $workoutMovements = WorkoutMovement::factory()->times(5)->create(['recorded_workout_id' => $recordedWorkout->id]);
        foreach ($workoutMovements as $workoutMovement) {
            foreach ($workoutMovement->fields as $field) {
                WorkoutMovementField::factory()->create(['movement_field_id' => $field->id, 'workout_movement_id' => $workoutMovement->id]);
            }
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'history']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('history', function ($viewHistory) {
                $this->assertCount(4, $viewHistory);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_their_paginated_workouts_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $recordedWorkout = RecordedWorkout::factory()->create();
        $workoutMovements = WorkoutMovement::factory()->times(25)->create(['recorded_workout_id' => $recordedWorkout->id]);
        foreach ($workoutMovements as $workoutMovement) {
            foreach ($workoutMovement->fields as $field) {
                WorkoutMovementField::factory()->create(['movement_field_id' => $field->id, 'workout_movement_id' => $workoutMovement->id]);
            }
        }

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'history', 'history' => 2]));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('history', function ($viewHistory) {
                $this->assertCount(5, $viewHistory);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_equipment_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipments = Equipment::factory()->times(5)->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'equipment']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('linkableEquipment', function ($viewLinkableEquipment) {
                $this->assertCount(5, $viewLinkableEquipment);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_progressions_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movementField = MovementField::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $progressions = Movement::factory()->times(5)->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'progressions']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('linkableMovements', function ($viewLinkableProgressions) {
                $this->assertCount(5, $viewLinkableProgressions);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewMovementCategories) {
                $this->assertCount(1, $viewMovementCategories);
                return true;
            })
            ->assertViewHas('movementFields', function ($viewMovementFields) {
                $this->assertCount(1, $viewMovementFields);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_advancements_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movementField = MovementField::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $advancements = Movement::factory()->times(5)->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'advancements']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('linkableMovements', function ($viewLinkableAdvancements) {
                $this->assertCount(5, $viewLinkableAdvancements);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewMovementCategories) {
                $this->assertCount(1, $viewMovementCategories);
                return true;
            })
            ->assertViewHas('movementFields', function ($viewMovementFields) {
                $this->assertCount(1, $viewMovementFields);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_exercises_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $type1 = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movementField = MovementField::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $exercises = Movement::factory()->times(5)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'exercises']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('linkableMovements', function ($viewLinkableExercises) {
                $this->assertCount(5, $viewLinkableExercises);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewMovementCategories) {
                $this->assertCount(1, $viewMovementCategories);
                return true;
            })
            ->assertViewHas('movementFields', function ($viewMovementFields) {
                $this->assertCount(1, $viewMovementFields);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_moves_of_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $type1 = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movementField = MovementField::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'type_id' => $type->id, 'category_id' => $category->id]);
        $moves = Movement::factory()->times(5)->create(['type_id' => $type1->id, 'category_id' => $category1->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_view', [$movement->id, 'tab' => 'moves']));

        $response->assertOk()
            ->assertViewIs('movements.view')
            ->assertViewHas('linkableMovements', function ($viewLinkableMoves) {
                $this->assertCount(5, $viewLinkableMoves);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewMovementCategories) {
                $this->assertCount(1, $viewMovementCategories);
                return true;
            })
            ->assertViewHas('movementFields', function ($viewMovementFields) {
                $this->assertCount(1, $viewMovementFields);
                return true;
            });
    }

    /** @test */
    public function create_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_create'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function create_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_create'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function create_premium_user_can_view_page()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('movement_create'));

        $response->assertOk()
            ->assertViewIs('movements.create');
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('movement_store'), [
            'category' => 1,
            'type' => 1,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'Mg7WANy8QE4',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('movement_store'), [
            'category' => 1,
            'type' => 1,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'Mg7WANy8QE4',
        ]);

        $response->assertRedirect(route('premium'));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
        ]));

        $movement = Movement::first();

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement_from_a_spot()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'spot' => $spot->id,
        ]));

        $movement = Movement::first();

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ])
            ->assertDatabaseCount('spots_movements', 1)
            ->assertDatabaseHas('spots_movements', [
                'movement_id' => $movement->id,
                'spot_id' => $spot->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement_as_a_progression()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();
        $advancement = Movement::factory()->create(['type_id' => $type->id, 'category_id' => $category->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'progression' => $advancement->id,
        ]));

        $movement = Movement::orderByDesc('id')->first();

        $this->assertDatabaseCount('movements', 2)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ])
            ->assertDatabaseCount('movements_progressions', 1)
            ->assertDatabaseHas('movements_progressions', [
                'progression_id' => $movement->id,
                'advancement_id' => $advancement->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement_as_an_advancement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();
        $progression = Movement::factory()->create(['type_id' => $type->id, 'category_id' => $category->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'advancement' => $progression->id,
        ]));

        $movement = Movement::orderByDesc('id')->first();

        $this->assertDatabaseCount('movements', 2)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ])
            ->assertDatabaseHas('movements_progressions', [
                'progression_id' => $progression->id,
                'advancement_id' => $movement->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement_as_an_exercise()
    {
        $type = MovementType::factory()->create(['name' => 'Exercise']);
        $type1 = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movementField = MovementField::factory()->create();
        $move = Movement::factory()->create(['type_id' => $type1->id, 'category_id' => $category1->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'move' => $move->id,
        ]));

        $movement = Movement::orderByDesc('id')->first();

        $this->assertDatabaseCount('movements', 2)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ])
            ->assertDatabaseCount('movements_exercises', 1)
            ->assertDatabaseHas('movements_exercises', [
                'move_id' => $move->id,
                'exercise_id' => $movement->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_store_valid_movement_as_a_move()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $type1 = MovementType::factory()->create(['name' => 'Exercise']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $category1 = MovementCategory::factory()->create(['type_id' => $type1->id]);
        $movementField = MovementField::factory()->create();
        $exercise = Movement::factory()->create(['type_id' => $type1->id, 'category_id' => $category1->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'exercise' => $exercise->id,
        ]));

        $movement = Movement::orderByDesc('id')->first();

        $this->assertDatabaseCount('movements', 2)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
                'description' => 'This is a test movement',
                'visibility' => 'public',
                'youtube' => 'Mg7WANy8QE4',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField->id,
            ])
            ->assertDatabaseCount('movements_exercises', 1)
            ->assertDatabaseHas('movements_exercises', [
                'move_id' => $movement->id,
                'exercise_id' => $exercise->id,
            ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $movementField = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('movement_store', [
            // category missing to invalidate request
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
        ]));

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('movements', 0)
            ->assertDatabaseCount('movements_fields', 0);
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_edit', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_edit', $movement->id));

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_equipment()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movement = Movement::factory()->create();
        $movementField = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_edit', $movement->id));

        $response->assertOk()
            ->assertViewIs('movements.edit')
            ->assertViewHas('movement', function($pageMovement) use ($movement) {
                $this->assertSame($movement->name, $pageMovement->name);
                $this->assertSame($movement->description, $pageMovement->description);
                return true;
            })
            ->assertViewHas('movementFields', function ($pageMovementFields) use ($movementField) {
                $this->assertSame($movementField->name, $pageMovementFields->first()->name);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('movement_update', 1), [
            'category' => 1,
            'type' => 1,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [1],
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('equipment_update', 1), [
            'category' => 1,
            'type' => 1,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [1],
        ]);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_update', $movement->id), [
            'category' => $category->id,
            'type' => $type->id,
            'name' => 'Test Movement',
            'description' => 'This is a test movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
        ]);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function update_owner_premium_user_can_update_movement_with_valid_data()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movementField = MovementField::factory()->create();
        $movementField1 = MovementField::factory()->create();
        $movement = Movement::factory()->create(['name' => 'Test Movement']);
        $movement->fields()->attach($movementField->id);

        $response = $this->actingAs($this->premiumUser)->post(route('movement_update', $movement->id), [
            'name' => 'Updated Movement',
            'description' => 'This is an updated movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField1->id],
        ]);

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => 'Updated Movement',
            ])
            ->assertDatabaseMissing('movements', [
                'name' => 'Test Movement',
            ])
            ->assertDatabaseCount('movements_fields', 1)
            ->assertDatabaseHas('movements_fields', [
                'movement_id' => $movement->id,
                'movement_field_id' => $movementField1->id,
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_movement_with_invalid_data()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create(['type_id' => $type->id]);
        $movement = Movement::factory()->create(['name' => 'Test Movement']);
        $movementField = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('movement_update', $movement->id), [
            // name missing to invalidate request
            'description' => 'This is an updated movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
        ]);
//
        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => 'Test Movement',
            ])
            ->assertDatabaseMissing('movements', [
                'name' => 'Updated Movement',
            ]);
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_delete', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_delete', $movement->id));

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_delete', $movement->id));

        $this->assertDatabaseCount('movements', 1)
            ->assertSoftDeleted($movement);
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_equipment_through_update()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $movementField = MovementField::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('movement_update', $movement->id), [
            'name' => 'Updated Movement',
            'description' => 'This is an updated movement',
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Mg7WANy8QE4',
            'fields' => [$movementField->id],
            'delete' => true,
            'redirect' => route('movement_view', $movement->id),
        ]);

        $this->assertDatabaseCount('movements', 1)
            ->assertSoftDeleted($movement);

        $response->assertRedirect(route('movement_view', $movement->id));
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_recover', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_movement()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_recover', $movement->id));

        $this->assertDatabaseCount('movements', 1)
            ->assertSoftDeleted($movement);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_equipment()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['deleted_at' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_recover', $movement->id));

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => $movement->name,
                'description' => $movement->description,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_remove', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_movement()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_remove', $movement->id));

        $this->assertDatabaseCount('movements', 1)
            ->assertDatabaseHas('movements', [
                'name' => $movement->name,
                'description' => $movement->description,
            ]);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_movement()
    {
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('movement_remove', $movement->id));

        $this->assertDatabaseCount('movements', 0);
    }

    /** @test */
    public function remove_premium_user_with_remove_content_permission_can_remove_private_movement()
    {
        $removeContent = Permission::create(['name' => 'remove content']);
        $user = User::factory()->create();
        $this->premiumUser->givePermissionTo($removeContent);
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_remove', $movement->id));

        $this->assertDatabaseCount('movements', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('movement_report', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('movement_report', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function report_premium_user_can_report_visible_movement()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_report', $movement->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $movement->id,
                'reportable_type' => Movement::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function report_premium_user_can_not_report_invisible_movement()
    {
        $user = User::factory()->create();
        $type = MovementType::factory()->create(['name' => 'Move']);
        $category = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('movement_report', $movement->id));

        $this->assertDatabaseCount('reports', 0);
    }
}
