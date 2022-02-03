<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Scopes\BannedUserScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanController extends Controller
{
    public function index()
    {
        $bans = User::withoutGlobalScope(BannedUserScope::class)->whereNotNull('banned_at')->paginate(20);

        return view('content_listings', [
            'title' => 'Banned Users',
            'content' => $bans,
            'component' => 'user',
        ]);
    }

    public function create($id)
    {
        if (!Auth::user()->hasPermissionTo('manage bans')) {
            abort(404);
        }

        $user = User::where('id', $id)->first();
        $user->banned_at = now();
        $user->save();

        return back()->with('status', 'Successfully banned user');
    }

    public function view()
    {
        if (Auth::check() && Auth::user()->banned_at !== null) {
            return view('banned');
        }

        abort(404);
    }

    public function delete($id)
    {
        if (!Auth::user()->hasPermissionTo('manage bans')) {
            abort(404);
        }

        $user = User::where('id', $id)->first();
        $user->banned_at = null;
        $user->save();

        return back()->with('status', 'Successfully unbanned user');
    }
}
