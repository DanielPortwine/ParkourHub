<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Hit;
use App\Models\Report;
use App\Models\Spot;
use App\Models\SpotView;
use App\Models\User;
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
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
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
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
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
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
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
            ->assertViewHas('content', function ($viewSpots) use ($spot) {
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
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $bestSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $worstSpot->id,
        ]);
        $oldestReport->save();

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
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $bestSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $worstSpot->id,
        ]);
        $oldestReport->save();

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
        $mostSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $mostSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $leastSpot->id,
        ]);
        $oldestReport->save();

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
        $mostSpotViews = SpotView::factory()->create(['spot_id' => $leastSpot->id]);
        $latestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $mostSpot->id,
        ]);
        $latestReport->save();
        $oldestReport = new Report([
            'user_id' => $this->adminUser->id,
            'reportable_type' => Spot::class,
            'reportable_id' => $leastSpot->id,
        ]);
        $oldestReport->save();

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
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
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
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
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
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
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
            ->assertViewHas('content', function ($viewChallenges) use ($challenge) {
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
            ->assertViewHas('content', function ($viewEntries) use ($entry) {
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
}
