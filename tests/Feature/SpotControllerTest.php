<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Hit;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Models\MovementType;
use App\Models\Review;
use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\SpotView;
use App\Models\User;
use App\Models\Workout;
use App\Notifications\SpotCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SpotControllerTest extends TestCase
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
    public function index_non_logged_in_user_can_view_index()
    {
        $response = $this->get(route('spots'));

        $response->assertOk()
            ->assertViewIs('spots.index');
    }

    /** @test */
    public function index_non_premium_user_can_view_index()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('spots'));

        $response->assertOk()
            ->assertViewIs('spots.index');
    }

    /** @test */
    public function index_premium_user_can_view_index()
    {
        $response = $this->actingAs($this->premiumUser)->get(route('spots'));

        $response->assertOk()
            ->assertViewIs('spots.index');
    }

    /** @test */
    public function listing_non_logged_in_user_can_view_public_spots()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_follower_spots_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_follower_spots_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_logged_in_user_can_not_view_follower_spots()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_their_own_private_spots()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_private_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_logged_in_user_can_not_view_private_spots()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_deleted_public_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_their_own_deleted_spots()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_logged_in_user_can_not_view_deleted_public_spots()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_spots_between_two_dates()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($user)->get(route('spot_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_spots_outside_two_dates()
    {
        $spot = Spot::factory()->create(['created_at' => '2021-06-01 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_non_premium_user_can_view_public_spots_matching_search_term()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public', 'name' => 'reality']);

        $response = $this->actingAs($user)->get(route('spot_listing', ['search' => 'reality']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_not_view_spots_not_matching_search_term()
    {
        $spot = Spot::factory()->create(['name' => 'reality']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['search' => 'history']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(0, $viewSpot);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_public_spots_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot1 = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['following' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_spots_of_given_rating()
    {
        $spot = Spot::factory()->create(['rating' => '3']);
        $spot1 = Spot::factory()->create(['rating' => '4']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['rating' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_spots_on_their_hitlist()
    {
        $spot = Spot::factory()->create();
        $spot1 = Spot::factory()->create();
        $hit = new Hit([
            'user_id' => $this->premiumUser->id,
            'spot_id' => $spot->id,
        ]);
        $hit->save();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['on_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_only_spots_ticked_off_their_hitlist()
    {
        $spot = Spot::factory()->create();
        $spot1 = Spot::factory()->create();
        $hit = new Hit([
            'user_id' => $this->premiumUser->id,
            'spot_id' => $spot->id,
            'completed_at' => now(),
        ]);
        $hit->save();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['ticked_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($spot) {
                $this->assertCount(1, $viewSpot);
                $this->assertSame($spot->id, $viewSpot->first()->id);
                $this->assertSame($spot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_latest_spots_first()
    {
        $latestSpot = Spot::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestSpot = Spot::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($latestSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($latestSpot->id, $viewSpot->first()->id);
                $this->assertSame($latestSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_oldest_spots_first()
    {
        $latestSpot = Spot::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestSpot = Spot::factory()->create(['created_at' => '2021-04-30 19:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($oldestSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($oldestSpot->id, $viewSpot->first()->id);
                $this->assertSame($oldestSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_highest_rated_spots_first()
    {
        $bestSpot = Spot::factory()->create(['rating' => '4']);
        $worstSpot = Spot::factory()->create(['rating' => '2']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['sort' => 'rating_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($bestSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($bestSpot->id, $viewSpot->first()->id);
                $this->assertSame($bestSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_lowest_rated_spots_first()
    {
        $bestSpot = Spot::factory()->create(['rating' => '4']);
        $worstSpot = Spot::factory()->create(['rating' => '2']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['sort' => 'rating_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($worstSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($worstSpot->id, $viewSpot->first()->id);
                $this->assertSame($worstSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_most_viewed_spots_first()
    {
        $mostSpot = Spot::factory()->create(['rating' => '4']);
        $leastSpot = Spot::factory()->create(['rating' => '2']);
        $mostSpotViews = SpotView::factory()->times(2)->create(['spot_id' => $mostSpot->id]);
        $leastSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['sort' => 'views_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($mostSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($mostSpot->id, $viewSpot->first()->id);
                $this->assertSame($mostSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function listing_premium_user_can_view_least_viewed_spots_first()
    {
        $mostSpot = Spot::factory()->create(['rating' => '4']);
        $leastSpot = Spot::factory()->create(['rating' => '2']);
        $mostSpotViews = SpotView::factory()->times(2)->create(['spot_id' => $mostSpot->id]);
        $leastSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_listing', ['sort' => 'views_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpot) use ($leastSpot) {
                $this->assertCount(2, $viewSpot);
                $this->assertSame($leastSpot->id, $viewSpot->first()->id);
                $this->assertSame($leastSpot->name, $viewSpot->first()->name);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_public_spot()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                $this->assertSame($spot->name, $viewSpot->name);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_can_view_follower_spot_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                $this->assertSame($spot->name, $viewSpot->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_follower_spot_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_follower_spot()
    {
        $spot = Spot::factory()->create(['visibility' => 'follower']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_premium_user_can_view_their_own_private_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                $this->assertSame($spot->name, $viewSpot->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_private_spot_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_private_spot()
    {
        $spot = Spot::factory()->create(['visibility' => 'private']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_premium_user_can_view_their_own_deleted_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);
        $spot->delete();

        $response = $this->actingAs($user)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                $this->assertSame($spot->name, $viewSpot->name);
                return true;
            });
    }

    /** @test */
    public function view_premium_user_can_not_view_public_deleted_spot_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_deleted_public_spot()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $spot->delete();

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertNotFound();
    }

    /** @test */
    public function view_premium_user_can_view_spot_hit()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $hit = Hit::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('hit', function ($viewHit) {
                $this->assertNotNull($viewHit);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_spot_unlinked_linkable_movements()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementType = MovementType::factory()->create(['name' => 'Move']);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('linkableMovements', function ($viewMovements) {
                $this->assertCount(1, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_spot_linked_linkable_movements()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementType = MovementType::factory()->create(['name' => 'Move']);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);
        $spot->movements()->attach($movement->id, ['user_id' => $this->premiumUser->id]);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('linkableMovements', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_spot_unlinked_unlinkable_movements()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementType = MovementType::factory()->create(['name' => 'Exercise']);
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('linkableMovements', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_spot_move_categories()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementType = MovementType::factory()->create(['name' => 'Move']);
        $movementCategory = MovementCategory::factory()->create();

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewCategories) {
                $this->assertCount(1, $viewCategories);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_spot_exercise_categories()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementType = MovementType::factory()->create(['name' => 'Exercise']);
        $movementCategory = MovementCategory::factory()->create();

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('movementCategories', function ($viewCategories) {
                $this->assertCount(0, $viewCategories);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_spot_movement_fields()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $movementField = MovementField::factory()->create();

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('movementFields', function ($viewFields) {
                $this->assertCount(1, $viewFields);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_spot_unlinked_linkable_workouts()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $workout = Workout::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'workouts']));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('linkableWorkouts', function ($viewWorkouts) {
                $this->assertCount(1, $viewWorkouts);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_not_view_spot_linked_linkable_workouts()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $workout = Workout::factory()->create(['visibility' => 'public']);
        $spot->workouts()->attach($workout->id);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'workouts']));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('linkableWorkouts', function ($viewWorkouts) {
                $this->assertCount(0, $viewWorkouts);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_unpaginated_spot_reviews()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $reviews = Review::factory()->times(5)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('reviews', function ($viewReviews) {
                $this->assertCount(4, $viewReviews);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_paginated_spot_reviews()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $reviews = Review::factory()->times(21)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'reviews' => 2]));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('reviews', function ($viewReviews) {
                $this->assertCount(1, $viewReviews);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_unpaginated_spot_comments()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $comments = SpotComment::factory()->times(5)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'comments']));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('comments', function ($viewComments) {
                $this->assertCount(4, $viewComments);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_paginated_spot_comments()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $comments = SpotComment::factory()->times(21)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'comments', 'comments' => 2]));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('comments', function ($viewComments) {
                $this->assertCount(1, $viewComments);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_unpaginated_spot_challenges()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $challenges = Challenge::factory()->times(5)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'challenges']));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('challenges', function ($viewChallenges) {
                $this->assertCount(4, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_paginated_spot_challenges()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $challenges = Challenge::factory()->times(21)->create(['visibility' => 'public']);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'challenges', 'challenges' => 2]));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('challenges', function ($viewChallenges) {
                $this->assertCount(1, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_unpaginated_spot_workouts()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $workouts = Workout::factory()->times(5)->create(['visibility' => 'public']);
        $spot->workouts()->attach($workouts);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'workouts']));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('workouts', function ($viewWorkouts) {
                $this->assertCount(4, $viewWorkouts);
                return true;
            });
    }

    /** @test */
    public function view_non_logged_in_user_can_view_paginated_spot_workouts()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $workouts = Workout::factory()->times(21)->create(['visibility' => 'public']);
        $spot->workouts()->attach($workouts);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'workouts', 'workouts' => 2]));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('workouts', function ($viewWorkouts) {
                $this->assertCount(1, $viewWorkouts);
                return true;
            });
    }

    /** @test */
    public function view_non_premium_user_logs_their_view_of_a_spot_of_a_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            });

        $this->assertDatabaseCount('spot_views', 1)
            ->assertDatabaseHas('spot_views', [
                'spot_id' => $spot->id,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function view_premium_user_does_not_log_their_view_of_their_own_spot()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            });

        $this->assertDatabaseCount('spot_views', 0);
    }

    /** @test */
    public function view_premium_user_does_not_log_their_view_of_a_spot_they_already_viewed()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        SpotView::factory()->create(['spot_id' => $spot->id, 'user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            });

        $this->assertDatabaseCount('spot_views', 1)
            ->assertDatabaseHas('spot_views', [
                'spot_id' => $spot->id,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function view_non_premium_user_reads_notification_if_coming_from_notification_link()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $user->notify(new SpotCreated($spot));
        $notification = $user->unreadNotifications->first();

        $response = $this->actingAs($user)->get(route('spot_view', ['id' => $spot->id, 'notification' => $notification->id]));

        $response->assertRedirect(route('spot_view', $spot->id));

        $this->assertDatabaseMissing('notifications', [
                'id' => $notification->id,
                'read_at' => null,
            ])
            ->assertDatabaseHas('notifications', [
                'id' => $notification->id,
            ]);
    }
}
