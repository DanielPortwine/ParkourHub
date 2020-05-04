<?php

namespace App\Http\Controllers;

use App\Hit;
use App\Spot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpotController extends Controller
{
    public function index()
    {
        return view('spots.index');
    }

    public function view($id)
    {
        $spot = Spot::with(['user'])->where('id', $id)->first();
        $hitlist = Hit::where('user_id', Auth::id())->pluck('completed_at', 'spot_id')->toArray();

        return view('spots.view', ['spot' => $spot, 'hitlist' => $hitlist]);
    }

    public function fetch()
    {
        $spots = Spot::where('private', false)
            ->orWhere('user_id', Auth::id())
            ->get();

        return $spots;
    }

    public function create(Request $request)
    {
        $spot = new Spot;
        $spot->user_id = Auth::id();
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->private = $request['private'] ?: false;
        $spot->coordinates = $request['coordinates'];
        if (!empty($request['image'])) {
            $spot->image = Storage::url($request->file('image')->store('images/spots', 'public'));
        }
        $spot->save();

        return redirect()->route('spots', ['spot' => $spot->id]);
    }

    public function edit($id)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id != Auth::id()) {
            return redirect()->route('spot_view', $id);
        }

        return view('spots.edit', ['spot' => $spot]);
    }

    public function update(Request $request, $id)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id != Auth::id()) {
            return redirect()->route('spot_view', $id);
        }
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->private = $request['private'] ?: false;
        if (!empty($request->image)) {
            Storage::disk('public')->delete($spot->image);
            $spot->image = $request->file('image')->store('images/spots', 'public');
        }
        $spot->save();

        return redirect()->route('spot_view', $spot->id)->with('status', 'Spot updated successfully');
    }

    public function delete($id)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id === Auth::id()) {
            $spot->delete();
        }

        return redirect()->route('spots');
    }

    public function search(Request $request)
    {
        $search = $request['search'];
        $spots = Spot::with(['user'])
            ->where(function($query) use($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->where(function ($query) {
                $query->where('private', false)
                    ->orWhere('user_id', Auth::id());
            })
            ->limit(20)
            ->get();

        return $spots;
    }

    public function addToHitlist($id)
    {
        $hit = new Hit;
        $hit->user_id = Auth::id();
        $hit->spot_id = $id;
        $hit->save();

        return back()->with('status', 'Successfully added spot to your hitlist');
    }

    public function tickOffHitlist($id)
    {
        $hit = Hit::where('user_id', Auth::id())->where('spot_id', $id)->first();
        $hit->completed_at = Carbon::now();
        $hit->save();

        return back()->with('status', 'Successfully ticked off this spot from your hitlist');
    }
}
