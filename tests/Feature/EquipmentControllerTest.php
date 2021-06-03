<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function listing_non_premium_user_redirects_to_login()
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
}
