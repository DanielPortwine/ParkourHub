<?php

namespace App\Http\Controllers;

use App\Follower;
use App\Hit;
use App\Http\Requests\AddMovement;
use App\Http\Requests\CreateSpot;
use App\Http\Requests\SearchMap;
use App\Http\Requests\UpdateSpot;
use App\Notifications\SpotCreated;
use App\Spot;
use App\SpotView;
use App\Workout;
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
            ->where(function($q) {
                $q->where('visibility', 'public')
                    ->orWhere(function($q1) {
                        $q1->where('visibility', 'follower')
                            ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                    })
                    ->orWhere('user_id', Auth::id());
            })
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

        $spot = Spot::with([
            'user',
            'movements' => function($query) {
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('movements.user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('movements.user_id', Auth::id());
                });
            },
            'reviews',
            'comments' => function($query) {
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('user_id', Auth::id());
                });
            },
            'challenges' => function($query) {
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('user_id', Auth::id());
                });
            },
            'workouts' => function($query) {
                $query->where(function($q) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('user_id', Auth::id());
                });
            },
        ])
            ->where(function($q) {
                if (Auth::id() !== 1) {
                    $q->where('visibility', 'public')
                        ->orWhere(function($q1) {
                            $q1->where('visibility', 'follower')
                                ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                        })
                        ->orWhere('user_id', Auth::id());
                }
            })
            ->where('id', $id)
            ->first();

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
                $reviews = $spot->reviews()->whereNotNull('title')->orderByDesc('created_at')->paginate(20, ['*'], 'reviews');
            } else if ($tab == null || $tab === 'reviews') {
                $reviews = $spot->reviews()->whereNotNull('title')->orderByDesc('created_at')->limit(4)->get();
            }
            if (!empty($request['comments']) && $tab === 'comments') {
                $comments = $spot->comments()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*'], 'comments');
            } else if ($tab === 'comments') {
                $comments = $spot->comments()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->limit(4)
                    ->get();
            }
            if (!empty($request['challenges']) && $tab === 'challenges') {
                $challenges = $spot->challenges()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*'], 'challenges');
            } else if ($tab === 'challenges') {
                $challenges = $spot->challenges()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->limit(4)
                    ->get();
            }
            if (!empty($request['workouts']) && $tab === 'workouts') {
                $workouts = $spot->workouts()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->paginate(20, ['*'], 'workouts');
            } else if ($tab === 'workouts') {
                $workouts = $spot->workouts()
                    ->where(function($q) {
                        if (Auth::id() !== 1) {
                            $q->where('visibility', 'public')
                                ->orWhere(function($q1) {
                                    $q1->where('visibility', 'follower')
                                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                                })
                                ->orWhere('user_id', Auth::id());
                        }
                    })
                    ->orderByDesc('created_at')
                    ->limit(4)
                    ->get();
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
                $hit = Auth()->user()->hits->where('spot_id', $id)->first();
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
            ]);
        }
    }

    public function fetch()
    {
        $spots = Spot::where(function($q) {
            $q->where('visibility', 'public')
                ->orWhere(function($q1) {
                    $q1->where('visibility', 'follower')
                        ->whereIn('user_id', Follower::where('follower_id', Auth::id())->pluck('user_id')->toArray());
                })
                ->orWhere('user_id', Auth::id());
        })
            ->get();

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

    public function search(SearchMap $request)
    {
        $search = $request['search'];
        $spots = Spot::with(['user'])
            ->where(function($query) use($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->where(function($query) {
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

    public function getSpots(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $results = [];
        $workoutSpots = Workout::where('id', $request['workout'])->first()->spots()->pluck('spots.id')->toArray();
        $spots = Spot::whereNotIn('id', $workoutSpots)->get();

        foreach ($spots as $spot) {
            $results[] = [
                'id' => $spot->id,
                'text' => $spot->name,
            ];
        }

        return $results;
    }
}
