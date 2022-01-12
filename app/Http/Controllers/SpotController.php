<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\Hit;
use App\Http\Requests\AddMovement;
use App\Http\Requests\CreateSpot;
use App\Http\Requests\SearchMap;
use App\Http\Requests\UpdateSpot;
use App\Models\Movement;
use App\Models\MovementCategory;
use App\Models\MovementField;
use App\Notifications\SpotCreated;
use App\Scopes\VisibilityScope;
use App\Models\Spot;
use App\Models\SpotView;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            ->with(['reviews', 'reports', 'hits', 'user'])
            ->hitlist(!empty($request['on_hitlist']) ? true : false)
            ->ticked(!empty($request['ticked_hitlist']) ? true : false)
            ->hometown(!empty($request['in_hometown']) ? true : false)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->following(!empty($request['following']) ? true : false)
            ->movement($request['movement'])
            ->search($request['search'] ?? false)
            ->orderBy($sort[0], $sort[1])
            ->paginate(20)
            ->appends(request()->query());

        return view('content_listings', [
            'title' => 'Spots',
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function view(Request $request, $id, $tab = 'reviews')
    {
        // if coming from a notification, set the notification as read
        if (!empty($request['notification'])) {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if ($notification->id === $request['notification']) {
                    $notification->markAsRead();
                    break;
                }
            }

            return redirect()->route('spot_view', $id);
        }

        $spot = Spot::withTrashed()
            ->with([
                'user',
                'reviews',
                'comments',
                'reports',
                'movements',
                'comments',
                'challenges',
                'workouts',
            ])
            ->where('id', $id)
            ->first();

        if (empty($spot) || ($spot->deleted_at !== null && Auth::id() !== $spot->user_id)) {
            abort(404);
        }

        if ($request->ajax()){
            return view('components.spot', [
                'spot' => $spot,
                'lazyload' => false,
                'map' => true,
            ])->render();
        }

        switch ($tab) {
            case 'reviews':
                $reviews = $spot->reviews()
                    ->with(['reports', 'user'])
                    ->whereNotNull('title')
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                $spotReviewsWithTextCount = $spot->reviews()->withText()->count();
                break;
            case 'comments':
                $comments = $spot->comments()
                    ->with(['reports', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
            case 'challenges':
                $challenges = $spot->challenges()
                    ->withCount('entries')
                    ->with(['entries', 'reports', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
            case 'locals':
                $locals = $spot->locals()
                    ->orderByDesc('name')
                    ->paginate(40, ['*']);
                break;
            case 'workouts':
                $workouts = $spot->workouts()
                    ->withCount('movements')
                    ->with(['movements', 'bookmarks', 'user'])
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*']);
                break;
        }
        $usersViewed = SpotView::where('spot_id', $id)->pluck('user_id')->toArray();
        if (Auth::check() && !in_array(Auth::id(), $usersViewed) && Auth::id() !== $spot->user_id) {
            $view = new SpotView;
            $view->spot_id = $id;
            $view->user_id = Auth::id();
            $view->save();
        }

        if (Auth::check()) {
            $hit = $spot->hits()->where('user_id', Auth::id())->first();
        }

        $localsIDs = $spot->locals()->pluck('id')->toArray();

        $linkableMovements = Movement::with(['type'])
            ->whereHas('type', function($q) {
                return $q->where('name', 'Move');
            })
            ->whereNotIn('id', $spot->movements()->pluck('movements.id')->toArray())
            ->get();
        $movementCategories = Cache::remember('movement_categories_1', 86400, function() {
            return MovementCategory::with(['type'])
                ->whereHas('type', function ($q) {
                    return $q->where('name', 'Move');
                })
                ->get();
        });
        $movementFields = Cache::remember('movement_fields', 86400, function() {
            return MovementField::get();
        });

        if ($tab === 'workouts') {
            $linkableWorkouts = Workout::whereNotIn('id', $spot->workouts()->pluck('workouts.id')->toArray())
                ->get();
        }

        return view('spots.view', [
            'spot' => $spot,
            'request' => $request,
            'localsIDs' => $localsIDs,
            'locals' => $locals ?? null,
            'reviews' => $reviews ?? null,
            'comments' => $comments ?? null,
            'challenges' => $challenges ?? null,
            'workouts' => $workouts ?? null,
            'tab' => $tab,
            'movements' => $spot->movements,
            'hit' => $hit ?? null,
            'linkableMovements' => $linkableMovements ?? null,
            'movementCategories' => $movementCategories,
            'movementFields' => $movementFields,
            'linkableWorkouts' => $linkableWorkouts ?? null,
            'spotReviewsWithTextCount' => $spotReviewsWithTextCount ?? 0,
        ]);
    }

    public function fetch()
    {
        $spots = Spot::get();

        return $spots;
    }

    public function store(CreateSpot $request)
    {
        $spot = new Spot;
        $spot->user_id = Auth::id();
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->visibility = $request['visibility'] ?: 'private';
        $spot->coordinates = $request['coordinates'];
        $latLon = explode(',', $request['lat_lon']);
        $spot->latitude = $latLon[0];
        $spot->longitude = $latLon[1];
        if (!empty($request['image'])) {
            $spot->image = Storage::url($request->file('image')->store('images/spots', 'public'));
        }
        $spot->save();

        // notify followers that user created a spot
        $followers = Auth::user()->followers()->get();
        foreach ($followers as $follower) {
            if (in_array(setting('notifications_new_spot', null, $follower->id), ['on-site', 'email', 'email-site'])) {
                $follower->notify(new SpotCreated($spot));
            }
        }

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
        if (!empty($request['delete'])) {
            return $this->delete($id, $request['redirect']);
        }

        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id != Auth::id()) {
            return redirect()->route('spot_view', $id);
        }
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['image'])) {
            Storage::disk('public')->delete(str_replace('storage/', '', $spot->image));
            $spot->image = Storage::url($request->file('image')->store('images/spots', 'public'));
        }
        $spot->save();

        return back()->with([
            'status' => 'Successfully updated spot',
            'redirect' => $request['redirect'],
        ]);
    }

    public function delete($id, $redirect = null)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id === Auth::id()) {
            $spot->delete();
        } else {
            return redirect()->route('spot_view', $spot->id);
        }

        if (!empty($redirect)) {
            return redirect($redirect)->with('status', 'Successfully deleted spot');
        }

        return back()->with('status', 'Successfully deleted spot');
    }

    public function recover(Request $request, $id)
    {
        $spot = Spot::onlyTrashed()->where('id', $id)->first();

        if (empty($spot) || $spot->user_id !== Auth::id()) {
            return back();
        }

        $spot->restore();

        return back()->with('status', 'Successfully recovered spot.');
    }

    public function remove(Request $request, $id)
    {
        $spot = Spot::withTrashed()->withoutGlobalScope(VisibilityScope::class)->where('id', $id)->first();

        if ($spot->user_id !== Auth::id() && !Auth::user()->hasPermissionTo('remove content')) {
            return back();
        }

        if (!empty($spot->image)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $spot->image));
        }

        $spot->forceDelete();

        return back()->with('status', 'Successfully removed spot forever.');
    }

    public function search(SearchMap $request)
    {
        $spots = Spot::with(['user'])
            ->search($request['search'] ?? false)
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

    public function removeFromHitlist(Request $request, $id)
    {
        if (!$request->ajax()) {
            return back();
        }

        $hit = Hit::where('user_id', Auth::id())->where('spot_id', $id)->first();
        $hit->delete();

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

    public function becomeLocal($id)
    {
        $spot = Spot::where('id', $id)->first();

        $spot->locals()->attach(Auth::id());

        return back();
    }

    public function abandonLocal($id)
    {
        $spot = Spot::where('id', $id)->first();

        $spot->locals()->detach(Auth::id());

        return back();
    }

    public function report($id)
    {
        $spot = Spot::where('id', $id)->first();

        $spot->report();

        return back()->with('status', 'Successfully reported spot');
    }

    public function discardReports(Spot $spot)
    {
        if (!Auth::user()->hasPermissionTo('manage reports') || $spot->user_id === Auth::id()) {
            return back();
        }

        $spot->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function addMovement(AddMovement $request, $id)
    {
        if (!Auth::user()->isPremium()) {
            return back();
        }

        $spot = Spot::with(['movements'])->where('id', $id)->first();
        if (empty($spot->movements()->where('movements.id', $request['movement'])->first())) {
            $spot->movements()->attach($request['movement'], ['user_id' => Auth::id()]);
        }

        return back()->with('status', 'Successfully added movement to spot');
    }

    public function removeMovement($spotID, $movement)
    {
        if (!Auth::user()->isPremium() || Auth::user() !== $movement->spots()->where('id', $spotID)->first()->pivot->user_id) {
            return back();
        }

        $spot = Spot::with(['movements'])->where('id', $spotID)->first();
        if (!empty($spot->movements()->where('movements.id', $movement)->first())) {
            $spot->movements()->detach([$movement, $spotID]);
        }

        return back()->with('status', 'Successfully removed movement from spot');
    }

    public function linkWorkout(Request $request)
    {
        if (!Auth::user()->isPremium()) {
            return back();
        }

        $spot = Spot::with(['workouts'])->where('id', $request['spot'])->first();
        if (empty($spot->workouts()->where('workouts.id', $request['workout'])->first())) {
            $spot->workouts()->attach($request['workout']);
        }

        return back()->with('status', 'Successfully linked workout with spot');
    }
}
