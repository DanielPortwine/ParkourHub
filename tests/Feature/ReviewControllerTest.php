<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
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
    public function store_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('review_store'), [
            'spot' => 1,
            'rating' => 5,
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_can_store_valid_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('review_store'), [
            'spot' => $spot->id,
            'rating' => 4,
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '4',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
            ])
            ->assertDatabaseHas('spots', [
                'rating' => '4',
            ]);
    }

    /** @test */
    public function store_premium_user_can_store_valid_review()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('review_store'), [
            'spot' => $spot->id,
            'rating' => 4,
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '4',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
            ])
            ->assertDatabaseHas('spots', [
                'rating' => '4',
            ]);
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_review()
    {
        $spot = Spot::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('review_store'), [
            'spot' => $spot->id,
            // rating missing to invalidate request
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 0);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('review_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_random_non_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('review_edit', $review->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_edit', $review->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function edit_owner_non_premium_user_can_edit_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('review_edit', $review->id));

        $response->assertOk()
            ->assertViewIs('reviews.edit')
            ->assertViewHas('review', function($pageReview) use ($review) {
                $this->assertSame($review->title, $pageReview->title);
                $this->assertSame($review->review, $pageReview->review);
                return true;
            });
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_review()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_edit', $review->id));

        $response->assertOk()
            ->assertViewIs('reviews.edit')
            ->assertViewHas('review', function($pageReview) use ($review) {
                $this->assertSame($review->title, $pageReview->title);
                $this->assertSame($review->review, $pageReview->review);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('review_update', 1), [
            'spot' => 1,
            'rating' => 4,
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'rating' => '3', 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('review_update', $review->id), [
            'rating' => 4,
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'public',
        ]);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_owner_non_premium_user_can_update_review_with_valid_data()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => '3',
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->post(route('review_update', $review->id), [
            'rating' => 4,
            'title' => 'Updated Review',
            'review' => 'This is an updated review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '4',
                'title' => 'Updated Review',
                'review' => 'This is an updated review.',
                'visibility' => 'public',
            ])
            ->assertDatabaseMissing('reviews', [
                'rating' => '3',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
                'visibility' => 'private',
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_update_review_with_valid_data()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $this->premiumUser->id,
            'rating' => '3',
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('review_update', $review->id), [
            'rating' => 4,
            'title' => 'Updated Review',
            'review' => 'This is an updated review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '4',
                'title' => 'Updated Review',
                'review' => 'This is an updated review.',
                'visibility' => 'public',
            ])
            ->assertDatabaseMissing('reviews', [
                'rating' => '3',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
                'visibility' => 'private',
            ]);
    }

    /** @test */
    public function update_owner_non_premium_user_can_not_update_review_with_invalid_data()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => '3',
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->post(route('review_update', $review->id), [
            // rating missing to invalidate request
            'title' => 'Updated Review',
            'review' => 'This is an updated review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '3',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
                'visibility' => 'private',
            ])
            ->assertDatabaseMissing('reviews', [
                'rating' => '4',
                'title' => 'Updated Review',
                'review' => 'This is an updated review.',
                'visibility' => 'public',
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_review_with_invalid_data()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $this->premiumUser->id,
            'rating' => '3',
            'title' => 'Test Review',
            'review' => 'This is a test review.',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('review_update', $review->id), [
            // rating missing to invalidate request
            'title' => 'Updated Review',
            'review' => 'This is an updated review.',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'rating' => '3',
                'title' => 'Test Review',
                'review' => 'This is a test review.',
                'visibility' => 'private',
            ])
            ->assertDatabaseMissing('reviews', [
                'rating' => '4',
                'title' => 'Updated Review',
                'review' => 'This is an updated review.',
                'visibility' => 'public',
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function update_owner_non_premium_user_can_delete_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('review_update', $review->id), [
            'rating' => (int)$review->rating,
            'title' => $review->title,
            'review' => $review->review,
            'visibility' => $review->visibility,
            'delete' => true,
            'redirect' => route('spot_view', $spot->id),
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_owner_premium_user_can_delete_review()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('review_update', $review->id), [
            'rating' => (int)$review->rating,
            'title' => $review->title,
            'review' => $review->review,
            'visibility' => $review->visibility,
            'delete' => true,
            'redirect' => route('spot_view', $spot->id),
        ]);

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('review_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_random_non_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('review_delete', $review->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_delete', $review->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_owner_non_premium_user_can_delete_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('review_delete', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_review()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_delete', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('review_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_random_non_premium_user_can_not_recover_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);
        $review->delete();

        $response = $this->actingAs($user)->get(route('review_recover', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $review->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('review_recover', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertSoftDeleted($review);
    }

    /** @test */
    public function recover_owner_non_premium_user_can_recover_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $review->delete();

        $response = $this->actingAs($user)->get(route('review_recover', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'title' => $review->title,
                'review' => $review->review,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_review()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);
        $review->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('review_recover', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'title' => $review->title,
                'review' => $review->review,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('review_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_random_non_premium_user_can_not_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'title' => $review->title,
                'review' => $review->review,
            ]);
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 1)
            ->assertDatabaseHas('reviews', [
                'title' => $review->title,
                'review' => $review->review,
            ]);
    }

    /** @test */
    public function remove_owner_non_premium_user_can_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_review()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function remove_random_non_premium_user_with_remove_content_permission_can_remove_private_review()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $user->givePermissionTo($removeContent);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function remove_random_premium_user_with_remove_content_permission_can_remove_private_review()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $this->premiumUser->givePermissionTo($removeContent);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('review_remove', $review->id));

        $this->assertDatabaseCount('reviews', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create();

        $response = $this->get(route('review_report', $review->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_non_premium_user_can_report_visible_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('review_report', $review->id));

        $this->assertDatabaseCount('reports', 1)
        ->assertDatabaseHas('reports', [
            'reportable_id' => $review->id,
            'reportable_type' => Review::class,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function report_premium_user_can_report_visible_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function report_non_premium_user_can_not_report_invisible_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('review_report', $review->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function report_premium_user_can_not_report_invisible_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report', $review->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_non_logged_in_user_redirects_to_login()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create();

        $response = $this->get(route('review_report_discard', $review->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_reports_random_non_premium_user_can_not_discard_review_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $user->id]);

        $response = $this->actingAs($user)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_premium_user_can_not_discard_review_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_non_premium_user_can_not_discard_review_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $user->id]);

        $response = $this->actingAs($user)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_can_not_discard_review_reports()
    {
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_non_premium_user_with_manage_reports_permission_can_discard_review_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $user->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $user->id]);

        $response = $this->actingAs($user)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_random_premium_user_with_manage_reports_permission_can_discard_review_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_owner_non_premium_user_with_manage_reports_permission_can_not_discard_review_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $user->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $user->id]);

        $response = $this->actingAs($user)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_with_manage_reports_permission_can_not_discard_review_reports()
    {
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$review->id, Review::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('review_report_discard', $review->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $review->id,
                'reportable_type' => Review::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }
}
