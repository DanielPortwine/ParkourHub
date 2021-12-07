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
use App\Models\Comment;
use App\Models\SpotView;
use App\Models\User;
use App\Models\Workout;
use App\Notifications\SpotCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function view_premium_user_can_view_spot_locals_ids()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $local = User::factory()->create();
        $spot->locals()->attach($local->id);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_view', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('localsIDs', function ($viewLocalsIDs) use ($local) {
                $this->assertCount(1, $viewLocalsIDs);
                $this->assertSame($local->id, $viewLocalsIDs[0]);
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
        $comments = Comment::factory()->times(5)->create(['visibility' => 'public']);

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
        $comments = Comment::factory()->times(21)->create(['visibility' => 'public']);

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
    public function view_non_logged_in_user_can_view_paginated_spot_locals()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);
        $locals = User::factory()->times(41)->create();
        $spot->locals()->attach($locals);

        $response = $this->get(route('spot_view', [$spot->id, 'tab' => 'locals', 'page' => 2]));

        $response->assertOk()
            ->assertViewIs('spots.view')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                return true;
            })
            ->assertViewHas('locals', function ($viewLocals) {
                $this->assertCount(1, $viewLocals);
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

    /** @test */
    public function fetch_non_logged_in_user_can_get_public_spots()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_non_premium_user_can_get_follower_spots_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_premium_user_can_not_get_follower_spots_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_non_premium_user_can_get_their_own_private_spots()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_premium_user_can_not_get_private_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_premium_user_can_not_get_public_deleted_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function fetch_premium_user_can_not_get_their_own_deleted_spots()
    {
        $spot = Spot::factory()->create();
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_fetch'));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('spot_store'), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_can_store_valid_spot_and_redirects_to_map_on_spot()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($user)->post(route('spot_store'), [
            'name' => 'Test Spot',
            'description' => 'This is a test spot',
            'coordinates' => '-14464782.609967,6708530.8180593',
            'lat_lon' => '51.490558,-129.939353',
            'visibility' => 'public',
            'image' => $image,
        ]);

        $this->assertDatabaseCount('spots', 1)
            ->assertDatabaseHas('spots', [
                'name' => 'Test Spot',
                'description' => 'This is a test spot',
                'coordinates' => '-14464782.609967,6708530.8180593',
                'latitude' => '51.490558',
                'longitude' => '-129.939353',
                'visibility' => 'public',
            ]);

        $spot = Spot::first();
        $response->assertRedirect(route('spots', ['spot' => $spot->id]));

        Storage::disk('public')->assertExists('images/spots/' . $image->hashName());
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_spot()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($user)->post(route('spot_store'), [
            // name missing to invalidate request
            'description' => 'This is a test spot',
            'coordinates' => '-14464782.609967,6708530.8180593',
            'lat_lon' => '51.490558,-129.939353',
            'visibility' => 'public',
            'image' => $image,
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('spots', 0);

        Storage::disk('public')->assertMissing('images/spots/' . $image->hashName());
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_edit', $spot->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_spot()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_edit', $spot->id));

        $response->assertOk()
            ->assertViewIs('spots.edit')
            ->assertViewHas('spot', function ($viewSpot) use ($spot) {
                $this->assertSame($spot->id, $viewSpot->id);
                $this->assertSame($spot->name, $viewSpot->name);
                $this->assertSame($spot->coordinates, $viewSpot->coordinates);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('spot_update', 1), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_update', $spot->id), [
            'name' => 'Updated Spot',
            'description' => 'This is an updated spot',
            'visibility' => 'public',
            'image' => $image,
        ]);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_owner_non_premium_user_can_update_spot_with_valid_data()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($user)->post(route('spot_update', $spot->id), [
            'name' => 'Updated Spot',
            'description' => 'This is an updated spot',
            'visibility' => 'public',
            'image' => $image,
        ]);

        $this->assertDatabaseCount('spots', 1)
            ->assertDatabaseHas('spots', [
                'name' => 'Updated Spot',
                'description' => 'This is an updated spot',
                'visibility' => 'public',
            ]);

        Storage::disk('public')->assertExists('images/spots/' . $image->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_spot_with_invalid_data()
    {
        Storage::fake('public');

        $spot = Spot::factory()->create();
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_update', $spot->id), [
            // name missing to invalidate request
            'description' => 'This is an updated spot',
            'visibility' => 'public',
            'image' => $image,
        ]);

        $this->assertDatabaseCount('spots', 1)
            ->assertDatabaseMissing('spots', [
                'name' => 'Updated Spot',
                'description' => 'This is an updated spot',
                'visibility' => 'public',
            ]);

        Storage::disk('public')->assertMissing('images/spots/' . $image->hashName());
    }

    /** @test */
    public function update_owner_premium_user_can_delete_spot_and_redirect()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_update', $spot->id), [
            'name' => 'Updated Spot',
            'description' => 'This is an updated spot',
            'visibility' => 'public',
            'delete' => true,
            'redirect' => route('spot_view', $spot->id),
        ]);

        $this->assertDatabaseCount('spots', 1)
            ->assertSoftDeleted($spot);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_delete', $spot->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_spot()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_delete', $spot->id));

        $this->assertDatabaseCount('spots', 1)
            ->assertSoftDeleted($spot);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_recover', $spot->id));

        $this->assertDatabaseCount('spots', 1)
            ->assertSoftDeleted($spot);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_spot()
    {
        $spot = Spot::factory()->create();
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_recover', $spot->id));

        $this->assertDatabaseCount('spots', 1)
            ->assertDatabaseHas('spots', [
                'name' => $spot->name,
                'description' => $spot->description,
                'coordinates' => $spot->coordinates,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_remove', $spot->id));

        $this->assertDatabaseCount('spots', 1)
            ->assertDatabaseHas('spots', [
                'name' => $spot->name,
                'description' => $spot->description,
                'coordinates' => $spot->coordinates,
            ]);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_spot()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_remove', $spot->id));

        $this->assertDatabaseCount('spots', 0);
    }

    /** @test */
    public function remove_random_premium_user_with_remove_content_permission_can_remove_spot()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $this->premiumUser->givePermissionTo($removeContent);
        $spot = Spot::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_remove', $spot->id));

        $this->assertDatabaseCount('spots', 0);
    }

    /** @test */
    public function search_non_logged_in_user_can_get_public_spots()
    {
        $spot = Spot::factory()->create(['visibility' => 'public']);

        $response = $this->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function search_non_premium_user_can_get_follower_spots_of_user_they_follow()
    {
        $user = User::factory()->create();
        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);

        $response = $this->actingAs($user)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function search_premium_user_can_not_get_follower_spots_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function search_non_premium_user_can_get_their_own_private_spots()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonFragment(['name' => $spot->name]);
    }

    /** @test */
    public function search_premium_user_can_not_get_private_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function search_premium_user_can_not_get_public_deleted_spots_of_different_user()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function search_premium_user_can_not_get_their_own_deleted_spots()
    {
        $spot = Spot::factory()->create();
        $spot->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_search', [
            'search' => $spot->name,
        ]));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function search_premium_user_can_not_get_spot_not_matching_search()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_search', [
            'search' => 'xretuip',
        ]));

        $response->assertOk()
            ->assertJsonMissing(['name' => $spot->name]);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_report', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_non_premium_user_can_report_visible_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('spot_report', $spot->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $spot->id,
                'reportable_type' => Spot::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function report_premium_user_can_not_report_invisible_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_report', $spot->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_non_logged_in_user_redirects_to_login()
    {
        $spot = Spot::factory()->create();

        $response = $this->get(route('spot_report_discard', $spot->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_reports_random_premium_user_can_not_discard_spot_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$spot->id, Spot::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_report_discard', $spot->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $spot->id,
                'reportable_type' => Spot::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_can_not_discard_spot_reports()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$spot->id, Spot::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_report_discard', $spot->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $spot->id,
                'reportable_type' => Spot::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_premium_user_with_manage_reports_permission_can_discard_spot_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$spot->id, Spot::class, $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_report_discard', $spot->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function add_movement_reports_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('spot_add_movement', 1), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function add_movement_non_premium_user_can_not_add_movement_to_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->post(route('spot_add_movement', $spot->id), [
            'movement' => $movement->id,
        ]);

        $this->assertDatabaseCount('spots_movements', 0);
    }

    /** @test */
    public function add_movement_premium_user_can_add_movement_to_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_add_movement', $spot->id), [
            'movement' => $movement->id,
        ]);

        $this->assertDatabaseCount('spots_movements', 1)
            ->assertDatabaseHas('spots_movements', [
                'movement_id' => $movement->id,
                'spot_id' => $spot->id,
            ]);
    }

    /** @test */
    public function remove_movement_reports_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_remove_movement', [1, 1]));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_movement_owner_non_premium_user_can_not_remove_movement_from_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id]);
        $spot->movements()->attach($movement->id, ['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('spot_remove_movement', [$spot->id, $movement->id]));

        $this->assertDatabaseCount('spots_movements', 1)
            ->assertDatabaseHas('spots_movements', [
                'movement_id' => $movement->id,
                'spot_id' => $spot->id,
            ]);
    }

    /** @test */
    public function remove_movement_owner_premium_user_can_remove_movement_from_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_remove_movement', [$spot->id, $movement->id]));

        $this->assertDatabaseCount('spots_movements', 0);
    }

    /** @test */
    public function remove_movement_random_premium_user_can_not_remove_movement_from_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id]);
        $spot->movements()->attach($movement->id, ['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_remove_movement', [$spot->id, $movement->id]));

        $this->assertDatabaseCount('spots_movements', 1)
            ->assertDatabaseHas('spots_movements', [
                'movement_id' => $movement->id,
                'spot_id' => $spot->id,
            ]);
    }

    /** @test */
    public function link_workout_reports_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('spot_workout_link'), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function link_workout_non_premium_user_can_not_link_workout_to_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout = Workout::factory()->create();

        $response = $this->actingAs($user)->post(route('spot_workout_link'), [
            'spot' => $spot->id,
            'workout' => $workout->id,
        ]);

        $this->assertDatabaseCount('spots_movements', 0);
    }

    /** @test */
    public function link_workout_premium_user_can_link_workout_to_spot()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('spot_workout_link'), [
            'spot' => $spot->id,
            'workout' => $workout->id,
        ]);

        $this->assertDatabaseCount('spots_workouts', 1)
            ->assertDatabaseHas('spots_workouts', [
                'workout_id' => $workout->id,
                'spot_id' => $spot->id,
            ]);
    }
}
