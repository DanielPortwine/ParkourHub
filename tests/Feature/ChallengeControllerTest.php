<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\ChallengeView;
use App\Models\Spot;
use App\Models\User;
use App\Notifications\SpotChallenged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ChallengeControllerTest extends TestCase
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
    public function listing_non_logged_in_user_can_view_public_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_public_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_public_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_follower_challenges_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_follower_challenges_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_follower_challenges_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_challenges_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_their_own_private_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_their_own_private_challenges()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_private_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_private_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_challenge_of_follower_spot_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_challenge_of_private_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_deleted_public_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_deleted_public_challenges_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_their_own_deleted_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($user)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_public_challenges_between_two_dates()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_public_challenges_between_two_dates()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_public_challenges_outside_two_dates()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_challenges_outside_two_dates()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_public_challenges_matching_search_term()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'name' => 'survival']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['search' => 'survival']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_public_challenges_matching_search_term()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'name' => 'survival']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['search' => 'survival']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_public_challenges_not_matching_search_term()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'name' => 'survival']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['search' => 'ambulance']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_public_challenges_not_matching_search_term()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'name' => 'survival']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['search' => 'ambulance']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) {
                $this->assertCount(0, $viewChallenge);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_only_public_challenges_of_users_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['following' => 'on']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_public_challenges_of_users_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge1 = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['following' => 'on']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_only_public_challenges_they_have_entered()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $user->id]);
        $challenge1 = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['entered' => 'on']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_public_challenges_they_have_entered()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['entered' => 'on']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_only_public_challenges_of_a_given_difficulty()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'difficulty' => 3]);
        $challenge1 = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'difficulty' => 4]);

        $response = $this->actingAs($user)->get(route('challenge_listing', ['difficulty' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_public_challenges_of_a_given_difficulty()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 3]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 4]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['difficulty' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(1, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_newest_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-04-30 19:30:00']);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => '2021-05-31 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_hardest_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 4]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 2]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['sort' => 'difficulty_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_easiest_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 2]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'difficulty' => 4]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['sort' => 'difficulty_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_most_entered_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->times(4)->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->create(['challenge_id' => $challenge1->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['sort' => 'entries_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_least_entered_challenge_first()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);
        $challenge1 = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->times(4)->create(['challenge_id' => $challenge1->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_listing', ['sort' => 'entries_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenge) use ($challenge) {
                $this->assertCount(2, $viewChallenge);
                $this->assertSame($challenge->id, $viewChallenge->first()->id);
                $this->assertSame($challenge->name, $viewChallenge->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_public_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_public_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_public_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_follower_challenge_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_follower_challenge_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_not_view_follower_challenge_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_challenge_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_premium_user_can_view_their_own_private_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_their_own_private_challenge()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_not_view_private_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_private_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_premium_user_can_view_their_own_deleted_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_their_own_deleted_challenge()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_not_view_deleted_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_deleted_challenge_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_non_existent_challenge()
    {
        $response = $this->get(route('challenge_view', 123));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_premium_user_can_not_view_non_existent_challenge()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('challenge_view', 123));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_not_view_non_existent_challenge()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', 123));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_logged_in_user_can_view_paginated_challenge_entries()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->times(11)->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('entries', function ($viewEntries) {
                $this->assertCount(10, $viewEntries);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_paginated_challenge_entries()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->times(11)->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('entries', function ($viewEntries) {
                $this->assertCount(10, $viewEntries);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_paginated_challenge_entries()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->times(11)->create(['challenge_id' => $challenge->id, 'user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('entries', function ($viewEntries) {
                $this->assertCount(10, $viewEntries);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_that_they_have_entered_a_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('entered', function ($viewEntered) {
                $this->assertSame(true, $viewEntered);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_that_they_have_entered_a_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('entered', function ($viewEntered) {
                $this->assertSame(true, $viewEntered);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_the_winning_entry_of_a_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $winner = ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id, 'winner' => true]);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('winner', function ($viewWinner) use ($winner) {
                $this->assertSame($winner->id, $viewWinner->id);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_view_the_winning_entry_of_a_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $winner = ChallengeEntry::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $user->id, 'winner' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            })
            ->assertViewHas('winner', function ($viewWinner) use ($winner) {
                $this->assertSame($winner->id, $viewWinner->id);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_logs_their_view_of_a_challenge_of_a_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 1)
            ->assertDatabaseHas('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function view_premium_user_logs_their_view_of_a_challenge_of_a_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 1)
            ->assertDatabaseHas('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function view_non_premium_user_does_not_log_their_view_of_their_challenge()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 0)
            ->assertDatabaseMissing('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function view_premium_user_does_not_log_their_view_of_their_challenge()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 0)
            ->assertDatabaseMissing('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function view_non_premium_user_does_not_log_their_view_of_a_challenge_they_already_viewed()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        ChallengeView::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 1)
            ->assertDatabaseHas('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function view_premium_user_does_not_log_their_view_of_a_challenge_they_already_viewed()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        ChallengeView::factory()->create(['challenge_id' => $challenge->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', $challenge->id));

        $response->assertOk()
            ->assertViewIs('challenges.view')
            ->assertViewHas('challenge', function ($viewChallenge) use ($challenge) {
                $this->assertSame($viewChallenge->id, $challenge->id);
                return true;
            });

        $this->assertDatabaseCount('challenge_views', 1)
            ->assertDatabaseHas('challenge_views', [
                'challenge_id' => $challenge->id,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function view_non_premium_user_reads_notification_if_coming_from_notification_link()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $user->notify(new SpotChallenged($challenge));
        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user)->get(route('challenge_view', ['id' => $challenge->id, 'notification' => $notification->id]));

        $response->assertRedirect(route('challenge_view', $challenge->id));

        $this->assertDatabaseMissing('notifications', [
                'id' => $notification->id,
                'read_at' => null,
            ])
            ->assertDatabaseHas('notifications', [
                'id' => $notification->id,
            ]);
    }

    /** @test */
    public function view_premium_user_reads_notification_if_coming_from_notification_link()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $this->premiumUser->notify(new SpotChallenged($challenge));
        $notification = $this->premiumUser->unreadNotifications->first();

        $response = $this->actingAs($this->premiumUser)->get(route('challenge_view', ['id' => $challenge->id, 'notification' => $notification->id]));

        $response->assertRedirect(route('challenge_view', $challenge->id));

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ])
            ->assertDatabaseHas('notifications', [
                'id' => $notification->id,
            ]);
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('challenge_store'), [
            'name' => 'Test Challenge',
            'description' => 'This is a test challenge',
            'difficulty' => 4,
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_can_store_valid_challenge_and_redirects_to_view()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('challenge_store'), [
            'spot' => $spot->id,
            'name' => 'Test Challenge',
            'description' => 'This is a test challenge',
            'difficulty' => 4,
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
        ]);

        $this->assertDatabaseCount('challenges', 1)
            ->assertDatabaseHas('challenges', [
                'spot_id' => $spot->id,
                'name' => 'Test Challenge',
                'description' => 'This is a test challenge',
                'difficulty' => 4,
                'visibility' => 'public',
                'youtube' => 'Oykjn35X3EY',
            ]);

        $challenge = Challenge::first();
        $response->assertRedirect(route('challenge_view', $challenge->id));

        Storage::disk('public')->assertExists('images/challenges/' . $thumbnail->hashName());
    }

    /** @test */
    public function store_premium_user_can_store_valid_challenge_and_redirects_to_view()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $thumbnail = UploadedFile::fake()->image('thumbnail.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('challenge_store'), [
            'spot' => $spot->id,
            'name' => 'Test Challenge',
            'description' => 'This is a test challenge',
            'difficulty' => 4,
            'visibility' => 'public',
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
            'thumbnail' => $thumbnail,
        ]);

        $this->assertDatabaseCount('challenges', 1)
            ->assertDatabaseHas('challenges', [
                'spot_id' => $spot->id,
                'name' => 'Test Challenge',
                'description' => 'This is a test challenge',
                'difficulty' => 4,
                'visibility' => 'public',
                'youtube' => 'Oykjn35X3EY',
            ]);

        $challenge = Challenge::first();
        $response->assertRedirect(route('challenge_view', $challenge->id));

        Storage::disk('public')->assertExists('images/challenges/' . $thumbnail->hashName());
    }
}
