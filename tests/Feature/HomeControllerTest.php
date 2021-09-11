<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Hit;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'access premium']);
    }

    /** @test */
    public function index_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function index_user_can_view_nothing()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
    }

    /** @test */
    public function index_returns_less_than_4_items_per_section()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user->following()->attach($user1->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user1->id, 'visibility' => 'follower']);
        $challenge = Challenge::factory()->create(['user_id' => $user1->id, 'visibility' => 'public']);
        $hit = Hit::factory()->create(['user_id' => $user->id, 'spot_id' => $spot->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk()
            ->assertViewHas('followedSpots', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                $this->assertSame($spot->name, $viewSpots->first()->name);
                return true;
            })
            ->assertViewHas('followedChallenges', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                $this->assertSame($challenge->name, $viewChallenges->first()->name);
                return true;
            })
            ->assertViewHas('hitlist', function ($viewHitlist) use ($hit) {
                $this->assertCount(1, $viewHitlist);
                $this->assertSame($hit->id, $viewHitlist->first()->id);
                $this->assertSame($hit->name, $viewHitlist->first()->name);
                return true;
            });
    }

    /** @test */
    public function index_returns_up_to_5_items_per_section_without_hometown()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user->following()->attach($user1->id, ['accepted' => true]);
        $spots = Spot::factory()->times(6)->create(['user_id' => $user1->id, 'visibility' => 'follower']);
        $challenges = Challenge::factory()->times(6)->create(['user_id' => $user1->id, 'visibility' => 'public']);
        $hits = Hit::factory()->times(6)->create(['user_id' => $user->id, 'spot_id' => $spots->first()->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk()
            ->assertViewHas('followedSpots', function ($viewSpots) {
                $this->assertCount(5, $viewSpots);
                return true;
            })
            ->assertViewHas('followedChallenges', function ($viewChallenges) {
                $this->assertCount(5, $viewChallenges);
                return true;
            })
            ->assertViewHas('hitlist', function ($viewHitlist) {
                $this->assertCount(5, $viewHitlist);
                return true;
            })
            ->assertViewHas('hometownSpots', function ($viewHometownSpots) {
                $this->assertCount(0, $viewHometownSpots);
                return true;
            })
            ->assertViewHas('hometownChallenges', function ($viewHometownChallenges) {
                $this->assertCount(0, $viewHometownChallenges);
                return true;
            });
    }

    /** @test */
    public function index_returns_up_to_5_items_per_section_with_hometown()
    {
        $user = User::factory()->create();
        $user->hometown_name = 'City of Durham, Durham, County Durham, North East England, England, United Kingdom';
        $user->hometown_bounding = '54.7358637,54.793347,-1.6058428,-1.553796';
        $user->save();
        $user1 = User::factory()->create();
        $user->following()->attach($user1->id, ['accepted' => true]);
        $spots = Spot::factory()->times(6)->create(['user_id' => $user1->id, 'visibility' => 'follower', 'latitude' => '54.768276', 'longitude' => '-1.581320']);
        $challenges = Challenge::factory()->times(6)->create(['user_id' => $user1->id, 'visibility' => 'public']);
        $hits = Hit::factory()->times(6)->create(['user_id' => $user->id, 'spot_id' => $spots->first()->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk()
            ->assertViewHas('followedSpots', function ($viewSpots) {
                $this->assertCount(5, $viewSpots);
                return true;
            })
            ->assertViewHas('followedChallenges', function ($viewChallenges) {
                $this->assertCount(5, $viewChallenges);
                return true;
            })
            ->assertViewHas('hitlist', function ($viewHitlist) {
                $this->assertCount(5, $viewHitlist);
                return true;
            })
            ->assertViewHas('hometownSpots', function ($viewHometownSpots) {
                $this->assertCount(5, $viewHometownSpots);
                return true;
            })
            ->assertViewHas('hometownChallenges', function ($viewHometownChallenges) {
                $this->assertCount(5, $viewHometownChallenges);
                return true;
            });
    }
}
