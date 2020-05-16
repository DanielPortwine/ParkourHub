<?php

namespace App\Http\Controllers;

use App\Hit;
use App\Http\Requests\CreateSpot;
use App\Http\Requests\SearchMap;
use App\Http\Requests\UpdateSpot;
use App\Spot;
use App\SpotView;
use Carbon\Carbon;
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
        $usersViewed = SpotView::where('spot_id', $id)->pluck('user_id')->toArray();
        if (!in_array(Auth::id(), $usersViewed) && Auth::id() !== $spot->user_id) {
            $view = new SpotView;
            $view->spot_id = $id;
            $view->user_id = Auth::id();
            $view->save();
        }
        $views = count($spot->views);

        return view('spots.view', [
            'spot' => $spot,
            'hitlist' => $hitlist,
            'views' => $views,
        ]);
    }

    public function fetch()
    {
        $spots = Spot::where('private', false)
            ->orWhere('user_id', Auth::id())
            ->get();

        return $spots;
    }

    public function create(CreateSpot $request)
    {
        $spot = new Spot;
        $spot->user_id = Auth::id();
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->private = $request['private'] ?: false;
        $spot->coordinates = $request['coordinates'];
        $latLon = explode(',', $request['lat_lon']);
        $spot->latitude = $latLon[0];
        $spot->longitude = $latLon[1];
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

    public function update(UpdateSpot $request, $id)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id != Auth::id()) {
            return redirect()->route('spot_view', $id);
        }
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->private = $request['private'] ?: false;
        if (!empty($request['image'])) {
            Storage::disk('public')->delete($spot->image);
            $spot->image = $request->file('image')->store('images/spots', 'public');
        }
        $spot->save();

        return back()->with('status', 'Spot updated successfully');
    }

    public function delete($id)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id === Auth::id()) {
            $spot->delete();
        }

        return redirect()->route('spots');
    }

    public function search(SearchMap $request)
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
