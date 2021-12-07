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
use App\Models\Comment;
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
        if (empty($request['search'])) {
            abort(404);
        }

        $users = User::whereNotNull('email_verified_at')
            ->search($request['search'] ?? false)
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Users',
            'content' => $users,
            'component' => 'user',
        ]);
    }

    public function view(Request $request, $id, $tab = 'spots')
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

        switch ($tab) {
            case 'hitlist':
                $spotWith = 'hits';
                break;
            case 'comments':
                $spotWith = 'comments';
                break;
            case 'entries':
                $spotWith = 'challengeEntries';
                break;
            case 'follow_requests':
                $spotWith = 'followers';
                break;
            default:
                $spotWith = $tab;
                break;
        }

        $user = User::with($spotWith)
            ->where('id', $id)
            ->whereNotNull('email_verified_at')
            ->first();

        if (empty($user)) {
            abort(404);
        }

        $spots = $hits = $reviews = $comments = $challenges = $entries = $workouts = $movements = $equipments = $followers = $following = $followRequests = null;
        if ($tab === 'spots') {
            $spots = $user->spots()
                ->with(['hits', 'reviews', 'reports', 'user'])
                ->orderByDesc('rating')
                ->paginate(20);
        }
        if ($tab === 'hitlist') {
            $hits = $user->hits()
                ->with('spot')
                ->whereHas('spot')
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'reviews') {
            $reviews = $user->reviews()
                ->with(['spot', 'user', 'reports'])
                ->whereHas('spot')
                ->whereNotNull('title')
                ->orderByDesc('created_at')
                ->paginate(40);
            $userReviewsWithTextCount = $user->reviews()->withText()->count();
        }
        if ($tab === 'comments') {
            $linkSpotOnComment = true;
            $comments = $user->comments()
                ->with(['reports', 'user'])
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'challenges') {
            $challenges = $user->challenges()
                ->withCount('entries')
                ->with(['entries', 'reports', 'spot', 'user'])
                ->whereHas('spot')
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'entries') {
            $entries = $user->challengeEntries()
                ->with(['challenge', 'reports', 'user'])
                ->whereHas('challenge')
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'workouts') {
            $workouts = $user->workouts()
                ->with(['movements', 'user', 'spots'])
                ->withCount('movements')
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'movements') {
            $movements = $user->movements()
                ->with(['reports', 'moves', 'user', 'spots'])
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'equipment') {
            $equipments = $user->equipment()
                ->withCount(['movements'])
                ->with(['movements', 'reports', 'user'])
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($tab === 'followers') {
            if (
                !(
                    setting('privacy_follow_lists', null, $user->id) === 'anybody' || (
                        setting('privacy_follow_lists', null, $user->id) === 'follower' &&
                        !empty($user->followers->firstWhere('id', Auth()->id()))
                    )
                ) &&
                $user->id !== Auth()->id()
            ) {
                abort(404);
            }

            $followers = $user->followers()
                ->with('followers')
                ->where('accepted', true)
                ->orderByDesc('created_at')
                ->paginate(40);
        }
        if ($tab === 'following') {
            if (
                !(
                    setting('privacy_follow_lists', null, $user->id) === 'anybody' || (
                        setting('privacy_follow_lists', null, $user->id) === 'follower' &&
                        !empty($user->followers->firstWhere('id', Auth()->id()))
                    )
                ) &&
                $user->id !== Auth()->id()
            ) {
                abort(404);
            }

            $following = $user->following()
                ->with('followers')
                ->where('accepted', true)
                ->orderByDesc('created_at')
                ->paginate(40);
        }
        if ($tab === 'follow_requests') {
            if ($user->id !== Auth()->id()) {
                abort(404);
            }

            $followRequests = $user->followers()
                ->with('followers')
                ->where('accepted', false)
                ->orderByDesc('created_at')
                ->paginate(40);
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
            'user' => $user,
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
            if ($user->email !== $request['email'] && !empty($subscriber = Subscriber::where('email', $user->email)->first())) {
                $subscriber->email = $request['email'];
                $subscriber->save();
            }
            $user->email = $request['email'];
            if (!empty($request->file('profile_image'))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile_image));
                $user->profile_image = Storage::url($request->file('profile_image')->store('images/users/profile', 'public'));
            } else if (empty($request['old_profile_image'])) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->profile_image));
                $user->profile_image = null;
            }
            if (!empty($request->file('cover_image'))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->cover_image));
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
            $user->instagram = str_replace('https://www.instagram.com/', '', trim($request['instagram'], '/'));
            $user->youtube = str_replace('https://www.youtube.com/c/', '', trim($request['youtube'], '/'));
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
                'settings' => User::where('id', Auth::id())->first()->settings,
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

        return redirect()->route('welcome')->with('status', 'Successfully deleted account and all related content');
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
            ->following(!empty($request['following']) ? true : false)
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

    public function bin(Request $request, $tab = 'spots')
    {
        $user = User::where('id', Auth::id())->first();

        $spots = $reviews = $comments = $challenges = $entries = $movements = $equipment = $workouts = null;
        if ($tab === 'spots') {
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
            $comments = $user->comments()
                ->onlyTrashed()
                ->with(['reports', 'user', 'commentable'])
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

    public function follow($id)
    {
        if ((int)$id === Auth::id()) {
            return back()->with('status', 'You can not follow yourself');
        }

        if (!empty(Follower::where('user_id', $id)->where('follower_id', Auth::id())->first())) {
            return back()->with('status', 'You are already following this user or they haven\'t accepted your request yet');
        }

        $followSetting = setting('privacy_follow', 'nobody', $id);
        if ($followSetting === 'nobody') {
            return back()->with('status', 'This user is not accepting followers');
        }

        $follower = new Follower([
            'user_id' => $id,
            'follower_id' => Auth::id(),
        ]);
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

    public function unfollow($id)
    {
        $follower = Follower::where('user_id', $id)->where('follower_id', Auth::id())->first();
        $follower->delete();

        $this->updateFollowersCount($id);

        return back()->with('status', 'Successfully unfollowed user');
    }

    public function removeFollower($id)
    {
        $follower = Follower::where('user_id', Auth::id())->where('follower_id', $id)->first();
        $follower->delete();

        return back()->with('status', 'Successfully removed follower');
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
