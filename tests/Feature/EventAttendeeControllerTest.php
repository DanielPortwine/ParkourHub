<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EventAttendeeControllerTest extends TestCase
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
        $response = $this->post(route('event_attendee_store'), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function store_non_premium_user_can_store_accepted_event_attendee()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'none']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('event_attendee_store'), [
            'event' => $event->id,
            'user' => $user->id,
        ]);

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => true,
            ]);
    }

    /** @test */
    public function store_premium_user_can_store_applicant_event_attendee()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            'event' => $event->id,
            'user' => $user->id,
            'comment' => 'Test Comment',
        ]);

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => false,
                'comment' => 'Test Comment',
            ]);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_attendee_without_event()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            // event missing
            'user' => $user->id,
            'comment' => 'Test Comment',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_attendee_with_invalid_event()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            'event' => 987,
            'user' => $user->id,
            'comment' => 'Test Comment',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_attendee_without_user()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            'event' => $event->id,
            // user missing
            'comment' => 'Test Comment',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_attendee_with_invalid_user()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            'event' => $event->id,
            'user' => 987,
            'comment' => 'Test Comment',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function store_premium_user_can_not_store_event_attendee_with_array_comment()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_store'), [
            'event' => $event->id,
            'user' => $user->id,
            'comment' => ['Test Comment'],
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('event_attendee_update', 1), []);

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function update_non_premium_user_can_accept_invite()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'invite']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($user)->post(route('event_attendee_update', $event->id), [
            'user' => $user->id,
            'accepted' => 'true',
        ]);

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => true,
            ]);
    }

    /** @test */
    public function update_premium_user_can_accept_event_attendee_request()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_update', $event->id), [
            'user' => $user->id,
            'accepted' => 'true',
        ]);

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => true,
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_accept_event_attendee_request_without_user()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_update', $event->id), [
            // user missing
            'accepted' => 'true',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => false,
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_accept_event_attendee_request_with_invalid_user()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_update', $event->id), [
            'user' => 987,
            'accepted' => 'true',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => false,
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_accept_event_attendee_request_with_invalid_accepted()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_update', $event->id), [
            'user' => $user->id,
            'accepted' => 'invalid',
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => false,
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_accept_event_attendee_request_with_array_comment()
    {
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept']);
        $user = User::factory()->create();
        $event->attendees()->attach($user, ['accepted' => false]);

        $response = $this->actingAs($this->premiumUser)->post(route('event_attendee_update', $event->id), [
            'user' => $user->id,
            'accepted' => 'true',
            'comment' => ['This is a comment'],
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'accepted' => false,
            ]);
    }

    /** @test */
    public function delete_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('event_attendee_delete', [1, 1]));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function delete_random_premium_user_can_not_delete_event_attendee()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept', 'user_id' => $user->id]);
        $event->attendees()->attach($user, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_attendee_delete', [$event->id, $user->id]));

        $this->assertDatabaseCount('events_attendees', 1)
            ->assertDatabaseHas('events_attendees', [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ]);
    }

    /** @test */
    public function delete_event_owner_premium_user_can_delete_event_attendee()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept', 'user_id' => $this->premiumUser->id]);
        $event->attendees()->attach($user, ['accepted' => true]);

        $response = $this->actingAs($this->premiumUser)->get(route('event_attendee_delete', [$event->id, $user->id]));

        $this->assertDatabaseCount('events_attendees', 0);
    }

    /** @test */
    public function delete_attendee_non_premium_user_can_delete_event_attendee()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['visibility' => 'public', 'accept_method' => 'accept', 'user_id' => $this->premiumUser->id]);
        $event->attendees()->attach($user, ['accepted' => true]);

        $response = $this->actingAs($user)->get(route('event_attendee_delete', [$event->id, $user->id]));

        $this->assertDatabaseCount('events_attendees', 0);
    }
}
