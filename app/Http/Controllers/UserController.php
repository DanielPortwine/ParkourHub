<?php

namespace App\Http\Controllers;

use App\Subscriber;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = User::where('id', $request['id'])->find(1);
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->save();

        return back()->with('status', 'Updated Account Information');
    }

    public function subscribe (Request $request)
    {
        if (count(Subscriber::where('email', $request['email'])->get()) == 0) {
            $subscriber = new Subscriber;
            $subscriber->email = $request['email'];
            $subscriber->save();
        }

        return redirect()->route('subscription_thanks');
    }
}
