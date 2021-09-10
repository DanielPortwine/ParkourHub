<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Spot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ChallengeEntryControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'access premium']);
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
    public function store_user_can_store_valid_challenge_entry()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->post(route('entry_store'), [
            'challenge' => $challenge->id,
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
        ]);

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
            ]);
    }

    /** @test */
    public function store_user_can_not_store_invalid_challenge_entry()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);

        $response = $this->actingAs($user)->post(route('entry_store'), [
            // challenge missing to invalidate request
            'youtube' => 'https://youtu.be/Oykjn35X3EY',
        ]);

        $this->assertDatabaseCount('challenge_entries', 0);
    }

    /** @test */
    public function store_user_can_not_store_challenge_entry_twice()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create();

        $response = $this->actingAs($user)->post(route('entry_store'), [
            'challenge' => $challenge->id,
            'youtube' => 'https://www.youtube.com/watch?v=8vfBYE5WQSk',
        ]);

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
            ])
            ->assertDatabaseMissing('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => '8vfBYE5WQSk',
            ]);
    }

    /** @test */
    public function win_challenge_owner_can_crown_winning_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user)->get(route('entry_win', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
                'winner' => true,
            ])
            ->assertDatabaseHas('challenges', [
                'id' => $challenge->id,
                'won' => true,
            ]);
    }

    /** @test */
    public function win_random_user_can_not_crown_winning_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user1)->get(route('entry_win', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
            ])
            ->assertDatabaseMissing('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
                'winner' => true,
            ])
            ->assertDatabaseMissing('challenges', [
                'id' => $challenge->id,
                'won' => true,
            ]);
    }

    /** @test */
    public function win_owner_can_not_crown_winning_entry_if_challenge_already_won()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public', 'won' => true]);
        $winningChallengeEntry = ChallengeEntry::factory()->create(['user_id' => $user1->id, 'winner' => true]);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('entry_win', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 2)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'youtube' => 'Oykjn35X3EY',
                'winner' => false,
            ])
            ->assertDatabaseMissing('challenge_entries', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'youtube' => 'Oykjn35X3EY',
                'winner' => true,
            ])
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'user_id' => $user1->id,
                'youtube' => 'Oykjn35X3EY',
                'winner' => true,
            ]);
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_view()
    {
        $response = $this->get(route('entry_delete', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_random_user_can_not_delete_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user1)->get(route('entry_delete', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
            ]);
    }

    /** @test */
    public function delete_owner_can_delete_entry()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('entry_delete', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertSoftDeleted($challengeEntry);
    }

    /** @test */
    public function recover_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('entry_recover', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function recover_random_user_can_not_recover_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id, 'deleted_at' => now()]);

        $response = $this->actingAs($user1)->get(route('entry_recover', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertSoftDeleted($challengeEntry);
    }

    /** @test */
    public function recover_owner_can_recover_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id, 'deleted_at' => now()]);

        $response = $this->actingAs($user)->get(route('entry_recover', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('entry_remove', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_random_user_can_not_remove_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user1)->get(route('entry_remove', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 1)
            ->assertDatabaseHas('challenge_entries', [
                'challenge_id' => $challenge->id,
                'youtube' => 'Oykjn35X3EY',
                'deleted_at' => null,
            ]);
    }

    /** @test */
    public function remove_owner_can_remove_entry()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('entry_remove', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 0);
    }

    /** @test */
    public function remove_random_user_with_remove_content_permission_can_remove_entry()
    {
        $user = User::factory()->create();
        $removeContent = Permission::create(['name' => 'remove content']);
        $user1 = User::factory()->create()->givePermissionTo($removeContent);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user1)->get(route('entry_remove', $challengeEntry->id));

        $this->assertDatabaseCount('challenge_entries', 0);
    }

    /** @test */
    public function report_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('entry_report', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function report_user_can_report_visible_entry()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('entry_report', $challengeEntry->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $challengeEntry->id,
                'reportable_type' => ChallengeEntry::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function report_user_can_not_report_invisible_entry()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user1)->get(route('entry_report', $challengeEntry->id));

        $this->assertDatabaseCount('reports', 0);
    }

    /** @test */
    public function discard_reports_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('entry_report_discard', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function discard_reports_random_user_can_not_discard_entry_reports()
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$challengeEntry->id, ChallengeEntry::class, $user1->id]);

        $response = $this->actingAs($user1)->get(route('entry_report_discard', $challengeEntry->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $challengeEntry->id,
                'reportable_type' => ChallengeEntry::class,
                'user_id' => $user1->id,
            ]);
    }

    /** @test */
    public function discard_owner_can_not_discard_entry_reports()
    {
        $user = User::factory()->create();
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$challengeEntry->id, ChallengeEntry::class, $user->id]);

        $response = $this->actingAs($user)->get(route('entry_report_discard', $challengeEntry->id));

        $this->assertDatabaseCount('reports', 1)
            ->assertDatabaseHas('reports', [
                'reportable_id' => $challengeEntry->id,
                'reportable_type' => ChallengeEntry::class,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function discard_random_user_with_manage_reports_permission_can_discard_entry_reports()
    {
        $user = User::factory()->create();
        $manageReports = Permission::create(['name' => 'manage reports']);
        $user1 = User::factory()->create()->givePermissionTo($manageReports);
        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $challengeEntry = ChallengeEntry::factory()->create(['user_id' => $user->id]);
        DB::insert('INSERT INTO reports (reportable_id, reportable_type, user_id) VALUES (?, ?, ?)', [$challengeEntry->id, ChallengeEntry::class, $user->id]);

        $response = $this->actingAs($user1)->get(route('entry_report_discard', $challengeEntry->id));

        $this->assertDatabaseCount('reports', 0);
    }
}
