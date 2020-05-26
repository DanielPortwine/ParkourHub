<?php

namespace App\Http\Controllers;

use App\Hit;
use App\Http\Requests\CreateSpot;
use App\Http\Requests\SearchMap;
use App\Http\Requests\UpdateSpot;
use App\Spot;
use App\SpotView;
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

    public function listing(Request $request)
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
            'title' => 'Spots',
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function view(Request $request, $id)
    {
        $spot = Spot::with(['user'])->where('id', $id)->first();

        if ($request->ajax()){
            return view('components.spot', [
                'spot' => $spot,
            ])->render();
        } else {
            if (!empty($request['reviews'])) {
                $reviews = $spot->reviews()->whereNotNull('title')->orderByDesc('created_at')->paginate(20, ['*'], 'reviews')->fragment('reviews');
            } else {
                $reviews = $spot->reviews()->whereNotNull('title')->orderByDesc('created_at')->limit(4)->get();
            }
            if (!empty($request['comments'])) {
                $comments = $spot->comments()->orderByDesc('created_at')->paginate(20, ['*'], 'comments')->fragment('comments');
            } else {
                $comments = $spot->comments()->orderByDesc('created_at')->limit(4)->get();
            }
            if (!empty($request['challenges'])) {
                $challenges = $spot->challenges()->orderByDesc('created_at')->paginate(20, ['*'], 'challenges')->fragment('challenges');
            } else {
                $challenges = $spot->challenges()->orderByDesc('created_at')->limit(4)->get();
            }
            $usersViewed = SpotView::where('spot_id', $id)->pluck('user_id')->toArray();
            if (!in_array(Auth::id(), $usersViewed) && Auth::id() !== $spot->user_id) {
                $view = new SpotView;
                $view->spot_id = $id;
                $view->user_id = Auth::id();
                $view->save();
            }

            return view('spots.view', [
                'spot' => $spot,
                'request' => $request,
                'reviews' => $reviews,
                'comments' => $comments,
                'challenges' => $challenges,
            ]);
        }
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

    public function addToHitlist(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        $hit = new Hit;
        $hit->user_id = Auth::id();
        $hit->spot_id = $id;
        $hit->save();

        return false;
    }

    public function tickOffHitlist(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        $hit = Hit::where('user_id', Auth::id())->where('spot_id', $id)->first();
        $hit->completed_at = Carbon::now();
        $hit->save();

        return false;
    }
}
