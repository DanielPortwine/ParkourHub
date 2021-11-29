<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WorkoutPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $accessPremium;
    protected $premiumUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->accessPremium = Permission::create(['name' => 'access premium']);
        $this->premiumUser = User::factory()->create();
        $this->premiumUser->givePermissionTo($this->accessPremium);
    }

    /** @test */
    public function index_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_plan'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function index_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_plan'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function index_premium_user_can_view_their_own_plan_workouts_for_today()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id]);
        $workout1 = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
        $this->premiumUser->planWorkouts()->attach($workout->id, ['date' => now()]);
        $user->planWorkouts()->attach($workout1->id, ['date' => now()]);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_plan'));

        $response->assertOk()
            ->assertViewIs('workouts.plan.index')
            ->assertViewHas('weeks', function ($viewWeeks) use ($workout) {
                $carbonDayDate = Carbon::now();
                $viewWorkouts = $viewWeeks[$carbonDayDate->weekNumberInMonth][$carbonDayDate->dayOfWeek]['workouts'];
                $this->assertCount(1, $viewWorkouts);
                $this->assertSame($workout->id, $viewWorkouts[0]->id);
                $this->assertSame($workout->name, $viewWorkouts[0]->name);
                return true;
            })
            ->assertViewHas('addableWorkouts', function ($viewAddableWorkouts) {
                $this->assertCount(2, $viewAddableWorkouts);
                return true;
            });
    }

    /** @test */
    public function index_premium_user_can_view_their_own_plan_workouts_for_a_different_month()
    {
        $workout = Workout::factory()->create();
        $this->premiumUser->planWorkouts()->attach($workout->id, ['date' => '2021-10-29 21:30:00']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_plan', ['month' => '2021-10']));

        $response->assertOk()
            ->assertViewIs('workouts.plan.index')
            ->assertViewHas('weeks', function ($viewWeeks) use ($workout) {
                $carbonDayDate = Carbon::parse('2021-10-29 21:30:00');
                $viewWorkouts = $viewWeeks[$carbonDayDate->weekNumberInMonth][$carbonDayDate->dayOfWeek]['workouts'];
                $this->assertCount(1, $viewWorkouts);
                $this->assertSame($workout->id, $viewWorkouts[0]->id);
                $this->assertSame($workout->name, $viewWorkouts[0]->name);
                return true;
            })
            ->assertViewHas('addableWorkouts', function ($viewAddableWorkouts) {
                $this->assertCount(1, $viewAddableWorkouts);
                return true;
            });
    }

    /** @test */
    public function add_workout_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_plan_add_workout'));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function add_workout_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_plan_add_workout'));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function add_workout_premium_user_can_add_a_workout_to_their_own_plan_with_valid_data()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
        ]);

        $this->assertDatabaseCount('workout_plans', 1)
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-11-29',
            ]);
    }

    /** @test */
    public function add_workout_premium_user_can_add_a_daily_repeating_workout_to_their_own_plan_with_valid_data()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
            'repeat_frequency' => 'daily',
            'repeat_until' => '2021-12-05',
        ]);

        $this->assertDatabaseCount('workout_plans', 7)
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-11-29',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-11-30',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-01',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-02',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-03',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-04',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-05',
            ]);
    }

    /** @test */
    public function add_workout_premium_user_can_add_an_every_other_day_repeating_workout_to_their_own_plan_with_valid_data()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
            'repeat_frequency' => 'other',
            'repeat_until' => '2021-12-05',
        ]);

        $this->assertDatabaseCount('workout_plans', 4)
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-11-29',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-01',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-03',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-05',
            ]);
    }

    /** @test */
    public function add_workout_premium_user_can_add_a_weekly_repeating_workout_to_their_own_plan_with_valid_data()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
            'repeat_frequency' => 'weekly',
            'repeat_until' => '2021-12-14',
        ]);

        $this->assertDatabaseCount('workout_plans', 3)
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-11-29',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-06',
            ])
            ->assertDatabaseHas('workout_plans', [
                'workout_id' => $workout->id,
                'date' => '2021-12-13',
            ]);
    }

    /** @test */
    public function add_workout_premium_user_can_not_add_a_workout_to_their_own_plan_without_a_workout()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            // missing workout
            'date' => '2021-11-29',
        ]);

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function add_workout_premium_user_can_not_add_a_workout_to_their_own_plan_with_a_non_existent_workout()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => 5946,
            'date' => '2021-11-29',
        ]);

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function add_workout_premium_user_can_not_add_a_workout_to_their_own_plan_without_a_date()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            // date missing
        ]);

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function add_workout_premium_user_can_not_add_a_workout_to_their_own_plan_without_a_repeat_frequency()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
            // repeat_frequency missing
            'repeat_until' => '2021-12-14',
        ]);

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function add_workout_premium_user_can_not_add_a_workout_to_their_own_plan_without_a_repeat_until()
    {
        $workout = Workout::factory()->create();

        $response = $this->actingAs($this->premiumUser)->post(route('workout_plan_add_workout'), [
            'workout' => $workout->id,
            'date' => '2021-11-29',
            'repeat_frequency' => 'weekly',
            // repeat_until missing
        ]);

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function remove_workout_non_logged_in_user_redirects_to_login()
    {
        $response = $this->get(route('workout_plan_remove_workout', 1));

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function remove_workout_non_premium_user_redirects_to_premium()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workout_plan_remove_workout', 1));

        $response->assertRedirect('/premium');
    }

    /** @test */
    public function remove_workout_premium_user_can_remove_a_workout_from_their_own_plan()
    {
        $workout = Workout::factory()->create();
        $this->premiumUser->planWorkouts()->attach($workout->id, ['date' => '2021-11-29']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_plan_remove_workout', $this->premiumUser->planWorkouts->first()->pivot->id));

        $this->assertDatabaseCount('workout_plans', 0);
    }

    /** @test */
    public function remove_workout_premium_user_can_not_remove_a_workout_from_the_plan_of_a_different_user()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
        $user->planWorkouts()->attach($workout->id, ['date' => '2021-11-29']);

        $response = $this->actingAs($this->premiumUser)->get(route('workout_plan_remove_workout', $user->planWorkouts->first()->pivot->id));

        $this->assertDatabaseCount('workout_plans', 1)
            ->assertDatabaseHas('workout_plans', [
                'user_id' => $user->id,
                'workout_id' => $workout->id,
                'date' => '2021-11-29',
            ]);
    }
}
