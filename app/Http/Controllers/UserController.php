<?php

namespace App\Http\Controllers;

use App\Challenge;
use App\ChallengeEntry;
use App\Hit;
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

    public function update(Request $request)
    {
        $user = User::where('id', Auth::id())->first();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->save();

        if  ($request['subscribed'] == true) {
            $this->subscribe($request, false);
        } else {
            $this->unsubscribe();
        }

        return back()->with('status', 'Updated Account Information');
    }

    public function subscribe(Request $request, $return = true)
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
        $spots = Spot::where('user_id', Auth::id())->orderBy('updated_at', 'desc')->get();

        return view('user.spots', ['spots' => $spots]);
    }

    public function hitlist()
    {
        $hits = Hit::with(['spot'])->where('user_id', Auth::id())->get();
        $hitsToTickOff = $hits->whereNull('completed_at')->sortByDesc('created_at');
        $hitsTickedOff = $hits->whereNotNull('completed_at')->sortByDesc('completed_at');

        return view('user.hitlist', [
            'hitsToTickOff' => $hitsToTickOff,
            'hitsTickedOff' => $hitsTickedOff,
        ]);
    }

    public function challenges()
    {
        $challenges = Challenge::where('user_id', Auth::id())->get();

        return view('user.challenges', ['challenges' => $challenges]);
    }

    public function entries()
    {
        $entries = ChallengeEntry::where('user_id', Auth::id())->get();

        return view('user.entries', ['entries' => $entries]);
    }
}
