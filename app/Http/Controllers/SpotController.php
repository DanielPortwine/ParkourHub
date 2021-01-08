<?php

namespace App\Http\Controllers;

use App\Follower;
use App\Hit;
use App\Http\Requests\AddMovement;
use App\Http\Requests\CreateSpot;
use App\Http\Requests\SearchMap;
use App\Http\Requests\UpdateSpot;
use App\Movement;
use App\MovementCategory;
use App\MovementField;
use App\Notifications\SpotCreated;
use App\Scopes\VisibilityScope;
use App\Spot;
use App\SpotView;
use App\Workout;
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
            ->search($request['search'] ?? '')
            ->hitlist(!empty($request['on_hitlist']) ? true : false)
            ->ticked(!empty($request['ticked_hitlist']) ? true : false)
            ->rating($request['rating'] ?? null)
            ->dateBetween([
                'from' => $request['date_from'] ?? null,
                'to' => $request['date_to'] ?? null
            ])
            ->following(!empty($request['following']) ? true : false)
            ->movement($request['movement'])
            ->orderBy($sort[0], $sort[1])
            ->paginate(20);

        return view('content_listings', [
            'title' => 'Spots',
            'content' => $spots,
            'component' => 'spot',
        ]);
    }

    public function view(Request $request, $id, $tab = null)
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

        $spot = Cache::remember('spot_view_' . $id, 60, function() use($id) {
            $spot = Spot::withTrashed()
                ->with([
                    'user',
                    'reviews',
                    'reports',
                    'movements',
                    'comments',
                    'challenges',
                    'workouts',
                ])
                ->where('id', $id)
                ->first();

            if ($spot->deleted_at !== null && Auth::id() !== $spot->user_id) {
                return [];
            }

            return $spot;
        });

        if ($request->ajax()){
            return view('components.spot', [
                'spot' => $spot,
                'lazyload' => false,
            ])->render();
        } else {
            $reviews = null;
            $comments = null;
            $challenges = null;
            $workouts = null;
            if (!empty($request['reviews']) && ($tab == null || $tab === 'reviews')) {
                $reviews = Cache::remember('spot_reviews_' . $id . '_page_' . $request['reviews'], 60, function() use($spot) {
                    return $spot->reviews()
                        ->with(['reports', 'user'])
                        ->whereNotNull('title')
                        ->orderByDesc('created_at')
                        ->paginate(20, ['*'], 'reviews');
                });
                $spotReviewsWithTextCount = Cache::remember('spot_reviews_with_text_count_' . $id, 60, function() use($spot) {
                    return $spot->reviews()->withText()->count();
                });
            } else if ($tab == null || $tab === 'reviews') {
                $reviews = Cache::remember('spot_reviews_' . $id, 60, function() use($spot) {
                    return $spot->reviews()
                        ->with(['reports', 'user'])
                        ->whereNotNull('title')
                        ->orderByDesc('created_at')
                        ->limit(4)
                        ->get();
                });
                $spotReviewsWithTextCount = Cache::remember('spot_reviews_with_text_count_' . $id, 60, function() use($spot) {
                    return $spot->reviews()->withText()->count();
                });
            }
            if (!empty($request['comments']) && $tab === 'comments') {
                $comments = Cache::remember('spot_comments_' . $id . '_page_' . $request['comments'], 60, function() use($spot) {
                    return $spot->comments()
                        ->with(['reports', 'user'])
                        ->orderByDesc('created_at')
                        ->paginate(20, ['*'], 'comments');
                });
            } else if ($tab === 'comments') {
                $comments = Cache::remember('spot_comments_' . $id, 60, function() use($spot) {
                    return $spot->comments()
                        ->with(['reports', 'user'])
                        ->orderByDesc('created_at')
                        ->limit(4)
                        ->get();
                });
            }
            if (!empty($request['challenges']) && $tab === 'challenges') {
                $challenges = Cache::remember('spot_challenges_' . $id . '_page_' . $request['challenges'], 60, function() use($spot) {
                    return $spot->challenges()
                        ->withCount('entries')
                        ->with(['entries', 'reports', 'user'])
                        ->orderByDesc('created_at')
                        ->paginate(20, ['*'], 'challenges');
                });
            } else if ($tab === 'challenges') {
                $challenges = Cache::remember('spot_challenges_' . $id, 60, function() use($spot) {
                    return $spot->challenges()
                        ->withCount('entries')
                        ->with(['entries', 'reports', 'user'])
                        ->orderByDesc('created_at')
                        ->limit(4)
                        ->get();
                });
            }
            if (!empty($request['workouts']) && $tab === 'workouts') {
                $workouts = Cache::remember('spot_workouts_' . $id . '_page_' . $request['workouts'], 60, function() use($spot) {
                    return $spot->workouts()
                        ->withCount('movements')
                        ->with(['movements', 'bookmarks', 'user'])
                        ->orderByDesc('created_at')
                        ->paginate(20, ['*'], 'workouts');
                });
            } else if ($tab === 'workouts') {
                $workouts = Cache::remember('spot_workouts_' . $id, 60, function() use($spot) {
                    return $spot->workouts()
                        ->withCount('movements')
                        ->with(['movements', 'bookmarks', 'user'])
                        ->orderByDesc('created_at')
                        ->limit(4)
                        ->get();
                });
            }
            $usersViewed = SpotView::where('spot_id', $id)->pluck('user_id')->toArray();
            if (Auth::check() && !in_array(Auth::id(), $usersViewed) && Auth::id() !== $spot->user_id) {
                $view = new SpotView;
                $view->spot_id = $id;
                $view->user_id = Auth::id();
                $view->save();
            }

            $hit = null;
            if (Auth::check()) {
                $hit = $spot->hits()->where('user_id', Auth::id())->first();
            }

            $linkableMovements = Cache::remember('spot_linkable_movements_' . $id, 60, function() use($spot) {
                return Movement::where('type_id', 1)
                    ->whereNotIn('id', $spot->movements()->pluck('movements.id')->toArray())
                    ->get();
            });
            $movementCategories = Cache::remember('movement_categories_1', 86400, function() {
                return MovementCategory::where('type_id', 1)->get();
            });
            $movementFields = Cache::remember('movement_fields', 86400, function() {
                return MovementField::get();
            });
            $linkableWorkouts = null;
            if ($tab === 'workouts') {
                $linkableWorkouts = Cache::remember('spot_linkable_workouts_' . $id, 60, function() {
                    return Workout::get();
                });
            }

            return view('spots.view', [
                'spot' => $spot,
                'request' => $request,
                'reviews' => $reviews,
                'comments' => $comments,
                'challenges' => $challenges,
                'workouts' => $workouts,
                'tab' => $tab,
                'movements' => $spot->movements,
                'hit' => $hit,
                'linkableMovements' => $linkableMovements,
                'movementCategories' => $movementCategories,
                'movementFields' => $movementFields,
                'linkableWorkouts' => $linkableWorkouts,
                'spotReviewsWithTextCount' => $spotReviewsWithTextCount ?? 0,
            ]);
        }
    }

    public function fetch()
    {
        $spots = Spot::get();

        return $spots;
    }

    public function create(CreateSpot $request)
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
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id != Auth::id()) {
            return redirect()->route('spot_view', $id);
        }
        $spot->name = $request['name'];
        $spot->description = $request['description'];
        $spot->visibility = $request['visibility'] ?: 'private';
        if (!empty($request['image'])) {
            Storage::disk('public')->delete($spot->image);
            $spot->image = Storage::url($request->file('image')->store('images/spots', 'public'));
        }
        $spot->save();

        return back()->with('status', 'Spot updated successfully');
    }

    public function delete($id, $redirect = null)
    {
        $spot = Spot::where('id', $id)->first();
        if ($spot->user_id === Auth::id()) {
            $spot->delete();
        }
        if (empty($redirect)) {
            $redirect = redirect()->route('spot_listing');
        }

        return $redirect->with('status', 'Successfully deleted spot');
    }

    public function recover(Request $request, $id)
    {
        $spot = Spot::onlyTrashed()->where('id', $id)->first();

        if ($spot->user_id !== Auth::id()) {
            return back();
        }

        $spot->restore();

        return back()->with('status', 'Successfully recovered spot.');
    }

    public function remove(Request $request, $id)
    {
        $spot = Spot::onlyTrashed()->where('id', $id)->first();

        if ($spot->user_id !== Auth::id()) {
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
            ->search($request['search'] ?? null)
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

    public function report(Spot $spot)
    {
        $spot->report();

        return back()->with('status', 'Successfully reported spot');
    }

    public function discardReports(Spot $spot)
    {
        $spot->discardReports();

        return back()->with('status', 'Successfully discarded reports against this content');
    }

    public function addMovement(AddMovement $request, $id)
    {
        $spot = Spot::with(['movements'])->where('id', $id)->first();
        if (empty($spot->movements()->where('movements.id', $request['movement'])->first())) {
            $spot->movements()->attach($request['movement'], ['user_id' => Auth::id()]);
        }

        return back()->with('status', 'Successfully added movement to spot');
    }

    public function removeMovement($spotID, $movement)
    {
        $spot = Spot::with(['movements'])->where('id', $spotID)->first();
        if (!empty($spot->movements()->where('movements.id', $movement)->first())) {
            $spot->movements()->detach([$movement, $spotID]);
        }

        return back()->with('status', 'Successfully removed movement from spot');
    }

    public function linkWorkout(Request $request)
    {
        $spot = Spot::with(['workouts'])->where('id', $request['spot'])->first();
        $spot->workouts()->attach($request['workout']);

        return back()->with('status', 'Successfully linked workout with spot');
    }
}
