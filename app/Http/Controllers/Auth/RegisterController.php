<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscribe;
use App\Models\Subscriber;
use App\Providers\RouteServiceProvider;
use App\Rules\NotAutoUsername;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['nullable', 'string', 'max:25', 'unique:users', new NotAutoUsername],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'guidelines' => 'accepted',
            'newsletter' => 'boolean',
        ], [
            'guidelines.accepted' => 'You must accept the Community Guidelines.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'] ?: null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'settings' => '{}',
            'accepted_community_guidelines' => $data['guidelines'] ?: false,
        ]);

        if (empty($data['name'])) {
            $user->name = 'User' . $user->id;
            $user->save();
        }

        if  (!empty($data['newsletter'])) {
            $subscription = Http::convertkit()->post('forms/3093513/subscribe', [
                'api_key' => env('CONVERTKIT_KEY'),
                'email' => $user->email,
            ])->object()->subscription;
            $user->subscriber_id = $subscription->subscriber->id;
            $user->save();
        }

        return $user;
    }
}
