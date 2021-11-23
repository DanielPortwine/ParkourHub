<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Equipment;
use App\Models\Hit;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementType;
use App\Models\Review;
use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

//    /** @test */
//    public function listing_non_logged_in_user_redirects_to_login()
//    {
//        $response = $this->get(route('user_listing'));
//
//        $response->assertRedirect('/email/verify');
//    }
//
//    /** @test */
//    public function listing_non_premium_user_can_view_verified_users_matching_search_term()
//    {
//        $user = User::factory()->create();
//
//        $response = $this->actingAs($user)->get(route('user_listing', ['search' => $this->premiumUser->name]));
//
//        $response->assertOk()
//            ->assertViewIs('content_listings')
//            ->assertViewHas('content', function ($viewUser) {
//                $this->assertCount(1, $viewUser);
//                $this->assertSame($this->premiumUser->id, $viewUser->first()->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->first()->name);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function listing_premium_user_can_not_view_verified_users_without_a_search_term()
//    {
//        $user = User::factory()->create();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_listing'));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function listing_premium_user_can_not_view_unverified_users_matching_search_term()
//    {
//        $user = User::factory()->create(['email_verified_at' => null]);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_listing', ['search' => $user->name]));
//
//        $response->assertOk()
//            ->assertViewIs('content_listings')
//            ->assertViewHas('content', function ($viewUser) {
//                $this->assertCount(0, $viewUser);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function listing_premium_user_can_not_view_deleted_verified_users_matching_search_term()
//    {
//        $user = User::factory()->create(['email_verified_at' => null]);
//        $user->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_listing', ['search' => $user->name]));
//
//        $response->assertOk()
//            ->assertViewIs('content_listings')
//            ->assertViewHas('content', function ($viewUser) {
//                $this->assertCount(0, $viewUser);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_logged_in_user_redirects_to_login()
//    {
//        $response = $this->get(route('user_view', 1));
//
//        $response->assertRedirect('/email/verify');
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user()
//    {
//        $user = User::factory()->create();
//
//        $response = $this->actingAs($user)->get(route('user_view', $this->premiumUser->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_unverified_user()
//    {
//        $user = User::factory()->create(['email_verified_at' => null]);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $user->id));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_verified_user()
//    {
//        $user = User::factory()->create();
//        $user->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $user->id));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_spots()
//    {
//        $user = User::factory()->create();
//        $spots = Spot::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(1, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_spots_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', $this->premiumUser->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(1, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_spots_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $user->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(0, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_spots()
//    {
//        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $this->premiumUser->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(1, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_spots_of_different_user()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $user->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(0, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_spots()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $spot->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', $user->id));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('spots', function ($viewSpots) {
//                $this->assertCount(0, $viewSpots);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_hits()
//    {
//        $user = User::factory()->create();
//        $spots = Spot::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//        foreach ($spots as $spot) {
//            Hit::factory()->create(['user_id' => $this->premiumUser->id, 'spot_id' => $spot->id]);
//        }
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'hitlist', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(1, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_paginated_hits_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//        Hit::factory()->create(['user_id' => $this->premiumUser->id, 'spot_id' => $spot->id]);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'hitlist']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(1, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_hits_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//        Hit::factory()->create(['user_id' => $user->id, 'spot_id' => $spot->id]);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'hitlist']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(0, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_hits()
//    {
//        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//        Hit::factory()->create(['user_id' => $this->premiumUser->id, 'spot_id' => $spot->id]);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'hitlist']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(1, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_hits_of_different_user()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//        Hit::factory()->create(['user_id' => $user->id, 'spot_id' => $spot->id]);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'hitlist']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(0, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_hits()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        Hit::factory()->create(['user_id' => $user->id, 'spot_id' => $spot->id]);
//        $spot->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'hitlist']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('hits', function ($viewHits) {
//                $this->assertCount(0, $viewHits);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_reviews()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $reviews = Review::factory()->times(41)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'reviews', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(1, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_reviews_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $review = Review::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'reviews']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(1, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_reviews_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'reviews']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(0, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_reviews()
//    {
//        $spot = Spot::factory()->create();
//        $review = Review::factory()->create(['visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'reviews']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(1, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_reviews_of_different_user()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'reviews']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(0, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_reviews()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $review = Review::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $review->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'reviews']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('reviews', function ($viewReviews) {
//                $this->assertCount(0, $viewReviews);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_comments()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comments = SpotComment::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'comments', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(1, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_comments_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'comments']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(1, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_comments_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'comments']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(0, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_comments()
//    {
//        $spot = Spot::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//        $comment = SpotComment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'comments']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(1, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_comments_of_different_user()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'comments']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(0, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_comments()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comment = SpotComment::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $comment->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'comments']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('comments', function ($viewComments) {
//                $this->assertCount(0, $viewComments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_challenges()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenges = Challenge::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'challenges', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(1, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_challenges_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'challenges']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(1, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_challenges_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'challenges']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(0, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_challenges()
//    {
//        $spot = Spot::factory()->create();
//        $challenge = Challenge::factory()->create(['visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'challenges']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(1, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_challenges_of_different_user()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'challenges']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(0, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_challenges()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'challenges']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('challenges', function ($viewChallenges) {
//                $this->assertCount(0, $viewChallenges);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_entries()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//        $entries = ChallengeEntry::factory()->times(21)->create(['user_id' => $this->premiumUser->id]);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'entries', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('entries', function ($viewEntries) {
//                $this->assertCount(1, $viewEntries);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_verified_user_entries()
//    {
//        $user = User::factory()->create();
//        $spot = Spot::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $challenge = Challenge::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//        $entry = ChallengeEntry::factory()->create(['user_id' => $this->premiumUser->id]);
//        $entry->delete();
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'entries']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('entries', function ($viewEntries) {
//                $this->assertCount(0, $viewEntries);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_workouts()
//    {
//        $user = User::factory()->create();
//        $workouts = Workout::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'workouts', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(1, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_workouts_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'workouts']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(1, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_workouts_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'workouts']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(0, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_workouts()
//    {
//        $this->premiumUser->followers()->attach($this->premiumUser->id, ['accepted' => true]);
//        $workout = Workout::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'workouts']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(1, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_workouts_of_different_user()
//    {
//        $user = User::factory()->create();
//        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'workouts']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(0, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_workouts()
//    {
//        $user = User::factory()->create();
//        $workout = Workout::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $workout->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'workouts']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('workouts', function ($viewWorkouts) {
//                $this->assertCount(0, $viewWorkouts);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_movements()
//    {
//        $user = User::factory()->create();
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movements = Movement::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'movements', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(1, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_movements_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'movements']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(1, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_movements_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'movements']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(0, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_movements()
//    {
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movement = Movement::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'movements']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(1, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_movements_of_different_user()
//    {
//        $user = User::factory()->create();
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'movements']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(0, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_movements()
//    {
//        $user = User::factory()->create();
//        $movementType = MovementType::factory()->create();
//        $movementCategory = MovementCategory::factory()->create();
//        $movement = Movement::factory()->create(['user_id' => $user->id, 'visibility' => 'public']);
//        $movement->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'movements']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('movements', function ($viewMovements) {
//                $this->assertCount(0, $viewMovements);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_equipment()
//    {
//        $user = User::factory()->create();
//        $equipment = Equipment::factory()->times(21)->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(1, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_follower_verified_user_equipment_of_user_they_follow()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id, ['accepted' => true]);
//        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(1, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_follower_verified_user_equipment_of_user_they_do_not_follow()
//    {
//        $user = User::factory()->create();
//        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'follower']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(0, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_their_own_private_verified_user_equipment()
//    {
//        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(1, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_private_verified_user_equipment_of_different_user()
//    {
//        $user = User::factory()->create();
//        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'private']);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(0, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_public_verified_user_equipment()
//    {
//        $user = User::factory()->create();
//        $equipment = Equipment::factory()->create(['user_id' => $this->premiumUser->id, 'visibility' => 'public']);
//        $equipment->delete();
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'equipment']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('equipments', function ($viewEquipments) {
//                $this->assertCount(0, $viewEquipments);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_accepted_followers_of_user_allowing_anybody_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $followers = User::factory()->times(41)->create();
//        $this->premiumUser->followers()->attach($followers, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'anybody', $this->premiumUser->id);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'followers', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('followers', function ($viewFollowers) {
//                $this->assertCount(1, $viewFollowers);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_unaccepted_followers_of_user_allowing_anybody_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->followers()->attach($follower);
//        setting()->set('privacy_follow_lists', 'anybody', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'followers']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('followers', function ($viewFollowers) {
//                $this->assertCount(0, $viewFollowers);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_verified_user_accepted_followers_of_user_allowing_anybody_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->followers()->attach($follower, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'anybody', $user->id);
//        $follower->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'followers']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('followers', function ($viewFollowers) {
//                $this->assertCount(0, $viewFollowers);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_verified_user_accepted_followers_of_user_they_follow_allowing_followers_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'follower', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'followers']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('followers', function ($viewFollowers) {
//                $this->assertCount(1, $viewFollowers);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_accepted_followers_of_user_they_follow_allowing_followers_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->followers()->attach($follower->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'follower', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'followers']));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_accepted_followers_of_user_allowing_nobody_to_see_their_followers()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->followers()->attach($follower->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'nobody', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'followers']));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_verified_user_paginated_accepted_followings_of_user_allowing_anybody_to_see_who_they_follow()
//    {
//        $user = User::factory()->create();
//        $followings = User::factory()->times(41)->create();
//        $this->premiumUser->following()->attach($followings, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'anybody', $this->premiumUser->id);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'following', 'page' => 2]));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('following', function ($viewFollowings) {
//                $this->assertCount(1, $viewFollowings);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_unaccepted_followings_of_user_allowing_anybody_to_see_who_they_follow()
//    {
//        $user = User::factory()->create();
//        $following = User::factory()->create();
//        $user->following()->attach($following);
//        setting()->set('privacy_follow_lists', 'anybody', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'following']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('following', function ($viewFollowings) {
//                $this->assertCount(0, $viewFollowings);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_deleted_verified_user_accepted_followings_of_user_allowing_anybody_to_see_who_they_follow()
//    {
//        $user = User::factory()->create();
//        $following = User::factory()->create();
//        $user->following()->attach($following, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'anybody', $user->id);
//        $following->delete();
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'following']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('following', function ($viewFollowings) {
//                $this->assertCount(0, $viewFollowings);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_view_verified_user_accepted_followings_of_user_they_follow_allowing_followers_to_see_their_followings()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->following()->attach($follower->id, ['accepted' => true]);
//        $user->followers()->attach($this->premiumUser->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'follower', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'following']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('following', function ($viewFollowings) {
//                $this->assertCount(1, $viewFollowings);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_accepted_followings_of_user_they_do_not_follow_allowing_followers_to_see_their_followings()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->following()->attach($follower->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'follower', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'following']));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_accepted_followings_of_user_allowing_nobody_to_see_their_followings()
//    {
//        $user = User::factory()->create();
//        $follower = User::factory()->create();
//        $user->following()->attach($follower->id, ['accepted' => true]);
//        setting()->set('privacy_follow_lists', 'nobody', $user->id);
//
//        $response = $this->actingAs($this->premiumUser)->get(route('user_view', [$user->id, 'tab' => 'following']));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function view_non_premium_user_can_view_their_own_verified_user_follow_requests()
//    {
//        $user = User::factory()->create();
//        $user->followers()->attach($this->premiumUser->id);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$user->id, 'tab' => 'follow_requests']));
//
//        $response->assertOk()
//            ->assertViewIs('user.view')
//            ->assertViewHas('user', function ($viewUser) use ($user) {
//                $this->assertSame($user->id, $viewUser->id);
//                $this->assertSame($user->name, $viewUser->name);
//                return true;
//            })
//            ->assertViewHas('followRequests', function ($viewFollowRequests) {
//                $this->assertCount(1, $viewFollowRequests);
//                return true;
//            });
//    }
//
//    /** @test */
//    public function view_premium_user_can_not_view_verified_user_follow_requests_of_different_user()
//    {
//        $user = User::factory()->create();
//        $this->premiumUser->followers()->attach($user->id);
//
//        $response = $this->actingAs($user)->get(route('user_view', [$this->premiumUser->id, 'tab' => 'follow_requests']));
//
//        $response->assertNotFound();
//    }
//
//    /** @test */
//    public function manage_non_logged_in_user_redirects_to_login()
//    {
//        $response = $this->get(route('user_manage'));
//
//        $response->assertRedirect('/login');
//    }
//
//    /** @test */
//    public function manage_user_can_view_their_manage_page()
//    {
//        $response = $this->actingAs($this->premiumUser)->get(route('user_manage'));
//
//        $response->assertOk()
//            ->assertViewIs('user.manage')
//            ->assertViewHas('user', function($viewUser) {
//                $this->assertSame($this->premiumUser->id, $viewUser->id);
//                $this->assertSame($this->premiumUser->name, $viewUser->name);
//                return true;
//            });
//    }

    /** @test */
    public function update_non_logged_in_user_redirects_to_login()
    {
        $response = $this->post(route('user_update', 1), []);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function update_premium_user_can_update_their_name_with_valid_data()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => 'Updated',
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => 'Updated',
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_update_their_name_with_invalid_data()
    {
        $originalName = $this->premiumUser->name;

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => 'Updated Username That Is Too Long',
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $originalName,
            ]);
    }

    /** @test */
    public function update_premium_user_can_update_their_images_with_valid_data_and_old_images_are_deleted()
    {
        Storage::fake('public');
        $oldProfile = $this->premiumUser->profile_image;
        $oldCover = $this->premiumUser->cover_image;
        $profile = UploadedFile::fake()->image('profile.png', 640, 480);
        $cover = UploadedFile::fake()->image('cover.png', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $oldProfile,
            'old_cover_image' => $oldCover,
            'profile_image' => $profile,
            'cover_image' => $cover,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => 'User' . $this->premiumUser->id,
                'profile_image' => '/storage/images/users/profile/' . $profile->hashName(),
                'cover_image' => '/storage/images/users/cover/' . $cover->hashName(),
            ]);

        Storage::disk('public')->assertExists('images/users/profile/' . $profile->hashName());
        Storage::disk('public')->assertExists('images/users/cover/' . $cover->hashName());
        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $oldProfile));
        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $oldCover));
    }

    /** @test */
    public function update_premium_user_can_not_update_their_images_with_invalid_data()
    {
        Storage::fake('public');
        $oldProfile = $this->premiumUser->profile_image;
        $oldCover = $this->premiumUser->cover_image;
        $profile = UploadedFile::fake()->image('profile.bmp', 640, 480);
        $cover = UploadedFile::fake()->image('cover.bmp', 640, 480);

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $oldProfile,
            'old_cover_image' => $oldCover,
            'profile_image' => $profile,
            'cover_image' => $cover,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'profile_image' => $oldProfile,
                'cover_image' => $oldCover,
            ]);

        Storage::disk('public')->assertMissing('images/users/profile/' . $profile->hashName());
        Storage::disk('public')->assertMissing('images/users/cover/' . $cover->hashName());
    }

    /** @test */
    public function update_premium_user_can_delete_their_images_with_valid_data()
    {
        Storage::fake('public');
        $oldProfile = $this->premiumUser->profile_image;
        $oldCover = $this->premiumUser->cover_image;

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'profile_image' => null,
                'cover_image' => null,
            ]);

        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $oldProfile));
        Storage::disk('public')->assertMissing(str_replace('/storage/', '', $oldCover));
    }

    /** @test */
    public function update_premium_user_can_update_their_hometown_with_valid_data()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'hometown' => 'City of Durham, Durham, County Durham, North East England, England, United Kingdom|54.7358637,54.793347,-1.6058428,-1.553796'
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'hometown_name' => 'City of Durham, Durham, County Durham, North East England, England, United Kingdom',
                'hometown_bounding' => '54.7358637,54.793347,-1.6058428,-1.553796',
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_update_their_hometown_with_invalid_data()
    {
        $this->premiumUser->hometown_name = 'City of Durham, Durham, County Durham, North East England, England, United Kingdom';
        $this->premiumUser->hometown_bounding = '54.7358637,54.793347,-1.6058428,-1.553796';
        $this->premiumUser->save();

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'hometown' => 'City of Durham|54.7358637,54.793347'
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'hometown_name' => 'City of Durham, Durham, County Durham, North East England, England, United Kingdom',
                'hometown_bounding' => '54.7358637,54.793347,-1.6058428,-1.553796',
            ]);
    }

    /** @test */
    public function update_premium_user_can_subscribe_with_valid_data()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'subscribed' => true,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
            ])
            ->assertDatabaseCount('subscribers', 1)
            ->assertDatabaseHas('subscribers', [
                'email' => $this->premiumUser->email,
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_subscribe_with_invalid_data()
    {
        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $this->premiumUser->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'subscribed' => 'true',
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
            ])
            ->assertDatabaseCount('subscribers', 0);
    }

    /** @test */
    public function update_premium_user_can_update_their_email_with_valid_data()
    {
        $subscriber = new Subscriber([
            'email' => $this->premiumUser->email,
        ]);

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => 'new.email@parkourhub.co.uk',
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'subscribed' => true,
        ]);

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'email' => 'new.email@parkourhub.co.uk',
            ])
            ->assertDatabaseCount('subscribers', 1)
            ->assertDatabaseHas('subscribers', [
                'email' => 'new.email@parkourhub.co.uk',
            ]);
    }

    /** @test */
    public function update_premium_user_can_not_update_their_email_with_invalid_data()
    {
        $user = User::factory()->create();
        $originalEmail = $this->premiumUser->email;

        $response = $this->actingAs($this->premiumUser)->post(route('user_update', $this->premiumUser->id), [
            'account-form' => true,
            'name' => $this->premiumUser->name,
            'email' => $user->email,
            'old_profile_image' => $this->premiumUser->profile_image,
            'old_cover_image' => $this->premiumUser->cover_image,
            'subscribed' => true,
        ]);

        $this->assertDatabaseCount('users', 2)
            ->assertDatabaseHas('users', [
                'name' => $this->premiumUser->name,
                'email' => $originalEmail,
            ])
            ->assertDatabaseMissing('users', [
                'name' => $this->premiumUser->name,
                'email' => $user->email,
            ]);
    }
}
