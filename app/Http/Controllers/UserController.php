<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeEntry;
use App\Models\Equipment;
use App\Models\Follower;
use App\Http\Requests\Subscribe;
use App\Http\Requests\UpdateUser;
use App\Models\Movement;
use App\Notifications\UserFollowed;
use App\Notifications\UserFollowRequested;
use App\Models\Review;
use App\Models\Spot;
use App\Models\SpotComment;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\UserSettingsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private function updateFollowersCount($id)
    {
        $user = User::with(['followers'])->where('id', $id)->first();
        $followers = $user->followers()->count();
        $user->followers_quantified = quantify_number($followers);
        $user->save();
    }

    public function listing(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $users = User::whereNotNull('email_verified_at')
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Users',
            'content' => $users,
            'component' => 'user',
        ]);
    }

    public function view(Request $request, $id, $tab = null)
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('user_view', $id);
        }

        $user = User::with([
            'spots',
            'reports',
            'hits',
            'reviews',
            'spotComments',
            'challenges',
            'challengeEntries',
            'workouts',
            'movements',
            'equipment',
            'followers',
            'following',
        ])
            ->where('id', $id)
            ->first();

        if (empty($user) || ($user->deleted_at !== null && Auth::id() !== $user->id)) {
            return view('errors.404');
        }

        $spots = $hits = $reviews = $comments = $challenges = $entries = $workouts = $movements = $equipments = $followers = $following = $followRequests = null;
        if ($tab == null || $tab === 'spots') {
            $spots = $user->spots()
                ->with(['hits', 'reviews', 'reports', 'user'])
                ->orderByDesc('rating')
                ->paginate(10);
        }
        if ($tab === 'hitlist') {
            $hits = $user->hits()
                ->with('spot')
                ->whereHas('spot')
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'reviews') {
            $reviews = $user->reviews()
                ->with(['spot', 'user', 'reports'])
                ->whereHas('spot')
                ->whereNotNull('title')
                ->orderByDesc('created_at')
                ->paginate(20);
            $userReviewsWithTextCount = $user->reviews()->withText()->count();
        }
        if ($tab === 'comments') {
            $linkSpotOnComment = true;
            $comments = $user->spotComments()
                ->with(['reports', 'user'])
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'challenges') {
            $challenges = $user->challenges()
                ->withCount('entries')
                ->with(['entries', 'reports', 'spot', 'user'])
                ->whereHas('spot')
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'entries') {
            $entries = $user->challengeEntries()
                ->with(['challenge', 'reports', 'user'])
                ->whereHas('challenge')
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'workouts') {
            $workouts = $user->workouts()
                ->with(['movements', 'user', 'spots'])
                ->withCount('movements')
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'movements') {
            $movements = $user->movements()
                ->with(['reports', 'moves', 'user', 'spots'])
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'equipment') {
            $equipments = $user->equipment()
                ->withCount(['movements'])
                ->with(['movements', 'reports', 'user'])
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'followers') {
            $followers = $user->followers()
                ->with('followers')
                ->where('accepted', true)
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'following') {
            $following = $user->following()
                ->with('followers')
                ->orderByDesc('created_at')
                ->paginate(10);
        }
        if ($tab === 'follow_requests') {
            $followRequests = $user->followers()
                ->with('followers')
                ->where('accepted', false)
                ->orderByDesc('created_at')
                ->paginate(10);
        }

        $showHometown = !empty($user->hometown_name) && (
                (
                    setting('privacy_hometown', null, $user->id) === 'anybody' || (
                        setting('privacy_hometown', null, $user->id) === 'follower' &&
                        !empty($user->followers->firstWhere('id', Auth()->id()))
                    )
                ) ||
                $user->id === Auth()->id()
            );

        return view('user.view', [
            'user' => $user,
            'request' => $request,
            'spots' => $spots,
            'hits' => $hits,
            'reviews' => $reviews,
            'comments' => $comments,
            'linkSpotOnComment' => $linkSpotOnComment ?? false,
            'challenges' => $challenges,
            'entries' => $entries,
            'workouts' => $workouts,
            'movements' => $movements,
            'equipments' => $equipments,
            'followers' => $followers,
            'following' => $following,
            'followRequests' => $followRequests,
            'tab' => $tab,
            'showHometown' => $showHometown,
            'userReviewsWithTextCount' => $userReviewsWithTextCount ?? 0,
        ]);
    }

    public function manage()
    {
        $user = Auth::user();
        $subscribed = Subscriber::where('email', $user->email)->exists();
        $settings = setting()->all();

        return view('user.manage', [
            'user' => Auth::user(),
            'subscribed' => $subscribed,
            'settings' => $settings,
        ]);
    }

    public function update(UpdateUser $request)
    {
        if (!empty($request['account-form'])) {
            $user = User::where('id', Auth::id())->first();
            if (empty($request['name'])) {
                $user->name = 'User' . Auth::id();
            } else {
                $user->name = $request['name'];
            }
            $user->email = $request['email'];
            if (!empty($request->file('profile_image'))) {
                $user->profile_image = Storage::url($request->file('profile_image')->store('images/users/profile', 'public'));
            } else if (empty($request['old_profile_image'])) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile_image));
                $user->profile_image = null;
            }
            if (!empty($request->file('cover_image'))) {
                $user->cover_image = Storage::url($request->file('cover_image')->store('images/users/cover', 'public'));
            } else if (empty($request['old_cover_image'])) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->cover_image));
                $user->cover_image = null;
            }
            if (!empty($request['hometown'])) {
                $hometown = explode('|', $request['hometown']);
                $user->hometown_name = $hometown[0];
                $user->hometown_bounding = $hometown[1];
            } else {
                $user->hometown_name = null;
                $user->hometown_bounding = null;
            }
            $user->save();

            if  ($request['subscribed'] == true) {
                $this->subscribe(new Subscribe(['email' => $request['email']]), false);
            } else {
                $this->unsubscribe();
            }
        } else if (!empty($request['notification-form']) || !empty($request['privacy-form'])) {
            if (!empty($request['notification-form'])) {
                setting()->set($request['notifications']);
            } else if (!empty($request['privacy-form'])) {
                setting()->set($request['privacy']);
            }
            setting()->save();

            UserSettingsLog::create([
                'user_id' => Auth::id(),
                'settings' => Auth::user()->settings,
            ]);
        }

        return back()->with('status', 'Updated account information');
    }

    public function subscribe(Subscribe $request, $return = true)
    {
        if (!Subscriber::where('email', $request['email'])->exists()) {
            $subscriber = new Subscriber;
            $subscriber->email = $request['email'];
            $subscriber->save();
        }

        if ($return) {
            return back()->with('status', 'Thank you for subscribing!');
        }
    }

    public function unsubscribe()
    {
        $subscriber = Subscriber::where('email', Auth::user()->email);
        if ($subscriber->exists()) {
            $subscriber->delete();
        }
    }

    public function obfuscate($field)
    {
        $user = User::where('id', Auth::id())->first();
        switch($field) {
            case 'name':
                $user->name = 'User' . Auth::id();
                break;
        }
        $user->save();

        return back()->with('status', 'Obfuscated ' . $field);
    }

    public function delete()
    {
        $user = User::where('id', Auth::id())->first();

        if (!empty($user->profile_image)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $user->profile_image));
        }
        if (!empty($user->cover_image)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $user->cover_image));
        }

        $user->forceDelete();

        return redirect()->route('welcome');
    }

    public function spots(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
                'views' => 'views_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $spots = Spot::withCount('views')
            ->where('user_id', $userID)
            ->hitlist(!empty($request['on_hitlist']) ? true : false)
            ->ticked(!empty($request['ticked_hitlist']) ? true : false)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Spots',
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function hitlist(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
                'views' => 'views_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $spots = Spot::withCount('views')
            ->hitlist(true)
            ->ticked(!empty($request['ticked_hitlist']) ? true : false)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Hitlist',
            'content' => $spots,
            'component' => 'spot',
            'hitlist' => true,
        ]);
    }

    public function reviews(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $reviews = Review::where('user_id', $userID)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Reviews',
            'content' => $reviews,
            'component' => 'review',
            'options' => ['user' => true],
        ]);
    }

    public function comments(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'rating' => 'rating',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $comments = SpotComment::where('user_id', $userID)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Comments',
            'content' => $comments,
            'component' => 'comment',
            'options' => ['user' => true],
        ]);
    }

    public function challenges(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
                'difficulty' => 'difficulty',
                'entries' => 'entries_count',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $challenges = Challenge::withCount('entries')
            ->where('user_id', $userID)
            ->entered(!empty($request['entered']) ? true : false)
            ->difficulty($request['difficulty'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Challenges',
            'content' => $challenges,
            'component' => 'challenge',
        ]);
    }

    public function entries(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $entries = ChallengeEntry::where('user_id', $userID)
            ->winner(!empty($request['winner']) ? true : false)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Challenge Entries',
            'content' => $entries,
            'component' => 'entry',
        ]);
    }

    public function movements(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $movements = Movement::withCount('spots')
            ->where('user_id', $userID)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->type($request['type'] ?? null)
            ->category($request['category'] ?? null)
            ->exercise($request['exercise'] ?? null)
            ->equipment($request['equipment'] ?? null)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Movements',
            'content' => $movements,
            'component' => 'movement',
            'options' => ['user' => true],
        ]);
    }

    public function equipment(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $userID = Auth::id();
        $equipments = Equipment::where('user_id', $userID)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Your Equipment',
            'content' => $equipments,
            'component' => 'equipment',
            'options' => ['user' => true],
        ]);
    }

    public function bin(Request $request, $tab = null)
    {
        $id = Auth::id();
        $user = User::with([
            'spots' => function($q) {
                $q->onlyTrashed();
            },
            'reviews' => function($q) {
                $q->onlyTrashed();
            },
            'spotComments' => function($q) {
                $q->onlyTrashed();
            },
            'challenges' => function($q) {
                $q->onlyTrashed();
            },
            'challengeEntries' => function($q) {
                $q->onlyTrashed();
            },
            'movements' => function($q) {
                $q->onlyTrashed();
            },
            'equipment' => function($q) {
                $q->onlyTrashed();
            },
            'workouts' => function($q) {
                $q->onlyTrashed();
            }
        ])
            ->where('id', $id)
            ->first();

        $spots = $reviews = $comments = $challenges = $entries = $movements = $equipment = $workouts = null;
        if ($tab == null || $tab === 'spots') {
            $spots = $user->spots()
                ->onlyTrashed()
                ->with(['hits', 'reviews', 'reports', 'user'])
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'reviews') {
            $reviews = $user->reviews()
                ->onlyTrashed()
                ->with(['spot', 'user', 'reports'])
                ->whereHas('spot')
                ->whereNotNull('title')
                ->orderByDesc('deleted_at')
                ->paginate(40);
        }
        if ($tab === 'comments') {
            $linkSpotOnComment = true;
            $comments = $user->spotComments()
                ->onlyTrashed()
                ->with(['reports', 'user'])
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'challenges') {
            $challenges = $user->challenges()
                ->onlyTrashed()
                ->withCount('entries')
                ->with(['entries', 'reports', 'spot', 'user'])
                ->whereHas('spot')
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'entries') {
            $entries = $user->challengeEntries()
                ->onlyTrashed()
                ->with(['challenge', 'reports', 'user'])
                ->whereHas('challenge')
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'movements') {
            $movements = $user->movements()
                ->onlyTrashed()
                ->with(['reports', 'moves', 'user', 'spots'])
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'equipment') {
            $equipment = $user->equipment()
                ->onlyTrashed()
                ->withCount(['movements'])
                ->with(['movements', 'reports', 'user'])
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }
        if ($tab === 'workouts') {
            $workouts = $user->workouts()
                ->onlyTrashed()
                ->withCount(['movements'])
                ->with(['movements', 'user'])
                ->orderByDesc('deleted_at')
                ->paginate(20);
        }

        return view('user.bin', [
            'user' => $user,
            'request' => $request,
            'spots' => $spots,
            'reviews' => $reviews,
            'comments' => $comments,
            'linkSpotOnComment' => $linkSpotOnComment ?? false,
            'challenges' => $challenges,
            'entries' => $entries,
            'movements' => $movements,
            'equipments' => $equipment,
            'workouts' => $workouts,
            'tab' => $tab,
        ]);
    }

    public function fetchHometownBounding(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $bounding = explode(',', !empty($userHometown = Auth::user()->hometown_bounding) ? $userHometown : '');

        return count($bounding) === 4 ? $bounding : false;
    }

    public function follow(Request $request, $id)
    {
        if (!empty(Follower::where('user_id', $id)->where('follower_id', Auth::id())->first())) {
            return back()->with('status', 'You are already following this user or they haven\'t accepted your request yet');
        }

        $followSetting = setting('privacy_follow', 'nobody', $id);
        if ($followSetting === 'nobody') {
            return back()->with('status', 'This user is not accepting followers');
        }

        $follower = new Follower;
        $follower->user_id = $id;
        $follower->follower_id = Auth::id();
        if ($followSetting === 'anybody') {
            $follower->accepted = true;
        }
        $follower->save();

        // notify the user that someone started following them or requested to follow them
        $notificationSetting = setting('notifications_follower', 'on-site', $id);
        $user = User::with(['followers'])->where('id', $id)->first();
        switch ($followSetting) {
            case 'request':
                if ($notificationSetting !== 'none') {
                    $user->notify(new UserFollowRequested($follower));
                }
                $status = 'Successfully sent follow request to user';
                break;
            case 'anybody':
                if ($notificationSetting !== 'none') {
                    $user->notify(new UserFollowed($follower));
                }
                $this->updateFollowersCount($id);
                $status = 'Successfully started following user';
                break;
        }

        return back()->with('status', $status);
    }

    public function unfollow(Request $request, $id)
    {
        $follower = Follower::where('user_id', $id)->where('follower_id', Auth::id())->first();
        $follower->delete();

        $this->updateFollowersCount($id);

        return back()->with('status', 'Successfully unfollowed user');
    }

    public function removeFollower(Request $request, $id) {
        $follower = Follower::where('user_id', Auth::id())->where('follower_id', $id)->first();
        $follower->delete();

        return back()->with('status', 'Successfully removed follower');
    }

    public function followers(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $followers = Follower::where('user_id', Auth::id())->where('accepted', true)->pluck('follower_id');

        $users = User::whereIn('id', $followers)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Followers',
            'content' => $users,
            'component' => 'user',
        ]);
    }

    public function following(Request $request)
    {
        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $followings = Follower::where('follower_id', Auth::id())->where('accepted', true)->pluck('user_id');

        $users = User::whereIn('id', $followings)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Following',
            'content' => $users,
            'component' => 'user',
        ]);
    }

    public function followRequests(Request $request)
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('user_follow_requests');
        }

        $sort = ['created_at', 'desc'];
        if (!empty($request['sort'])) {
            $fieldMapping = [
                'date' => 'created_at',
            ];
            $sortParams = explode('_', $request['sort']);
            $sort = [$fieldMapping[$sortParams[0]], $sortParams[1]];
        }

        $requests = Follower::where('user_id', Auth::id())->where('accepted', false)->pluck('follower_id');

        $users = User::whereIn('id', $requests)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Follow Requests',
            'content' => $users,
            'component' => 'user',
        ]);
    }

    public function acceptFollower($id)
    {
        $follower = Follower::where('user_id', Auth::id())->where('follower_id', $id)->first();
        $follower->accepted = true;
        $follower->save();

        $this->updateFollowersCount($id);

        return back()->with('status', 'Accepted follow request');
    }

    public function rejectFollower($id)
    {
        $follower = Follower::where('user_id', Auth::id())->where('follower_id', $id)->first();
        $follower->delete();

        return back()->with('status', 'Rejected follow request');
    }

    public function resetPassword()
    {
        $user = Auth::user();
        $resetToken = app('auth.password.broker')->createToken($user);
        if (!empty($resetToken)) {
            return view('auth.passwords.reset')->with(['token' => $resetToken, 'email' => $user->email]);
        }

        return back();
    }
}
