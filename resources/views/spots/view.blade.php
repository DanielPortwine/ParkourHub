@extends('layouts.app')

@push('title'){{ $spot->name }} - Spot | @endpush

@section('description')View spot '{{ $spot->name }}' on Parkour Hub.@endsection
@section('twitter-card-type'){{ 'summary_large_image' }}@endsection
@section('meta-media-content'){{ url($spot->image) }}@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        <div class="content-wrapper">
            <div class="spot-icons">
                @if(!empty($hit))
                    @if(!empty($hit->completed_at))
                        <i class="fa fa-check-square-o text-shadow" title="Ticked Off {{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
                    @else
                        <i class="fa fa-crosshairs text-shadow" title="Added {{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
                    @endif
                @endif
            </div>
            @if(!empty($spot->image))
                <img class="full-content-content" src="{{ $spot->image }}" alt="Image of the {{ $spot->name }} spot.">
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $spot->name }}</h1>
                </div>
                @if(count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()))
                    <div class="col-auto vertical-center d-md-flex d-none">
                        <div>
                            @for($star = 1; $star <= 5; $star++)
                                <i class="rating-star pr-1 fa {{ $star <= $spot->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                            @endfor
                            <span>({{ count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()) }})</span>
                        </div>
                    </div>
                @else
                    <div class="col-auto vertical-center d-md-flex d-none">
                        No reviews
                    </div>
                @endif
                <div class="col-auto vertical-center">
                    @if($spot->user_id === Auth()->id() && $spot->deleted_at !== null)
                        <a class="btn text-white" href="{{ route('spot_recover', $spot->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                        <a class="btn text-white" href="{{ route('spot_remove', $spot->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                    @endif
                    @if($spot->deleted_at === null)
                        @auth
                            @if(empty($hit))
                                    <a class="btn text-white" href="{{ route('add_to_hitlist', $spot->id) }}" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                            @else
                                @if(empty($hit->completed_at))
                                    <a class="btn text-white" href="{{ route('tick_off_hitlist', $spot->id) }}" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                                @endif
                                    <a class="btn text-white" href="{{ route('remove_from_hitlist', $spot->id) }}" title="Remove From Hitlist"><i class="fa fa-times"></i></a>
                            @endif
                            @if(!in_array(Auth()->id(), $localsIDs))
                                <a class="btn text-white" href="{{ route('spot_become_local', $spot->id) }}" title="Become a Local"><i class="fa fa-house-user"></i></a>
                            @else
                                <a class="btn text-white" href="{{ route('spot_abandon_local', $spot->id) }}" title="Abandon Being a Local"><i class="fa fa-house-damage"></i></a>
                            @endif
                        @endauth
                        <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-grey">
                            <a class="dropdown-item text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker nav-icon"></i>Locate</a>
                            @if($spot->user_id === Auth()->id())
                                <a class="dropdown-item text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                                <a class="dropdown-item text-white" href="{{ route('spot_delete', $spot->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                            @endif
                            @auth
                                <a class="dropdown-item text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                            @endauth
                            @if(count($spot->reports) > 0)
                                @can('manage reports')
                                    <a class="dropdown-item text-white" href="{{ route('spot_report_discard', $spot->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                                @endcan
                                @can('remove content')
                                    <a class="dropdown-item text-white" href="{{ route('spot_remove', $spot->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                                @endcan
                            @endif
                            @can('manage copyright')
                                @if($spot->copyright_infringed_at === null)
                                    <a class="dropdown-item text-white" href="{{ route('spot_copyright_set', $spot->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                                @else
                                    <a class="dropdown-item text-white" href="{{ route('spot_copyright_remove', $spot->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                                @endif
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
            <div class="row pb-3 border-subtle">
                @if(count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()))
                    <div class="col vertical-center d-md-none d-flex">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pr-1 fa {{ $star <= $spot->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()) }})</span>
                    </div>
                @else
                    <div class="col-auto vertical-center d-md-none d-flex">
                        No reviews
                    </div>
                @endif
            </div>
            <div class="row pt-2">
                <div class="col vertical-center">
                    @if(!empty($spot->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $spot->user->profile_image }}"><img src="{{ $spot->user->profile_image }}" alt="Profile image of the user named {{ $spot->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $spot->user->id) }}">{{ $spot->user->name }}</a>
                </div>
            </div>
            <div class="row py-2 border-subtle">
                <div class="col">
                    <span>{{ $spot->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($spot->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
            @premium
                <div class="row">
                    <div class="col">
                        <small>Movements at this spot:</small>
                    </div>
                </div>
                <div class="row vertical-center">
                    <div class="col movements-list movements-list-hidden" id="movements-list">
                        <div id="movements-inner-container">
                            @foreach($movements as $movement)
                                @if($movement->user_id === Auth()->id() || $spot->user_id === Auth()->id())
                                    <a class="btn btn-feature btn-movement-{{ $movement->category->colour }}" href="{{ route('movement_view', $movement->id) }}">
                                        {{ $movement->name }}<a class="btn btn-feature-remove btn-green" href="{{ route('spot_remove_movement', [$spot->id, $movement->id]) }}"><i class="fa fa-times"></i></a>
                                    </a>
                                @else
                                    <a class="btn btn-feature btn-movement-{{ $movement->category->colour }}" href="{{ route('movement_view', $movement->id) }}">{{ $movement->name }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @if($spot->deleted_at === null)
                        <div class="col-auto">
                            <a class="btn btn-sm btn-green add-movement-button" data-id="{{ $spot->id }}"><i class="fa fa-plus"></i></a>
                        </div>
                    @endif
                </div>
                @if($spot->deleted_at === null)
                    <div class="row pb-3">
                        <div class="col">
                            <a class="btn btn-link" id="all-movements-button">Show All...</a>
                        </div>
                    </div>
                    <div class="row pb-3" id="add-movement-container" style="display:none">
                        <div class="col">
                            <form method="POST" action="{{ route('spot_add_movement', $spot->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">Select a Movement</label>
                                    <div class="col-md-8 vertical-center">
                                        <select class="select2-5-results" id="spot-{{ $spot->id }}" name="movement">
                                            @foreach($linkableMovements as $movement)
                                                <option value="{{ $movement->id }}">{{ $movement->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2"><button type="submit" class="btn btn-green">Add</button></div>
                                </div>
                            </form>
                            <div class="card @error('category') border-danger @enderror @error('name') border-danger @enderror @error('description') border-danger @enderror @error('video') border-danger @enderror @error('youtube') border-danger @enderror">
                                <div class="card-header bg-green sedgwick card-hidden-body">
                                    <div class="row">
                                        <div class="col">
                                            Can't find what you're looking for?
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-grey text-white">
                                    <form method="POST" action="{{ route('movement_store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="type" value="1">
                                        <input type="hidden" name="spot" value="{{ $spot->id }}">
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Category</label>
                                            <div class="col-md-8 vertical-center">
                                                <select class="select2-5-results @error('category') is-invalid border-danger @enderror" name="category">
                                                    @foreach($movementCategories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                            <div class="col-md-8">
                                                <input id="name" type="text" class="form-control @error('name') is-invalid border-danger @enderror" name="name" autocomplete="title" maxlength="25" value="{{ old('name') }}" required>
                                                @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="description" class="col-md-2 col-form-label text-md-right">Description</label>
                                            <div class="col-md-8">
                                                <textarea id="description" class="form-control @error('description') is-invalid border-danger @enderror" name="description" maxlength="255">{{ old('description') }}</textarea>
                                                @error('description')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
                                            <div class="col-lg-4 col-md-8">
                                                <input type="text" id="youtube" class="form-control @error('youtube') is-invalid border-danger @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                @error('youtube')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4 col-md-8 offset-md-2 offset-lg-0">
                                                <input type="file" id="video" class="form-control-file @error('video') is-invalid border-danger @enderror" name="video">
                                                @error('video')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col offset-md-2">
                                                <small>The video must contain a demonstration of the movement and nothing else!</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Thumbnail</label>
                                            <div class="col-lg-4 col-md-8 offset-md-2 offset-lg-0">
                                                <input type="file" id="thumbnail" class="form-control-file @error('thumbnail') is-invalid border-danger @enderror" name="thumbnail">
                                                @error('thumbnail')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Fields</label>
                                            <div class="col-md-8 vertical-center">
                                                <select class="select2-no-search @error('fields') is-invalid border-danger @enderror" name="fields[]" multiple="multiple">
                                                    @foreach($movementFields as $field)
                                                        <option value="{{ $field->id }}">{{ $field->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('fields')
                                                <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                            <div class="col-md-8">
                                                <select name="visibility" class="form-control select2-no-search">
                                                    @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                                        <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-8 offset-md-2">
                                                <button type="submit" class="btn btn-green">Create</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endpremium
        </div>
    </div>
    <div class="section">
        <div class="container-fluid container-lg p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'reviews')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => null]) }}">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'challenges')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'challenges']) }}">Challenges</a>
                        </li>
                        @auth
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'past-events')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'past-events']) }}">Past Events</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'upcoming-events')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'upcoming-events']) }}">Upcoming Events</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'locals')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'locals']) }}">Locals</a>
                            </li>
                        @endauth
                        @premium
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'workouts')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'workouts']) }}">Workouts</a>
                            </li>
                        @endpremium
                    </ul>
                </div>
                @if($tab === 'reviews')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $spot->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('rating') border-danger @enderror @error('title') border-danger @enderror @error('review') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Submit Review
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('review_store') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="spot" value="{{ $spot->id }}">
                                                <input type="hidden" id="rating" name="rating" value="{{ old('rating') ?: 0 }}">
                                                <div class="form-group row">
                                                    <label class="col-md-2 col-form-label text-md-right">Rating</label>
                                                    <div class="col-md-8 vertical-center">
                                                        <div>
                                                            <div class="rating-stars w-100 @error('rating') is-invalid @enderror">
                                                                <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                                                <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                                                <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                                                <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                                                <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                                            </div>
                                                            @error('rating')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Title</label>
                                                    <div class="col-md-8">
                                                        <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" autocomplete="title" maxlength="25" value="{{ old('title') }}">
                                                        @error('title')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="review" class="col-md-2 col-form-label text-md-right">Review</label>
                                                    <div class="col-md-8">
                                                        <textarea id="review" class="form-control @error('review') is-invalid @enderror" name="review" maxlength="255">{{ old('review') }}</textarea>
                                                        @error('review')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                                    <div class="col-md-8">
                                                        <select name="visibility" class="form-control select2-no-search">
                                                            @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                                                <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Submit</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{ $reviews->links() }}
                        @foreach($reviews->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $review)
                                    <div class="col-md-6 mb-4">
                                        @include('components.review')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $reviews->links() }}
                        @if ($spotReviewsWithTextCount === 0)
                            <p class="mb-0">This spot has no reviews yet.@auth Create one by clicking 'Submit Review' above. @else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $spot->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('comment') border-danger @enderror @error('image') border-danger @enderror @error('youtube') border-danger @enderror @error('video') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Submit Comment
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('comment_store') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="commentable_type" value="Spot">
                                                <input type="hidden" name="commentable_id" value="{{ $spot->id }}">
                                                <div class="form-group row">
                                                    <label for="comment" class="col-md-2 col-form-label text-md-right">Comment</label>
                                                    <div class="col-md-8">
                                                        <textarea id="comment" class="form-control @error('comment') is-invalid @enderror" name="comment" maxlength="255">{{ old('comment') }}</textarea>
                                                        @error('comment')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    @premium
                                                        <label class="col-md-2 col-form-label text-md-right">Youtube, Video or Image</label>
                                                    @else
                                                        <label class="col-md-2 col-form-label text-md-right">Youtube or Image</label>
                                                    @endpremium
                                                    <div class="col-md-4">
                                                        <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                        @error('youtube')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="file" id="video_image" class="form-control-file @error('video_image') is-invalid @enderror" name="video_image">
                                                        @error('video_image')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                                    <div class="col-md-8">
                                                        <select name="visibility" class="form-control select2-no-search">
                                                            @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                                                <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Submit</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{ $comments->links() }}
                        @foreach($comments as $comment)
                            <div class="row">
                                <div class="col mb-4">
                                    @include('components.comment')
                                </div>
                            </div>
                        @endforeach
                        {{ $comments->links() }}
                        @if (count($spot->comments) === 0)
                            <p class="mb-0">This spot has no comments yet.@auth Create one by clicking 'Submit Comment' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @elseif($tab === 'challenges')
                    <div class="card-body bg-black">
                        @if(auth()->check() && auth()->user()->isPremium() && $spot->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('name') border-danger @enderror @error('description') border-danger @enderror @error('difficulty') border-danger @enderror @error('youtube') border-danger @enderror @error('video') border-danger @enderror @error('thumbnail') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Create Challenge
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('challenge_store') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="spot" value="{{ $spot->id }}">
                                                <div class="form-group row">
                                                    <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                                    <div class="col-md-8">
                                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name" maxlength="25" value="{{ old('name') }}">
                                                        @error('name')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="description" class="col-md-2 col-form-label text-md-right">Description</label>
                                                    <div class="col-md-8">
                                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" maxlength="255" required>{{ old('description') }}</textarea>
                                                        @error('description')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <input type="hidden" id="difficulty" name="difficulty" value="{{ old('difficulty') }}">
                                                    <label class="col-md-2 col-form-label text-md-right">Difficulty</label>
                                                    <div class="col-md-8 vertical-center">
                                                        <div>
                                                            <div class="rating-buttons w-100 @error('difficulty') is-invalid @enderror">
                                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-1"></i>
                                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-2"></i>
                                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-3"></i>
                                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-4"></i>
                                                                <i class="rating-circle editable fa fa-circle-o" id="rating-circle-5"></i>
                                                            </div>
                                                            @error('difficulty')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                @premium
                                                    <div class="form-group row">
                                                        <label class="col-md-2 col-form-label text-md-right">Youtube or Video</label>
                                                        <div class="col-md-4">
                                                            <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                            @error('youtube')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="file" id="video" class="form-control-file @error('video') is-invalid @enderror" name="video">
                                                            @error('video')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="form-group row">
                                                        <label class="col-md-2 col-form-label text-md-right">Youtube</label>
                                                        <div class="col-md-4">
                                                            <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                            @error('youtube')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endpremium
                                                <div class="form-group row">
                                                    <label for="thumbnail" class="col-md-2 col-form-label text-md-right">Thumbnail</label>
                                                    <div class="col-md-8">
                                                        <input type="file" id="thumbnail" class="form-control-file @error('thumbnail') is-invalid @enderror" name="thumbnail" required>
                                                        @error('thumbnail')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                                    <div class="col-md-8">
                                                        <select name="visibility" class="form-control select2-no-search @error('visibility') is-invalid @enderror">
                                                            @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                                                <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('visibility')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-10 offset-md-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input @error('link_access') is-invalid @enderror" type="checkbox" name="link_access" id="link_access" value="1">
                                                            <label class="form-check-label" for="link_access">Anyone with link can view</label>
                                                            @error('link_access')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Create</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{ $challenges->links() }}
                        @foreach($challenges->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-6 mb-4">
                                        @include('components.challenge')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $challenges->links() }}
                        @if (count($spot->challenges) === 0)
                            <p class="mb-0">This spot has no challenges yet.@auth @premium Create one by clicking 'Create Challenge' above. @else Become a <a class="btn-link text-premium" href="/premium">Premium Member</a> to create a challenge. @endpremium @else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @elseif($tab === 'past-events')
                    <div class="card-body bg-black">
                        {{ $eventsPast->links() }}
                        @foreach($eventsPast->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $event)
                                    <div class="col-md-6 mb-4">
                                        @include('components.event')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $eventsPast->links() }}
                        @if ($eventsPast->total() === 0)
                            <p class="mb-0">This spot has no past events.</p>
                        @endif
                    </div>
                @elseif($tab === 'upcoming-events')
                    <div class="card-body bg-black">
                        {{ $eventsFuture->links() }}
                        @foreach($eventsFuture->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $event)
                                    <div class="col-md-6 mb-4">
                                        @include('components.event')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $eventsFuture->links() }}
                        @if ($eventsFuture->total() === 0)
                            <p class="mb-0">This spot has no upcoming events yet.</p>
                        @endif
                    </div>
                @elseif($tab === 'locals')
                    <div class="card-body bg-black">
                        {{ $locals->links() }}
                        @foreach($locals->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $locals->links() }}
                        @if (count($spot->locals) === 0)
                            <p class="mb-0">This spot has no locals yet.</p>
                        @endif
                    </div>
                @elseif($tab === 'workouts')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $spot->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('workout') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Link Workout
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('spot_workout_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="spot" value="{{ $spot->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Workout</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-5-results" name="workout">
                                                            @foreach($linkableWorkouts as $workout)
                                                                <option value="{{ $workout->id }}">{{ $workout->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small>Select a workout that can be completed at this spot.</small>
                                                        @error('workout')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="submit" class="btn btn-green">Link</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{ $workouts->links() }}
                        @foreach($workouts->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $workout)
                                    <div class="col-md-6 mb-4">
                                        @include('components.workout')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $workouts->links() }}
                        @if (count($spot->workouts) === 0)
                            <p class="mb-0">This spot has no workouts yet. Link one by selecting it above.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
