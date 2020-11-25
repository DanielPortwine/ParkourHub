<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\Follower;
use App\Http\Requests\Subscribe;
use App\Http\Requests\UpdateUser;
use App\Notifications\UserFollowed;
use App\Notifications\UserFollowRequested;
use App\Review;
use App\Spot;
use App\SpotComment;
use App\Subscriber;
use App\User;
use App\UserSettingsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
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

        $users = User::whereNotNull('email_verified_at')->orderBy($sort[0], $sort[1])->paginate(20);

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

        $user = User::with(['spots', 'challenges', 'reviews', 'spotComments', 'followers', 'following'])->where('id', $id)->first();

        $spots = $reviews = $comments = $challenges = $followers = $following = null;
        if (!empty($request['spots']) && ($tab == null || $tab === 'spots')) {
            $spots = $user->spots()->where('private', false)->orderByDesc('rating')->paginate(20, ['*'], 'spots');
        } else if ($tab == null || $tab === 'spots') {
            $spots = $user->spots()->where('private', false)->orderByDesc('rating')->limit(4)->get();
        }
        if (!empty($request['challenges']) && $tab === 'challenges') {
            $challenges = $user->challenges()->orderByDesc('created_at')->paginate(20, ['*'], 'challenges');
        } else if ($tab === 'challenges') {
            $challenges = $user->challenges()->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['reviews']) && $tab === 'reviews') {
            $reviews = $user->reviews()->whereNotNull('title')->orderByDesc('created_at')->paginate(20, ['*'], 'reviews');
        } else if ($tab == null || $tab === 'reviews') {
            $reviews = $user->reviews()->whereNotNull('title')->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['comments']) && $tab === 'comments') {
            $comments = $user->spotComments()->orderByDesc('created_at')->paginate(20, ['*'], 'comments');
        } else if ($tab === 'comments') {
            $comments = $user->spotComments()->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['followers']) && $tab === 'followers') {
            $followers = $user->followers()->orderByDesc('created_at')->paginate(20, ['*'], 'followers');
        } else if ($tab === 'followers') {
            $followers = $user->followers()->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['following']) && $tab === 'following') {
            $following = $user->following()->orderByDesc('created_at')->paginate(20, ['*'], 'following');
        } else if ($tab === 'following') {
            $following = $user->following()->orderByDesc('created_at')->limit(4)->get();
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
            'challenges' => $challenges,
            'reviews' => $reviews,
            'comments' => $comments,
            'followers' => $followers,
            'following' => $following,
            'tab' => $tab,
            'showHometown' => $showHometown,
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

        return back()->with('status', 'Updated Account Information');
    }

    public function subscribe(Subscribe $request, $return = true)
    {
        if (!Subscriber::where('email', $request['email'])->exists()) {
            $subscriber = new Subscriber;
            $subscriber->email = $request['email'];
            $subscriber->save();
        }

        if ($return) {
            return redirect()->route('subscription_thanks');
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
        User::where('id', Auth::id())->forceDelete();

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
            ->paginate(20);

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
            ->paginate(20);

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
            ->paginate(20);

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
            ->paginate(20);

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
            ->paginate(20);

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
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Your Challenge Entries',
            'content' => $entries,
            'component' => 'entry',
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
        if (!$request->ajax()) {
            return back();
        }

        if (!empty(Follower::where('user_id', $id)->where('follower_id', Auth::id())->first())) {
            return back();
        }

        $follower = new Follower;
        $follower->user_id = $id;
        $follower->follower_id = Auth::id();
        switch (setting('privacy_follow', null, $id)) {
            case null:
            case 'nobody':
                $follower->rejected = true;
                break;
            case 'anybody':
                $follower->accepted = true;
                break;
        }
        $follower->save();

        $user = User::with(['followers'])->where('id', $id)->first();
        $followers = $user->followers()->count();
        $user->followers_quantified = quantify_number($followers);
        $user->save();

        // notify the user that someone started following them or requested to follow them
        if ($id != Auth::id() && in_array(setting('notifications_follower', 'on-site', $id), ['on-site', 'email', 'email-site'])) {
            switch (setting('privacy_follow', null, $id)) {
                case 'request':
                    $user->notify(new UserFollowRequested($follower));
                    break;
                case 'anybody':
                    $user->notify(new UserFollowed($follower));
                    break;
            }
        }

        return false;
    }

    public function unfollow(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        $follower = Follower::where('user_id', $id)->where('follower_id', Auth::id())->first();
        $follower->delete();

        $user = User::with(['followers'])->where('id', $id)->first();
        $followers = $user->followers()->count();
        $user->followers_quantified = quantify_number($followers);
        $user->save();

        return false;
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

        $followers = Follower::where('user_id', Auth::id())->pluck('follower_id');

        $users = User::whereIn('id', $followers)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Users',
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

        $requests = Follower::where('user_id', Auth::id())->where('accepted', false)->where('rejected', false)->pluck('follower_id');

        $users = User::whereIn('id', $requests)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

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

        return back()->with('status', 'Accepted follow request');
    }

    public function rejectFollower($id)
    {
        $follower = Follower::where('user_id', Auth::id())->where('follower_id', $id)->first();
        $follower->rejected = true;
        $follower->save();

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
