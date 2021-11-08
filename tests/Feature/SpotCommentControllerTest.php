<?php

namespace Tests\Feature;

use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SpotCommentControllerTest extends TestCase
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
        $response = $this->post(route('spot_comment_store'), [
            'spot' => 1,
            'comment' => 'Test Comment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_can_store_valid_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($user)->post(route('spot_comment_store'), [
            'spot' => $spot->id,
            'comment' => 'This is a test comment.',
            'video_image' => $image,
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'This is a test comment.',
                'image' => '/storage/images/spot_comments/' . $image->hashName(),
            ]);

        Storage::disk('public')->assertExists('images/spot_comments/' . $image->hashName());
    }

    /** @test */
    public function store_premium_user_can_store_valid_comment()
    {
        $spot = Spot::factory()->create();
        $video = UploadedFile::fake()->create('video.mp4', 100);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_store'), [
            'spot' => $spot->id,
            'comment' => 'This is a test comment.',
            'video_image' => $video,
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'This is a test comment.',
                'video' => '/storage/videos/spot_comments/' . $video->hashName(),
                'video_type' => 'mp4',
            ]);

        Storage::disk('public')->assertExists('videos/spot_comments/' . $video->hashName());
    }

    /** @test */
    public function store_non_premium_user_can_not_store_invalid_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($user)->post(route('spot_comment_store'), [
            // spot missing to invalidate request
            'comment' => 'This is a test comment.',
            'video_image' => $image,
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 0);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function store_premium_user_can_not_store_invalid_comment()
    {
        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id]);
        $image = UploadedFile::fake()->image('image.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_store'), [
            // spot missing to invalidate request
            'comment' => 'This is a test comment.',
            'video_image' => $image,
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 0);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function edit_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_comment_edit', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function edit_random_non_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_edit', $comment->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function edit_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_edit', $comment->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function edit_owner_non_premium_user_can_edit_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id]);
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'comment' => 'Test Comment']);

        $response = $this->actingAs($user)->get(route('spot_comment_edit', $comment->id));

        $response->assertOk()
            ->assertViewIs('spots.comments.edit')
            ->assertViewHas('comment', function($pageComment) use ($comment) {
                $this->assertSame($comment->comment, $pageComment->comment);
                return true;
            });
    }

    /** @test */
    public function edit_owner_premium_user_can_edit_comment()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['comment' => 'Test Comment']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_edit', $comment->id));

        $response->assertOk()
            ->assertViewIs('spots.comments.edit')
            ->assertViewHas('comment', function($pageComment) use ($comment) {
                $this->assertSame($comment->comment, $pageComment->comment);
                return true;
            });
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('spot_comment_update', 1), [
            'spot' => 1,
            'comment' => 'Test Comment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_random_non_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'comment' => 'Test Comment', 'visibility' => 'private']);

        $response = $this->actingAs($user)->post(route('spot_comment_update', $comment->id), [
            'comment' => 'Updated Comment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'comment' => 'Test Comment', 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_update', $comment->id), [
            'comment' => 'Updated Comment',
            'visibility' => 'public',
        ]);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_owner_non_premium_user_can_update_comment_with_valid_data()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $user->id,
            'comment' => 'Test Comment',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->post(route('spot_comment_update', $comment->id), [
            'comment' => 'Updated Comment',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'Updated Comment',
                'visibility' => 'public',
            ])
            ->assertDatabaseMissing('spot_comments', [
                'comment' => 'Test Comment',
                'visibility' => 'private',
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_update_comment_with_valid_data()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $this->premiumUser->id,
            'comment' => 'Test Comment',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_update', $comment->id), [
            'comment' => 'Updated Comment',
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'Updated Comment',
                'visibility' => 'public',
            ])
            ->assertDatabaseMissing('spot_comments', [
                'comment' => 'Test Comment',
                'visibility' => 'private',
            ]);
    }

    /** @test */
    public function update_owner_non_premium_user_can_not_update_comment_with_invalid_data()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $user->id,
            'comment' => 'Test Comment',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->post(route('spot_comment_update', $comment->id), [
            // comment missing to invalidate request
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'Test Comment',
                'visibility' => 'private',
            ])
            ->assertDatabaseMissing('spot_comments', [
                'comment' => 'Updated Comment',
                'visibility' => 'public',
            ]);
    }

    /** @test */
    public function update_owner_premium_user_can_not_update_comment_with_invalid_data()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $this->premiumUser->id,
            'comment' => 'Test Comment',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_update', $comment->id), [
            // comment missing to invalidate request
            'visibility' => 'public',
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => 'Test Comment',
                'visibility' => 'private',
            ])
            ->assertDatabaseMissing('spot_comments', [
                'comment' => 'Updated Comment',
                'visibility' => 'public',
            ]);
    }

    /** @test */
    public function update_owner_non_premium_user_can_delete_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $user->id,
            'comment' => 'Test Comment',
        ]);

        $response = $this->actingAs($user)->post(route('spot_comment_update', $comment->id), [
            'comment' => $comment->comment,
            'visibility' => $comment->visibility,
            'delete' => true,
            'redirect' => route('spot_view', $spot->id),
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function update_owner_premium_user_can_delete_comment()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create([
            'user_id' => $this->premiumUser->id,
            'comment' => 'Test Comment',
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('spot_comment_update', $comment->id), [
            'comment' => $comment->comment,
            'visibility' => $comment->visibility,
            'delete' => true,
            'redirect' => route('spot_view', $spot->id),
        ]);

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_comment_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_random_non_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_delete', $comment->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_random_premium_user_redirects_to_view()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_delete', $comment->id));

        $response->assertRedirect(route('spot_view', $spot->id));
    }

    /** @test */
    public function delete_owner_non_premium_user_can_delete_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_delete', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);
    }

    /** @test */
    public function delete_owner_premium_user_can_delete_comment()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_delete', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_comment_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_random_non_premium_user_can_not_recover_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);
        $comment->delete();

        $response = $this->actingAs($user)->get(route('spot_comment_recover', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);
    }

    /** @test */
    public function recover_random_premium_user_can_not_recover_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);
        $comment->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_recover', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertSoftDeleted($comment);
    }

    /** @test */
    public function recover_owner_non_premium_user_can_recover_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);
        $comment->delete();

        $response = $this->actingAs($user)->get(route('spot_comment_recover', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => $comment->comment,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function recover_owner_premium_user_can_recover_comment()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);
        $comment->delete();

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_recover', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => $comment->comment,
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('spot_comment_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_random_non_premium_user_can_not_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => $comment->comment,
            ]);
    }

    /** @test */
    public function remove_random_premium_user_can_not_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 1)
            ->assertDatabaseHas('spot_comments', [
                'comment' => $comment->comment,
            ]);
    }

    /** @test */
    public function remove_owner_non_premium_user_can_remove_review()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 0);
    }

    /** @test */
    public function remove_owner_premium_user_can_remove_review()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 0);
    }

    /** @test */
    public function remove_random_non_premium_user_with_remove_content_permission_can_remove_private_comment()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $user->givePermissionTo($removeContent);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 0);
    }

    /** @test */
    public function remove_random_premium_user_with_remove_content_permission_can_remove_private_comment()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $this->premiumUser->givePermissionTo($removeContent);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_remove', $comment->id));

        $this->assertDatabaseCount('spot_comments', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create();

        $response = $this->get(route('spot_comment_report', $comment->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_non_premium_user_can_report_visible_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->get(route('spot_comment_report', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function report_premium_user_can_report_visible_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function report_non_premium_user_can_not_report_invisible_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);

        $response = $this->actingAs($user)->get(route('spot_comment_report', $comment->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function report_premium_user_can_not_report_invisible_comment()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report', $comment->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_non_logged_in_user_redirects_to_login()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create();

        $response = $this->get(route('spot_comment_report_discard', $comment->id));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_reports_random_non_premium_user_can_not_discard_comment_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_premium_user_can_not_discard_comment_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_non_premium_user_can_not_discard_comment_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_can_not_discard_comment_reports()
    {
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }

    /** @test */
    public function discard_reports_random_non_premium_user_with_manage_reports_permission_can_discard_comment_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $user->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_random_premium_user_with_manage_reports_permission_can_discard_comment_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_owner_non_premium_user_with_manage_reports_permission_can_not_discard_comment_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $user->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $user->id]);

        $response = $this->actingAs($user)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_reports_owner_premium_user_with_manage_reports_permission_can_not_discard_comment_reports()
    {
        $manageReports = Permission::create(['name' => 'manage reports']);
        $this->premiumUser->givePermissionTo($manageReports);
        $spot = Spot::factory()->create();
        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$comment->id, SpotComment::class, $this->premiumUser->id]);

        $response = $this->actingAs($this->premiumUser)->get(route('spot_comment_report_discard', $comment->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $comment->id,
                'reportable_type' => SpotComment::class,
                'user_id' => $this->premiumUser->id,
            ]);
    }
}
