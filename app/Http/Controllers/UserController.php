<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\Hit;
use App\Http\Requests\Subscribe;
use App\Http\Requests\UpdateUser;
use App\Review;
use App\Spot;
use App\Subscriber;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = User::where('id', $id)->first();

        $spots = $reviews = $comments = $challenges = null;
        if (!empty($request['spots']) && ($tab == null || $tab === 'spots')) {
            $spots = $user->spots()->where('private', false)->orderByDesc('rating')->paginate(20, ['*'], 'spots')->fragment('content');
        } else if ($tab == null || $tab === 'spots') {
            $spots = $user->spots()->where('private', false)->orderByDesc('rating')->limit(4)->get();
        }
        if (!empty($request['reviews']) && $tab === 'reviews') {
            $reviews = $user->reviews()->whereNotNull('title')->orderByDesc('created_at')->paginate(20, ['*'], 'reviews')->fragment('content');
        } else if ($tab == null || $tab === 'reviews') {
            $reviews = $user->reviews()->whereNotNull('title')->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['comments']) && $tab === 'comments') {
            $comments = $user->spotComments()->orderByDesc('created_at')->paginate(20, ['*'], 'comments')->fragment('content');
        } else if ($tab === 'comments') {
            $comments = $user->spotComments()->orderByDesc('created_at')->limit(4)->get();
        }
        if (!empty($request['challenges']) && $tab === 'challenges') {
            $challenges = $user->challenges()->orderByDesc('created_at')->paginate(20, ['*'], 'challenges')->fragment('content');
        } else if ($tab === 'challenges') {
            $challenges = $user->challenges()->orderByDesc('created_at')->limit(4)->get();
        }

        return view('user.view', [
            'user' => $user,
            'request' => $request,
            'spots' => $spots,
            'challenges' => $challenges,
            'reviews' => $reviews,
            'comments' => $comments,
            'tab' => $tab,
        ]);
    }

    public function manage()
    {
        $user = Auth::user();
        $subscribed = Subscriber::where('email', $user->email)->exists();

        return view('user.manage', [
            'user' => Auth::user(),
            'subscribed' => $subscribed,
        ]);
    }

    public function update(UpdateUser $request)
    {
        $user = User::where('id', Auth::id())->first();
        $user->name = $request['name'];
        $user->email = $request['email'];
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
                $user->name = 'ParkourHubUser' . Auth::id();
                break;
            case 'email':
                $user->email = Auth::id() . '@parkourhub.user';
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

        $spots = Spot::withCount('views')
            ->where('user_id', Auth::id())
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

        $reviews = Review::where('user_id', Auth::id())
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

        $challenges = Challenge::withCount('entries')
            ->where('user_id', Auth::id())
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
        $entries = ChallengeEntry::where('user_id', Auth::id())
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

        $bounding = explode(',', Auth::user()->hometown_bounding);

        return count($bounding) === 4 ? $bounding : null;
    }
}
