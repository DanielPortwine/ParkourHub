<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\User;
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
}
