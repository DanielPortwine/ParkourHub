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

    public function spots()
    {
        $spots = Spot::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->paginate(20);

        return view('content_listings', [
            'page' => Auth::user()->name . '\'s Spots',
            'title' => 'Your Spots',
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function hitlist()
    {
        $hits = Hit::whereHas('spot')->where('user_id', Auth::id())->whereNull('completed_at')->orderByDesc('created_at')->paginate(20);

        return view('user.hitlist', [
            'hits' => $hits,
        ]);
    }

    public function hitlistCompleted()
    {
        $hits = Hit::whereHas('spot')->where('user_id', Auth::id())->whereNotNull('completed_at')->orderByDesc('completed_at')->paginate(20);

        return view('user.hitlist', [
            'hits' => $hits,
        ]);
    }

    public function reviews()
    {
        $reviews = Review::where('user_id', Auth::id())->orderByDesc('updated_at')->paginate(40);

        return view('content_listings', [
            'page' => Auth::user()->name . '\'s Reviews',
            'title' => 'Your Reviews',
            'content' => $reviews,
            'component' => 'review',
            'options' => ['user' => true],
        ]);
    }

    public function challenges()
    {
        $challenges = Challenge::where('user_id', Auth::id())->orderByDesc('updated_at')->paginate(20);

        return view('content_listings', [
            'page' => Auth::user()->name . '\'s Challenges',
            'title' => 'Your Challenges',
            'content' => $challenges,
            'component' => 'challenge',
        ]);
    }

    public function entries()
    {
        $entries = ChallengeEntry::where('user_id', Auth::id())->paginate(20);

        return view('content_listings', [
            'page' => Auth::user()->name . '\'s Challenge Entries',
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
