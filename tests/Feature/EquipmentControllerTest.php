<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\User;
use Database\Seeders\MovementTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use proj4php\projCode\Equi;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EquipmentControllerTest extends TestCase
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
        $response = $this->get('/equipment');

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/equipment');

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function listing_premium_user_can_view_public_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                $this->assertSame($equipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_follower_equipment_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                $this->assertSame($equipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_equipment_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_own_private_equipment()
    {
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                $this->assertSame($equipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_private_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_deleted_public_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $equipment->delete();
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_equipment()
    {
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipment->delete();
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_public_equipment_between_two_dates()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment?date_from=2021-05-31&date_to=2021-06-02');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                $this->assertSame($equipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_equipment_outside_two_dates()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment?date_from=2021-05-01&date_to=2021-05-03');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_public_equipment_matching_search_term()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'name' => 'apocalypse']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment?search=apocalypse');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                $this->assertSame($equipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_equipment_not_matching_search_term()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'name' => 'apocalypse']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment?search=exodus');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_equipment_first()
    {
        $user = User::factory()->create();
        $latestEquipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);
        $oldestEquipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($latestEquipment) {
                $this->assertCount(2, $viewEquipment);
                $this->assertSame($latestEquipment->id, $viewEquipment->first()->id);
                $this->assertSame($latestEquipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_equipment_first()
    {
        $user = User::factory()->create();
        $latestEquipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);
        $oldestEquipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment?sort=date_asc');

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($oldestEquipment) {
                $this->assertCount(2, $viewEquipment);
                $this->assertSame($oldestEquipment->id, $viewEquipment->first()->id);
                $this->assertSame($oldestEquipment->name, $viewEquipment->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_redirects_to_login()
    {
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $response = $this->get('/equipment/view/' . $equipment->id);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function view_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $response = $this->actingAs($user)->get('/equipment/view/' . $equipment->id);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function view_premium_user_can_view_public_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('equipment', function ($viewEquipment) use ($equipment) {
                $this->assertSame($equipment->id, $viewEquipment->id);
                $this->assertSame($equipment->name, $viewEquipment->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_follower_equipment_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('equipment', function ($viewEquipment) use ($equipment) {
                $this->assertSame($equipment->id, $viewEquipment->id);
                $this->assertSame($equipment->name, $viewEquipment->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_equipment_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function view_premium_user_can_view_their_own_private_equipment()
    {
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('equipment', function ($viewEquipment) use ($equipment) {
                $this->assertSame($equipment->id, $viewEquipment->id);
                $this->assertSame($equipment->name, $viewEquipment->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_private_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function view_premium_user_can_not_view_deleted_public_equipment_of_different_user()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $equipment->delete();
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function view_premium_user_can_view_their_own_deleted_equipment()
    {
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipment->delete();
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('equipment', function ($viewEquipment) use ($equipment) {
                $this->assertSame($equipment->id, $viewEquipment->id);
                $this->assertSame($equipment->name, $viewEquipment->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_non_existent_equipment()
    {
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/999');

        $response->assertViewIs('errors.404');
    }

    /** @test */
    public function view_premium_user_can_view_only_four_movements()
    {
        $this->seed(MovementTypeSeeder::class);
        $exerciseTypeID = MovementType::where('name', 'Exercise')->first()->id;
        $movementCategory = MovementCategory::factory()->create(['type_id' => $exerciseTypeID]);
        $movements = Movement::factory()->times(5)->create(['type_id' => $exerciseTypeID, 'category_id' => $movementCategory->id]);
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipment->movements()->attach($movements, ['user_id' => $this->premiumUser->id]);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('movements', function ($viewMovements) {
                $this->assertCount(4, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_movements()
    {
        $this->seed(MovementTypeSeeder::class);
        $exerciseTypeID = MovementType::where('name', 'Exercise')->first()->id;
        $movementCategory = MovementCategory::factory()->create(['type_id' => $exerciseTypeID]);
        $movements = Movement::factory()->times(25)->create(['type_id' => $exerciseTypeID, 'category_id' => $movementCategory->id]);
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $equipment->movements()->attach($movements, ['user_id' => $this->premiumUser->id]);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id . '?movements=2');

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('movements', function ($viewMovements) {
                $this->assertCount(5, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_linkable_movements()
    {
        $this->seed(MovementTypeSeeder::class);
        $exerciseTypeID = MovementType::where('name', 'Exercise')->first()->id;
        $movementCategory = MovementCategory::factory()->create(['type_id' => $exerciseTypeID]);
        $movements = Movement::factory()->times(5)->create(['type_id' => $exerciseTypeID, 'category_id' => $movementCategory->id]);
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('linkableMovements', function ($viewLinkableMovements) {
                $this->assertCount(5, $viewLinkableMovements);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_movement_categories()
    {
        $this->seed(MovementTypeSeeder::class);
        $exerciseTypeID = MovementType::where('name', 'Exercise')->first()->id;
        $movementCategories = MovementCategory::factory()->times(5)->create(['type_id' => $exerciseTypeID]);
        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->get('/equipment/view/' . $equipment->id);

        $response->assertOk()
            ->assertViewIs('equipment.view')
            ->assertViewHas('movementCategories', function ($viewMovementCategories) {
                $this->assertCount(5, $viewMovementCategories);
                return true;
            });
    }

    /** @test */
    public function create_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('equipment_create'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function create_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('equipment_create'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function create_premium_user_can_view_page()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('equipment_create'));

        $response->assertOk()
            ->assertViewIs('equipment.create');
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('equipment_store'), [
            'name' => 'Test Equipment',
            'description' => 'This is test equipment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('equipment_store'), [
            'name' => 'Test Equipment',
            'description' => 'This is test equipment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect(route('premium'));
    }

    /** @test */
    public function store_premium_user_can_store_valid_equipment_and_redirects_to_view()
    {
        $this->seed(MovementTypeSeeder::class);
        $movementCategories = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $response = $this->actingAs($this->premiumUser)->post(route('equipment_store'), [
            'name' => 'Test Equipment',
            'description' => 'This is test equipment',
            'visibility' => 'public',
            'movement' => $movement->id,
        ]);

        $this->assertDatabaseCount('equipment', 1)
            ->assertDatabaseHas('equipment', [
                'name' => 'Test Equipment',
                'description' => 'This is test equipment',
                'visibility' => 'public',
            ])
            ->assertDatabaseCount('movements_equipments', 1);

        $equipment = Equipment::first();
        $response->assertRedirect(route('equipment_view', $equipment->id));
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_equipment()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('equipment_store'), [
            'name' => 'Test Equipment',
            'description' => 'This is test equipment',
            'visibility' => 'public',
            'movement' => 5,
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('equipment_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('equipment_edit', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->get(route('equipment_edit', $equipment->id));

        $response->assertRedirect(route('equipment_view', $equipment->id));
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_equipment()
    {
        $equipment = Equipment::factory()->create();
        $response = $this->actingAs($this->premiumUser)->get(route('equipment_edit', $equipment->id));

        $response->assertOk()
            ->assertViewIs('equipment.edit')
            ->assertViewHas('equipment', function($pageEquipment) use ($equipment) {
                $this->assertSame($equipment->name, $pageEquipment->name);
                $this->assertSame($equipment->desription, $pageEquipment->desription);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('equipment_update', 1), [
            'name' => 'Test Equipment',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('equipment_update', 1), [
            'name' => 'Test Equipment',
        ]);

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $response = $this->actingAs($this->premiumUser)->post(route('equipment_update', $equipment->id), [
            'name' => 'Test Equipment',
            'description' => 'New description of equipment',
            'visibility' => 'follower',
        ]);

        $response->assertRedirect(route('equipment_view', $equipment->id));
    }

    /** @test */
    public function update_owner_premium_user_can_update_equipment_with_valid_data()
    {
        $equipment = Equipment::factory()->create(['name' => 'Test Equipment']);
        $response = $this->actingAs($this->premiumUser)->post(route('equipment_update', $equipment->id), [
            'name' => 'Updated Equipment',
            'description' => 'New description of equipment',
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('equipment', 1)
            ->assertDatabaseHas('equipment', [
                'name' => 'Updated Equipment',
            ])
            ->assertDatabaseMissing('equipment', [
                'name' => 'Test Equipment',
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_equipment_with_invalid_data()
    {
        $equipment = Equipment::factory()->create(['name' => 'Test Equipment']);
        $response = $this->actingAs($this->premiumUser)->post(route('equipment_update', $equipment->id), [
            'name' => 'Updated Equipment Longer Than Twenty Five Characters',
            'description' => 'New description of equipment',
            'visibility' => 'follower',
        ]);

        $this->assertDatabaseCount('equipment', 1)
            ->assertDatabaseHas('equipment', [
                'name' => 'Test Equipment',
            ])
            ->assertDatabaseMissing('equipment', [
                'name' => 'Updated Equipment Longer Than Twenty Five Characters',
            ]);
    }
}
