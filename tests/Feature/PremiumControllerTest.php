<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Stripe\SetupIntent;
use Tests\TestCase;

class PremiumControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $accessPremium;

    public function setUp(): void
    {
        parent::setUp();

        $this->accessPremium = Permission::create(['name' => 'access premium']);
    }

    /** @test */
    public function index_non_logged_in_user_can_not_view_intent()
    {
        $response = $this->get(route('premium'));

        $response->assertOk()
            ->assertViewIs('premium')
            ->assertViewHas('intent', function ($pageIntent) {
                $this->assertNull($pageIntent);
                return true;
            });
    }

    /** @test */
    public function index_non_premium_user_can_view_intent()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('premium'));

        $response->assertOk()
            ->assertViewIs('premium')
            ->assertViewHas('intent', function ($pageIntent) {
                $this->assertNotNull($pageIntent);
                $this->assertIsObject($pageIntent);
                return true;
            });
    }

    /** @test */
    public function index_premium_non_paying_user_can_view_intent()
    {
        $user = User::factory()->create();
        $user->givePermissionTo($this->accessPremium);

        $response = $this->actingAs($user)->get(route('premium'));

        $response->assertOk()
            ->assertViewIs('premium')
            ->assertViewHas('intent', function ($pageIntent) {
                $this->assertNotNull($pageIntent);
                $this->assertIsObject($pageIntent);
                return true;
            });
    }
}
