@extends('layouts.app')

@push('title'){{ $title }} | @endpush

@section('description')All {{ $title }} on Parkour Hub.@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(!empty($_GET['search']))
        <div class="container bg-grey pt-2 d-block d-md-none">
            <form class="w-100" id="site-search-form" action="{{ strpos(Route::currentRouteName(), '_listing') > 0 ? route(Route::currentRouteName()) : route('spot_listing') }}" method="GET">
                <div class="input-group w-100">
                    <input type="text" class="form-control @error('search') is-invalid @enderror" name="search" placeholder="Search" aria-label="from" aria-describedby="from" value="{{ $_GET['search'] ?? '' }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-green input-group-text" title="Search"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
        <nav class="navbar navbar-expand-md navbar-dark text-white bg-grey shadow-sm">
            <div class="container">
                <ul class="navbar-nav flex-row justify-content-around w-100">
                    <li class="nav-item px-2">
                        <a class="nav-link @if(Route::currentRouteName() === 'spot_listing')active @endif" href="{{ route('spot_listing', ['search' => $_GET['search'] ?? '']) }}">
                            <i class="fa fa-map-marker nav-icon"></i>
                            <span class="d-none d-lg-inline">Spots</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if(Route::currentRouteName() === 'challenge_listing')active @endif" href="{{ route('challenge_listing', ['search' => $_GET['search'] ?? '']) }}">
                            <i class="fa fa-bullseye nav-icon"></i>
                            <span class="d-none d-lg-inline">Challenges</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if(Route::currentRouteName() === 'event_listing')active @endif" href="{{ route('event_listing', ['search' => $_GET['search'] ?? '']) }}">
                            <i class="fa fa-map-marked nav-icon"></i>
                            <span class="d-none d-lg-inline">Events</span>
                        </a>
                    </li>
                    @premium
                        <li class="nav-item px-2">
                            <a class="nav-link @if(Route::currentRouteName() === 'workout_listing')active @endif" href="{{ route('workout_listing', ['search' => $_GET['search'] ?? '']) }}">
                                <i class="fa fa-running nav-icon"></i>
                                <span class="d-none d-lg-inline">Workouts</span>
                            </a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link @if(Route::currentRouteName() === 'movement_listing')active @endif" href="{{ route('movement_listing', ['search' => $_GET['search'] ?? '']) }}">
                                <i class="fa fa-child nav-icon"></i>
                                <span class="d-none d-lg-inline">Movements</span>
                            </a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link @if(Route::currentRouteName() === 'equipment_listing')active @endif" href="{{ route('equipment_listing', ['search' => $_GET['search'] ?? '']) }}">
                                <i class="fa fa-dumbbell nav-icon"></i>
                                <span class="d-none d-lg-inline">Equipment</span>
                            </a>
                        </li>
                    @endpremium
                    @auth
                        <li class="nav-item px-2">
                            <a class="nav-link @if(Route::currentRouteName() === 'user_listing')active @endif" href="{{ route('user_listing', ['search' => $_GET['search'] ?? '']) }}">
                                <i class="fa fa-user nav-icon"></i>
                                <span class="d-none d-lg-inline">Users</span>
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </nav>
    @endif
    @if(Route::currentRouteName() === 'report_listing')
        <nav class="navbar navbar-expand-md navbar-dark text-white bg-grey shadow-sm">
            <div class="container">
                <ul class="navbar-nav flex-row justify-content-around w-100">
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'spot')active @endif" href="{{ route('report_listing', 'spot') }}">
                            <i class="fa fa-map-marker nav-icon"></i>
                            <span class="d-none d-lg-inline">Spots</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'review')active @endif" href="{{ route('report_listing', 'review') }}">
                            <i class="fa fa-star nav-icon"></i>
                            <span class="d-none d-lg-inline">Reviews</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'comment')active @endif" href="{{ route('report_listing', 'comment') }}">
                            <i class="fa fa-comment nav-icon"></i>
                            <span class="d-none d-lg-inline">Comments</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'event')active @endif" href="{{ route('report_listing', 'event') }}">
                            <i class="fa fa-map-marked nav-icon"></i>
                            <span class="d-none d-lg-inline">Events</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'challenge')active @endif" href="{{ route('report_listing', 'challenge') }}">
                            <i class="fa fa-bullseye nav-icon"></i>
                            <span class="d-none d-lg-inline">Challenges</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($component === 'entry')active @endif" href="{{ route('report_listing', 'entry') }}">
                            <i class="fa fa-trophy nav-icon"></i>
                            <span class="d-none d-lg-inline">Entries</span>
                        </a>
                    </li>
                    @premium
                        <li class="nav-item px-2">
                            <a class="nav-link @if($component === 'workout')active @endif" href="{{ route('report_listing', 'workout') }}">
                                <i class="fa fa-running nav-icon"></i>
                                <span class="d-none d-lg-inline">Workouts</span>
                            </a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link @if($component === 'movement')active @endif" href="{{ route('report_listing', 'movement') }}">
                                <i class="fa fa-child nav-icon"></i>
                                <span class="d-none d-lg-inline">Movements</span>
                            </a>
                        </li>
                        <li class="nav-item px-2">
                            <a class="nav-link @if($component === 'equipment')active @endif" href="{{ route('report_listing', 'equipment') }}">
                                <i class="fa fa-dumbbell nav-icon"></i>
                                <span class="d-none d-lg-inline">Equipment</span>
                            </a>
                        </li>
                    @endpremium
                </ul>
            </div>
        </nav>
    @endif
    <div class="container-fluid pt-4 position-relative">
        <div class="row">
            <div class="col">
                <h1 class="sedgwick text-center pb-3">{{ $title }}</h1>
            </div>
        </div>
        @if($component !== 'user')
            <div class="row mb-3">
                <div class="col">
                    <div class="card">
                        <div class="card-header bg-green sedgwick @if(
                                empty($_GET) ||
                                (count($_GET) === 1 && isset($_GET['search'])) ||
                                (count($_GET) === 1 && isset($_GET['personal'])) ||
                                (count($_GET) === 1 && isset($_GET['page'])) ||
                                (count($_GET) === 2 && isset($_GET['search']) && isset($_GET['page'])) ||
                                (count($_GET) === 2 && isset($_GET['personal']) && isset($_GET['page'])) ||
                                (count($_GET) === 3 && isset($_GET['search']) && isset($_GET['personal']) && isset($_GET['page']))
                            )
                            card-hidden-body
                        @endif">
                            <div class="row">
                                <div class="col">
                                    Filters
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-grey text-white">
                            <form method="GET">
                                @if(!empty($_GET['search']))
                                    <input type="hidden" name="search" value="{{ $_GET['search'] }}">
                                @endif
                                <div class="row">
                                    <div class="col-auto pb-3">
                                        <label><strong>Created Between: </strong></label>
                                        <div>
                                            <input type="date" name="date_from" value="{{ $_GET['date_from'] ?? '' }}">
                                            <input type="date" name="date_to" value="{{ $_GET['date_to'] ?? '' }}">
                                        </div>
                                    </div>
                                    @if(in_array($component, ['spot', 'challenge', 'event', 'workout']) && Auth::check())
                                        <div class="col-auto pb-3">
                                            <label><strong>Following</strong></label>
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" name="following" id="following" {{ !empty($_GET['following']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="following"></label>
                                            </div>
                                        </div>
                                    @endif
                                    @if($component === 'spot')
                                        <div class="col-auto pb-3">
                                            <label><strong>Rating</strong></label>
                                            <input type="hidden" id="rating" name="rating" value="{{ $_GET['rating'] ?? '0' }}">
                                            <div>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                            </div>
                                        </div>
                                        @auth
                                            <div class="col-auto pb-3">
                                                <label><strong>Move</strong></label>
                                                <select class="select2-5-results" name="movement">
                                                    <option></option>
                                                    @foreach($moves as $move)
                                                        <option value="{{ $move->id }}" @if(($_GET['movement'] ?? '') == $move->id)selected @endif>{{ $move->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if(empty($hitlist))
                                                <div class="col-auto pb-3">
                                                    <label><strong>On Hitlist</strong></label>
                                                    <div class="form-check text-center">
                                                        <input class="form-check-input" type="checkbox" name="on_hitlist" id="on-hitlist" {{ !empty($_GET['on_hitlist']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="on-hitlist"></label>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-auto pb-3">
                                                <label><strong>Ticked Off</strong></label>
                                                <div class="form-check text-center">
                                                    <input class="form-check-input" type="checkbox" name="ticked_hitlist" id="ticked-hitlist" {{ !empty($_GET['ticked_hitlist']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="ticked-hitlist"></label>
                                                </div>
                                            </div>
                                            @if(!empty(Auth()->user()->hometown_bounding))
                                                <div class="col-auto pb-3">
                                                    <label><strong>In Hometown</strong></label>
                                                    <div class="form-check text-center">
                                                        <input class="form-check-input" type="checkbox" name="in_hometown" id="in_hometown" {{ !empty($_GET['in_hometown']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="in_hometown"></label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endauth
                                    @elseif($component === 'challenge')
                                        @auth
                                            <div class="col-auto pb-3">
                                                <label><strong>Entered</strong></label>
                                                <div class="form-check text-center">
                                                    <input class="form-check-input" type="checkbox" name="entered" id="entered"  {{ !empty($_GET['entered']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="entered"></label>
                                                </div>
                                            </div>
                                            @if(!empty(Auth()->user()->hometown_bounding))
                                                <div class="col-auto pb-3">
                                                    <label><strong>In Hometown</strong></label>
                                                    <div class="form-check text-center">
                                                        <input class="form-check-input" type="checkbox" name="in_hometown" id="in_hometown" {{ !empty($_GET['in_hometown']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="in_hometown"></label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endauth
                                        <div class="col-auto pb-3">
                                            <label><strong>Difficulty</strong></label>
                                            <input type="hidden" id="difficulty" name="difficulty" value="{{ $_GET['difficulty'] ?? '0' }}">
                                            <div>
                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-1"></i>
                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-2"></i>
                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-3"></i>
                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-4"></i>
                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-5"></i>
                                            </div>
                                        </div>
                                    @elseif($component === 'event')
                                        <div class="col-auto pb-3">
                                            <label><strong>Date Between: </strong></label>
                                            <div>
                                                <input type="date" name="event_date_from" value="{{ $_GET['event_date_from'] ?? '' }}">
                                                <input type="date" name="event_date_to" value="{{ $_GET['event_date_to'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="col-auto pb-3">
                                            <label><strong>Historic</strong></label>
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" name="historic" id="historic" {{ !empty($_GET['historic']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="historic"></label>
                                            </div>
                                        </div>
                                        @if(Auth::check())
                                            <div class="col-auto pb-3">
                                                <label><strong>Attending</strong></label>
                                                <div class="form-check text-center">
                                                    <input class="form-check-input" type="checkbox" name="attending" id="attending" {{ !empty($_GET['attending']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="attending"></label>
                                                </div>
                                            </div>
                                            <div class="col-auto pb-3">
                                                <label><strong>Applied</strong></label>
                                                <div class="form-check text-center">
                                                    <input class="form-check-input" type="checkbox" name="applied" id="applied" {{ !empty($_GET['applied']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="applied"></label>
                                                </div>
                                            </div>
                                        @endif
                                        @if(!empty(Auth()->user()->hometown_bounding))
                                            <div class="col-auto pb-3">
                                                <label><strong>In Hometown</strong></label>
                                                <div class="form-check text-center">
                                                    <input class="form-check-input" type="checkbox" name="in_hometown" id="in_hometown" {{ !empty($_GET['in_hometown']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="in_hometown"></label>
                                                </div>
                                            </div>
                                        @endif
                                    @elseif($component === 'entry' && Auth::check())
                                        <div class="col-auto pb-3">
                                            <label><strong>Winner</strong></label>
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" name="winner" id="winner" {{ ($_GET['winner'] ?? '') === 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="winner"></label>
                                            </div>
                                        </div>
                                    @elseif($component === 'review')
                                        <div class="col-auto pb-3">
                                            <label><strong>Rating</strong></label>
                                            <input type="hidden" id="rating" name="rating" value="{{ $_GET['rating'] ?? '0' }}">
                                            <div>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                                <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                            </div>
                                        </div>
                                    @elseif($component === 'movement')
                                        <div class="col-auto pb-3">
                                            <label><strong>Type</strong></label>
                                            <select class="select2-5-results" name="movementType">
                                                <option></option>
                                                <option value="1" @if(($_GET['movementType'] ?? '') == 1)selected @endif>Move</option>
                                                <option value="2" @if(($_GET['movementType'] ?? '') == 2)selected @endif>Exercise</option>
                                            </select>
                                        </div>
                                        <div class="col-auto pb-3">
                                            <label><strong>Category</strong></label>
                                            <select class="select2-5-results" name="category">
                                                <option></option>
                                                @foreach($movementCategories as $category)
                                                    <option value="{{ $category->id }}" @if(($_GET['category'] ?? '') == $category->id)selected @endif>{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-auto pb-3">
                                            <label><strong>Equipment</strong></label>
                                            <select class="select2-5-results" name="equipment">
                                                <option></option>
                                                @foreach($equipments as $equipment)
                                                    <option value="{{ $equipment->id }}" @if(($_GET['equipment'] ?? '') == $equipment->id)selected @endif>{{ $equipment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @elseif($component === 'workout')
                                        <div class="col-auto pb-3">
                                            <label><strong>Bookmarked</strong></label>
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" name="bookmarked" id="bookmarked" {{ ($_GET['bookmarked'] ?? '') === 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="bookmarked"></label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-auto pb-3">
                                        <label><strong>Sort</strong></label>
                                        <div>
                                            <select name="sort" class="select2-no-search">
                                                <option value="date_desc" @if(($_GET['sort'] ?? '') === 'date_desc')selected @endif>Newest</option>
                                                <option value="date_asc" @if(($_GET['sort'] ?? '') === 'date_asc')selected @endif>Oldest</option>
                                                <option value="updated_desc" @if(($_GET['sort'] ?? '') === 'updated_desc')selected @endif>Newest Updated</option>
                                                <option value="updated_asc" @if(($_GET['sort'] ?? '') === 'updated_asc')selected @endif>Oldest Updated</option>
                                                @if($component === 'spot' || $component === 'review')
                                                    <option value="rating_desc" @if(($_GET['sort'] ?? '') === 'rating_desc')selected @endif>Highest Rated</option>
                                                    <option value="rating_asc" @if(($_GET['sort'] ?? '') === 'rating_asc')selected @endif>Lowest Rated</option>
                                                    <option value="views_desc" @if(($_GET['sort'] ?? '') === 'views_desc')selected @endif>Most Viewed</option>
                                                    <option value="views_asc" @if(($_GET['sort'] ?? '') === 'views_asc')selected @endif>Least Viewed</option>
                                                @elseif($component === 'challenge')
                                                    <option value="difficulty_desc" @if(($_GET['sort'] ?? '') === 'difficulty_desc')selected @endif>Most Difficult</option>
                                                    <option value="difficulty_asc" @if(($_GET['sort'] ?? '') === 'difficulty_asc')selected @endif>Least Difficult</option>
                                                    <option value="entries_desc" @if(($_GET['sort'] ?? '') === 'entries_desc')selected @endif>Most Entries</option>
                                                    <option value="entries_asc" @if(($_GET['sort'] ?? '') === 'entries_asc')selected @endif>Least Entries</option>
                                                @elseif($component === 'event')
                                                    @if(empty($_GET['historic']))
                                                        <option value="eventdate_asc" @if(($_GET['sort'] ?? '') === 'eventdate_asc' || empty($_GET['sort']))selected @endif>Soonest</option>
                                                        <option value="eventdate_desc" @if(($_GET['sort'] ?? '') === 'eventdate_desc')selected @endif>Furthest</option>
                                                    @else
                                                        <option value="eventdate_desc" @if(($_GET['sort'] ?? '') === 'eventdate_desc')selected @endif>Latest</option>
                                                        <option value="eventdate_asc" @if(($_GET['sort'] ?? '') === 'eventdate_asc')selected @endif>Furthest</option>
                                                    @endif
                                                    <option value="attendees_desc" @if(($_GET['sort'] ?? '') === 'attendees_desc')selected @endif>Most Attendees</option>
                                                    <option value="attendees_asc" @if(($_GET['sort'] ?? '') === 'attendees_asc')selected @endif>Least Attendees</option>
                                                    <option value="spots_desc" @if(($_GET['sort'] ?? '') === 'spots_desc')selected @endif>Most Spots</option>
                                                    <option value="spots_asc" @if(($_GET['sort'] ?? '') === 'spots_asc')selected @endif>Least Spots</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <button class="btn btn-green" type="submit">Filter</button>
                                        <a class="btn btn-link" href="?{{ !empty($_GET['search']) ? 'search=' . $_GET['search'] : '' }}">Clear</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{ $content->links() }}
        @foreach($content->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $card)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.' . $component, array_merge([$component => $card], $options ?? []))
                    </div>
                @endforeach
            </div>
        @endforeach
        {{ $content->links() }}
        @premium
            @if(!empty($create) && $create)
                <a class="btn btn-green z-10" style="position:absolute;top:1rem;left:1rem" href="{{ route($component . '_create') }}" title="Create New {{ ucfirst($component) }}"><i class="fa fa-plus"></i></a>
            @endif
        @endpremium
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
