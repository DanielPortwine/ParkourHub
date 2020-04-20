<?php

namespace App\Http\Controllers;

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
}
