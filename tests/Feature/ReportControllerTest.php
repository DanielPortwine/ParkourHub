<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Equipment;
use App\Models\Hit;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\Report;
use App\Models\Review;
use App\Models\Spot;
use App\Models\Comment;
use App\Models\SpotView;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $manageReports;
    protected $adminUser;
    protected $accessPremium;

    public function setUp(): void
    {
        parent::setUp();

        $this->manageReports = Permission::create(['name' => 'manage reports']);
        $this->adminUser = User::factory()->create()->givePermissionTo($this->manageReports);
        $this->accessPremium = Permission::create(['name' => 'access premium']);
    }

    /** @test */
    public function listing_non_logged_in_user_can_not_view_page()
    {
        $response = $this->get(route('report_listing'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function listing_non_premium_user_can_not_view_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('report_listing'));

        $response->assertNotFound();
    }

    /** @test */
    public function listing_premium_user_can_not_view_page()
    {
        $user = User::factory()->create()->givePermissionTo($this->accessPremium);

        $response = $this->actingAs($user)->get(route('report_listing'));

        $response->assertNotFound();
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_spots()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_spots()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $spot->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_spots_on_hitlist()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $hit = new Hit([
            'user_id' => $this->adminUser->id,
            'spot_id' => $spot->id,
        ]);
        $hit->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['on_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_spots_not_on_hitlist()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['on_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) {
                $this->assertCount(0, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_spots_ticked_off_hitlist()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $hit = new Hit([
            'user_id' => $this->adminUser->id,
            'spot_id' => $spot->id,
            'completed_at' => now(),
        ]);
        $hit->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['ticked_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_spots_not_ticked_off_hitlist()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $hit = new Hit([
            'user_id' => $this->adminUser->id,
            'spot_id' => $spot->id,
        ]);
        $hit->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['ticked_hitlist' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) {
                $this->assertCount(0, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_spots_with_rating()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'rating' => '3']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['rating' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_spots_with_different_rating()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'rating' => '3']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['rating' => '4']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) {
                $this->assertCount(0, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_spots_between_two_dates()
    {
        $spot = Spot::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
                $this->assertCount(1, $viewSpots);
                $this->assertSame($spot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_spots_outside_two_dates()
    {
        $spot = Spot::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $spot->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) {
                $this->assertCount(0, $viewSpots);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_spots_first()
    {
        $latestSpot = Spot::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestSpot = Spot::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $latestSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $oldestSpot->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($latestSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($latestSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_spots_first()
    {
        $latestSpot = Spot::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestSpot = Spot::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $latestSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $oldestSpot->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($oldestSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($oldestSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_highest_rated_reported_spots_first()
    {
        $bestSpot = Spot::factory()->create(['rating' => '4']);
        $worstSpot = Spot::factory()->create(['rating' => '2']);
        $bestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $bestSpot->id,
        ]);
        $bestReport->save();
        $worstReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $worstSpot->id,
        ]);
        $worstReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['sort' => 'rating_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($bestSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($bestSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_lowest_rated_reported_spots_first()
    {
        $bestSpot = Spot::factory()->create(['rating' => '4']);
        $worstSpot = Spot::factory()->create(['rating' => '2']);
        $bestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $bestSpot->id,
        ]);
        $bestReport->save();
        $worstReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $worstSpot->id,
        ]);
        $worstReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['sort' => 'rating_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($worstSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($worstSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_most_viewed_reported_spots_first()
    {
        $mostSpot = Spot::factory()->create();
        $leastSpot = Spot::factory()->create();
        $mostSpotViews = SpotView::factory()->times(2)->create(['spot_id' => $mostSpot->id]);
        $leastSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);
        $mostReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $mostSpot->id,
        ]);
        $mostReport->save();
        $leastReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $leastSpot->id,
        ]);
        $leastReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['sort' => 'views_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($mostSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($mostSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_least_viewed_reported_spots_first()
    {
        $mostSpot = Spot::factory()->create();
        $leastSpot = Spot::factory()->create();
        $mostSpotViews = SpotView::factory()->times(2)->create(['spot_id' => $mostSpot->id]);
        $leastSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);
        $mostReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $mostSpot->id,
        ]);
        $mostReport->save();
        $leastReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $leastSpot->id,
        ]);
        $leastReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['sort' => 'views_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewSpots) use ($leastSpot) {
                $this->assertCount(2, $viewSpots);
                $this->assertSame($leastSpot->id, $viewSpots->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', 'challenge'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_challenges()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challenge->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', 'challenge'));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_challenges_they_have_entered()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $entry = ChallengeEntry::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'entered' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_challenges_they_have_not_entered()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'entered' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) {
                $this->assertCount(0, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_challenges_with_difficulty()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['difficulty' => '3']);
        $entry = ChallengeEntry::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'difficulty' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_challenges_with_different_difficulty()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['difficulty' => '3']);
        $entry = ChallengeEntry::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'difficulty' => '4']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) {
                $this->assertCount(0, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_challenges_between_two_dates()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_challenges_outside_two_dates()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) {
                $this->assertCount(0, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_challenges_of_user_they_follow()
    {
        $user = User::factory()->create();
        $user->followers()->attach($this->adminUser->id, ['accepted' => true]);
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['user_id' => $user->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'following' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
                $this->assertCount(1, $viewChallenges);
                $this->assertSame($challenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_challenges_of_user_they_do_not_follow()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['user_id' => $user->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $challenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'following' => true]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) {
                $this->assertCount(0, $viewChallenges);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_challenges_first()
    {
        $spot = Spot::factory()->create();
        $latestChallenge = Challenge::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestChallenge = Challenge::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $latestChallenge->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $oldestChallenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($latestChallenge) {
                $this->assertCount(2, $viewChallenges);
                $this->assertSame($latestChallenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_challenges_first()
    {
        $spot = Spot::factory()->create();
        $latestChallenge = Challenge::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestChallenge = Challenge::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $latestChallenge->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $oldestChallenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($oldestChallenge) {
                $this->assertCount(2, $viewChallenges);
                $this->assertSame($oldestChallenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_most_entered_reported_challenges_first()
    {
        $spot = Spot::factory()->create();
        $mostChallenge = Challenge::factory()->create();
        $leastChallenge = Challenge::factory()->create();
        $mostEntries = ChallengeEntry::factory()->times(2)->create(['challenge_id' => $mostChallenge->id]);
        $leastEntry = ChallengeEntry::factory()->create(['challenge_id' => $leastChallenge->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $mostChallenge->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $leastChallenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'sort' => 'entries_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($mostChallenge) {
                $this->assertCount(2, $viewChallenges);
                $this->assertSame($mostChallenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_least_entered_reported_challenges_first()
    {
        $spot = Spot::factory()->create();
        $mostChallenge = Challenge::factory()->create();
        $leastChallenge = Challenge::factory()->create();
        $mostEntries = ChallengeEntry::factory()->times(2)->create(['challenge_id' => $mostChallenge->id]);
        $leastEntry = ChallengeEntry::factory()->create(['challenge_id' => $leastChallenge->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $mostChallenge->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Challenge::class,
            'reportable_id' => $leastChallenge->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['challenge', 'sort' => 'entries_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewChallenges) use ($leastChallenge) {
                $this->assertCount(2, $viewChallenges);
                $this->assertSame($leastChallenge->id, $viewChallenges->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_entries()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $entry = ChallengeEntry::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $entry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) use ($entry) {
                $this->assertCount(1, $viewEntries);
                $this->assertSame($entry->id, $viewEntries->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_winning_entries()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create(['won' => true]);
        $entry = ChallengeEntry::factory()->create(['winner' => true]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $entry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) use ($entry) {
                $this->assertCount(1, $viewEntries);
                $this->assertSame($entry->id, $viewEntries->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_entries_between_two_dates()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $entry = ChallengeEntry::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $entry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) use ($entry) {
                $this->assertCount(1, $viewEntries);
                $this->assertSame($entry->id, $viewEntries->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_entries_outside_two_dates()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $entry = ChallengeEntry::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $entry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) {
                $this->assertCount(0, $viewEntries);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_entries_first()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $latestEntry = ChallengeEntry::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestEntry = ChallengeEntry::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $latestEntry->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $oldestEntry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) use ($latestEntry) {
                $this->assertCount(2, $viewEntries);
                $this->assertSame($latestEntry->id, $viewEntries->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_entries_first()
    {
        $spot = Spot::factory()->create();
        $challenge = Challenge::factory()->create();
        $latestEntry = ChallengeEntry::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestEntry = ChallengeEntry::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $latestEntry->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => ChallengeEntry::class,
            'reportable_id' => $oldestEntry->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['entry', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEntries) use ($oldestEntry) {
                $this->assertCount(2, $viewEntries);
                $this->assertSame($oldestEntry->id, $viewEntries->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_reviews()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($review) {
                $this->assertCount(1, $viewReviews);
                $this->assertSame($review->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_reviews()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $review->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($review) {
                $this->assertCount(1, $viewReviews);
                $this->assertSame($review->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_reviews_with_rating()
    {
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['rating' => '3']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'rating' => '3']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($review) {
                $this->assertCount(1, $viewReviews);
                $this->assertSame($review->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_reviews_with_different_rating()
    {
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['rating' => '3']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'rating' => '4']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) {
                $this->assertCount(0, $viewReviews);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_reviews_between_two_dates()
    {
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($review) {
                $this->assertCount(1, $viewReviews);
                $this->assertSame($review->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_reviews_outside_two_dates()
    {
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $review = Review::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $review->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) {
                $this->assertCount(0, $viewReviews);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_reviews_first()
    {
        $spot = Spot::factory()->create();
        $latestReview = Review::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestReview = Review::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $latestReview->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $oldestReview->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($latestReview) {
                $this->assertCount(2, $viewReviews);
                $this->assertSame($latestReview->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_reviews_first()
    {
        $spot = Spot::factory()->create();
        $latestReview = Review::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestReview = Review::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $latestReview->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $oldestReview->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($oldestReview) {
                $this->assertCount(2, $viewReviews);
                $this->assertSame($oldestReview->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_best_reported_reviews_first()
    {
        $spot = Spot::factory()->create();
        $bestReview = Review::factory()->create(['rating' => '4']);
        $worstReview = Review::factory()->create(['rating' => '2']);
        $bestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $bestReview->id,
        ]);
        $bestReport->save();
        $worstReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $worstReview->id,
        ]);
        $worstReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'sort' => 'rating_desc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($bestReview) {
                $this->assertCount(2, $viewReviews);
                $this->assertSame($bestReview->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_worst_reported_reviews_first()
    {
        $spot = Spot::factory()->create();
        $bestReview = Review::factory()->create(['rating' => '4']);
        $worstReview = Review::factory()->create(['rating' => '2']);
        $bestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $bestReview->id,
        ]);
        $bestReport->save();
        $worstReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Review::class,
            'reportable_id' => $worstReview->id,
        ]);
        $worstReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['review', 'sort' => 'rating_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewReviews) use ($worstReview) {
                $this->assertCount(2, $viewReviews);
                $this->assertSame($worstReview->id, $viewReviews->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_comments()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) use ($comment) {
                $this->assertCount(1, $viewComments);
                $this->assertSame($comment->id, $viewComments->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_comments()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $this->adminUser->id]);
        $comment = Comment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $comment->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) use ($comment) {
                $this->assertCount(1, $viewComments);
                $this->assertSame($comment->id, $viewComments->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_comments_between_two_dates()
    {
        $spot = Spot::factory()->create();
        $comment = Comment::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) use ($comment) {
                $this->assertCount(1, $viewComments);
                $this->assertSame($comment->id, $viewComments->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_comments_outside_two_dates()
    {
        $spot = Spot::factory()->create();
        $comment = Comment::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $comment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) {
                $this->assertCount(0, $viewComments);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_comments_first()
    {
        $spot = Spot::factory()->create();
        $latestComment = Comment::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestComment = Comment::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $latestComment->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $oldestComment->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) use ($latestComment) {
                $this->assertCount(2, $viewComments);
                $this->assertSame($latestComment->id, $viewComments->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_comments_first()
    {
        $spot = Spot::factory()->create();
        $latestComment = Comment::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestComment = Comment::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $latestComment->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Comment::class,
            'reportable_id' => $oldestComment->id,
        ]);
        $oldestReport->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['comment', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewComments) use ($oldestComment) {
                $this->assertCount(2, $viewComments);
                $this->assertSame($oldestComment->id, $viewComments->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_movements()
    {
        $user = User::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_movements()
    {
        $user = User::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $movement->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_movements_between_two_dates()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_movements_outside_two_dates()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_movements_of_type()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'movementType' => $movementType->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_movements_of_different_type()
    {
        $movementType = MovementType::factory()->create();
        $movementType1 = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['type_id' => $movementType->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'movementType' => $movementType1->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_movements_in_category()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'category' => $movementCategory->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_movements_in_different_category()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movementCategory1 = MovementCategory::factory()->create();
        $movement = Movement::factory()->create(['category_id' => $movementCategory->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'category' => $movementCategory1->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_movements_with_equipment()
    {
        $equipment = Equipment::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $movement->equipment()->attach($equipment->id, ['user_id' => $this->adminUser->id]);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'equipment' => $equipment->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($movement) {
                $this->assertCount(1, $viewMovements);
                $this->assertSame($movement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_movements_without_equipment()
    {
        $equipment = Equipment::factory()->create();
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $movement = Movement::factory()->create();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $movement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'equipment' => $equipment->id]));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) {
                $this->assertCount(0, $viewMovements);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_movements_first()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $latestMovement = Movement::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestMovement = Movement::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $latestMovement->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $oldestMovement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($latestMovement) {
                $this->assertCount(2, $viewMovements);
                $this->assertSame($latestMovement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_movements_first()
    {
        $movementType = MovementType::factory()->create();
        $movementCategory = MovementCategory::factory()->create();
        $latestMovement = Movement::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestMovement = Movement::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $latestMovement->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Movement::class,
            'reportable_id' => $oldestMovement->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['movement', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewMovements) use ($oldestMovement) {
                $this->assertCount(2, $viewMovements);
                $this->assertSame($oldestMovement->id, $viewMovements->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_equipment()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $equipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_equipment()
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $equipment->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $equipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_equipment_between_two_dates()
    {
        $equipment = Equipment::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $equipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($equipment) {
                $this->assertCount(1, $viewEquipment);
                $this->assertSame($equipment->id, $viewEquipment->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_equipment_outside_two_dates()
    {
        $equipment = Equipment::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $equipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) {
                $this->assertCount(0, $viewEquipment);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_equipment_first()
    {
        $latestEquipment = Equipment::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestEquipment = Equipment::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $latestEquipment->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $oldestEquipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($latestEquipment) {
                $this->assertCount(2, $viewEquipment);
                $this->assertSame($latestEquipment->id, $viewEquipment->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_equipment_first()
    {
        $latestEquipment = Equipment::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestEquipment = Equipment::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $latestEquipment->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Equipment::class,
            'reportable_id' => $oldestEquipment->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['equipment', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewEquipment) use ($oldestEquipment) {
                $this->assertCount(2, $viewEquipment);
                $this->assertSame($oldestEquipment->id, $viewEquipment->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_reported_workouts()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $workout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) use ($workout) {
                $this->assertCount(1, $viewWorkouts);
                $this->assertSame($workout->id, $viewWorkouts->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_private_deleted_reported_workouts()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $workout->delete();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $workout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) use ($workout) {
                $this->assertCount(1, $viewWorkouts);
                $this->assertSame($workout->id, $viewWorkouts->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_reported_workouts_between_two_dates()
    {
        $workout = Workout::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $workout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout', 'date_from' => '2021-05-31', 'date_to' => '2021-06-02']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) use ($workout) {
                $this->assertCount(1, $viewWorkouts);
                $this->assertSame($workout->id, $viewWorkouts->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_not_view_reported_workouts_outside_two_dates()
    {
        $workout = Workout::factory()->create(['created_at' => '2021-06-01 21:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $workout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout', 'date_from' => '2021-05-01', 'date_to' => '2021-05-03']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) {
                $this->assertCount(0, $viewWorkouts);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_latest_reported_workouts_first()
    {
        $latestWorkout = Workout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestWorkout = Workout::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $latestWorkout->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $oldestWorkout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) use ($latestWorkout) {
                $this->assertCount(2, $viewWorkouts);
                $this->assertSame($latestWorkout->id, $viewWorkouts->first()->id);
                return true;
            });
    }

    /** @test */
    public function listing_manage_reports_user_can_view_oldest_reported_workouts_first()
    {
        $latestWorkout = Workout::factory()->create(['created_at' => '2021-05-31 19:30:00']);
        $oldestWorkout = Workout::factory()->create(['created_at' => '2021-04-30 19:30:00']);
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $latestWorkout->id,
        ]);
        $report->save();
        $report = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Workout::class,
            'reportable_id' => $oldestWorkout->id,
        ]);
        $report->save();

        $response = $this->actingAs($this->adminUser)->get(route('report_listing', ['workout', 'sort' => 'date_asc']));

        $response->assertOk()
            ->assertViewIs('content_listings')
            ->assertViewHas('content', function ($viewWorkouts) use ($oldestWorkout) {
                $this->assertCount(2, $viewWorkouts);
                $this->assertSame($oldestWorkout->id, $viewWorkouts->first()->id);
                return true;
            });
    }
}
