@extends('layouts.app')

@push('title'){{ $spot->name }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100 z-10" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        @if(!empty($spot->image))
            <div class="content-wrapper">
                <img class="full-content-content" src="{{ $spot->image }}" alt="Image of the {{ $spot->name }} challenge.">
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $spot->name }}</h1>
                </div>
                @if(count($spot->reviews))
                    <div class="col-auto vertical-center d-md-flex d-none">
                        <div>
                            @for($star = 1; $star <= 5; $star++)
                                <i class="rating-star pr-1 fa {{ $star <= round($spot->reviews->sum('rating') / count($spot->reviews)) ? 'fa-star' : 'fa-star-o' }}"></i>
                            @endfor
                            <span>({{ count($spot->reviews) }})</span>
                        </div>
                    </div>
                @else
                    <div class="col-auto vertical-center d-md-flex d-none">
                        No reviews
                    </div>
                @endif
                <div class="col-auto vertical-center">
                    <div>
                        @auth
                            <a class="btn text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @endauth
                        @if(Auth()->id() === 1)
                            <a class="btn text-white" href="{{ route('spot_delete', $spot->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                            @if(count($spot->reports) > 0)
                                <a class="btn text-white" href="{{ route('spot_report_discard', $spot->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                            @endif
                        @endif
                        @auth
                            <a class="btn text-white tick-off-hitlist-button @if(!(!empty($hit) && $hit->completed_at == null))d-none @endif" id="hitlist-spot-{{ $spot->id }}-add" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                            <a class="btn text-white add-to-hitlist-button @if(!empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-tick" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                        @endauth
                        <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
                    </div>
                </div>
            </div>
            <div class="row pb-3 border-subtle">
                @if(count($spot->reviews))
                    <div class="col vertical-center d-md-none d-flex">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pr-1 fa {{ $star <= round($spot->reviews->sum('rating') / count($spot->reviews)) ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews) }})</span>
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
                @if ($spot->user->id === Auth()->id())
                    <div class="col-auto">
                        <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    </div>
                @endif
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ count($spot->views) . (count($spot->views) === 1 ? ' view' : ' views') }} | {{ $spot->created_at->format('jS M, Y') }}</span>
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
                    <div class="col-auto">
                        <a class="btn btn-sm btn-green" id="add-movement-button"><i class="fa fa-plus"></i></a>
                    </div>
                </div>
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
                                    <select class="select2-movements" id="spot-{{ $spot->id }}" name="movement"></select>
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
                                            <select class="select2-movement-category" name="category"></select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                        <div class="col-md-8">
                                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="title" maxlength="25" value="{{ old('name') }}" required>
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
                                            <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" maxlength="255">{{ old('description') }}</textarea>
                                            @error('description')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
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
                                    <div class="form-group row">
                                        <div class="col offset-md-2">
                                            <small>The video must contain a demonstration of the movement and nothing else!</small>
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
            @endpremium
        </div>
    </div>
    <div class="section">
        <div class="container">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'reviews')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => null]) }}">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'challenges')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'challenges']) }}">Challenges</a>
                        </li>
                        @premium
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'workouts')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'workouts']) }}">Workouts</a>
                            </li>
                        @endpremium
                    </ul>
                </div>
                @if($tab == null || $tab === 'reviews')
                    <div class="card-body bg-black">
                        @auth
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
                                            <form method="POST" action="{{ route('review_create') }}" enctype="multipart/form-data">
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
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Submit</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endauth
                        @if(!empty($request['reviews']))
                            {{ $reviews->links() }}
                        @endif
                        @foreach($reviews->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $review)
                                    <div class="col-md-6 mb-4">
                                        @include('components.review')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['reviews']))
                            {{ $reviews->links() }}
                        @endif
                        @if (count($spot->textReviews) === 0)
                            <p class="mb-0">This spot has no reviews yet.@auth Create one by clicking 'Submit Review' above. @else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @elseif(count($spot->textReviews) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['reviews']))
                                    <a class="btn btn-green w-75" href="?reviews=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @auth
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
                                            <form method="POST" action="{{ route('spot_comment_create') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="spot" value="{{ $spot->id }}">
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
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Submit</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endauth
                        @if(!empty($request['comments']))
                            {{ $comments->links() }}
                        @endif
                        @foreach($comments->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $comment)
                                    <div class="col-md-6 mb-4">
                                        @include('components.comment')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['comments']))
                            {{ $comments->links() }}
                        @endif
                        @if (count($spot->comments) === 0)
                            <p class="mb-0">This spot has no comments yet.@auth Create one by clicking 'Submit Comment' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @elseif(count($spot->comments) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['comments']))
                                    <a class="btn btn-green w-75" href="?comments=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'challenges')
                    <div class="card-body bg-black">
                        @auth
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
                                            <form method="POST" action="{{ route('challenge_create') }}" enctype="multipart/form-data">
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
                        @endauth
                        @if(!empty($request['challenges']))
                            {{ $challenges->links() }}
                        @endif
                        @foreach($challenges->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-6 mb-4">
                                        @include('components.challenge')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['challenges']))
                            {{ $challenges->links() }}
                        @endif
                        @if (count($spot->challenges) === 0)
                            <p class="mb-0">This spot has no challenges yet.@auth Create one by clicking 'Create Challenge' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @elseif(count($spot->challenges) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['challenges']))
                                    <a class="btn btn-green w-75" href="?challenges=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'workouts')
                    <div class="card-body bg-black">
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
                                                    <select class="select2-workouts" name="workout"></select>
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
                        @if(!empty($request['workouts']))
                            {{ $workouts->links() }}
                        @endif
                        @foreach($workouts->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $workout)
                                    <div class="col-md-6 mb-4">
                                        @include('components.workout')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['workouts']))
                            {{ $workouts->links() }}
                        @endif
                        @if (count($spot->workouts) === 0)
                            <p class="mb-0">This spot has no workouts yet. Link one by selecting it above.</p>
                        @elseif(count($spot->workouts) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['workouts']))
                                    <a class="btn btn-green w-75" href="?workouts=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}">Less</a>
                                @endif
                            </div>
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

@push('scripts')
    <script defer>
        var urlParams = new URLSearchParams(window.location.search);
        $.ajax({
            url: '/movements/getMovements',
            data: {
                link: 'spotMove',
                id: {{ $spot->id }},
            },
            success: function (response) {
                $('.select2-movements').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
        $.ajax({
            url: '/movements/getMovementCategories',
            data: {
                types: [1]
            },
            success: function (response) {
                $('.select2-movement-category').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
        $.ajax({
            url: '/workouts/getWorkouts',
            data: {
                spot: {{ $spot->id }}
            },
            success: function (response) {
                $('.select2-workouts').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
    </script>
@endpush
