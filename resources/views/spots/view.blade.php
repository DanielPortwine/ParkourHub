@extends('layouts.app')

@push('title'){{ $spot->name }} | @endpush

@php
    $hit = Auth()->user()->hits->where('spot_id', $spot->id)->first()
@endphp

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
                        @if(Auth()->id() !== 1)
                            <a class="btn text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @else
                            @if(count($spot->reports) > 0)
                                <a class="btn text-white" href="{{ route('report_discard', ['id' => $spot->id, 'type' => 'App\Spot']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                            @endif
                            <a class="btn text-white" href="{{ route('spot_report_delete', $spot->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                        @endif
                        <a class="btn text-white tick-off-hitlist-button @if(!(!empty($hit) && $hit->completed_at == null))d-none @endif" id="hitlist-spot-{{ $spot->id }}-add" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                        <a class="btn text-white add-to-hitlist-button @if(!empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-tick" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
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
        </div>
    </div>
    <div class="fragment-link" id="content"></div>
    <div class="section">
        <div class="container">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'reviews')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => null]) }}#content">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'comments']) }}#content">Comments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'challenges')active @endif" href="{{ route('spot_view', ['id' => $spot->id, 'tab' => 'challenges']) }}#content">Challenges</a>
                        </li>
                    </ul>
                </div>
                @if($tab == null || $tab === 'reviews')
                    <div class="card-body bg-black">
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
                        @if (count($spot->reviews) === 0)
                            <p class="mb-0">This spot has no reviews yet. Create one by clicking 'Submit Review' above.</p>
                        @elseif(count($spot->reviews) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['reviews']))
                                    <a class="btn btn-green w-75" href="?reviews=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
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
                                            @if(Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium'))
                                                <div class="form-group row">
                                                    <label class="col-md-2 col-form-label text-md-right">Youtube, Video or Image</label>
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
                                            @else
                                                <div class="form-group row">
                                                    <label class="col-md-2 col-form-label text-md-right">Youtube or Image</label>
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
                                            @endif
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
                            <p class="mb-0">This spot has no comments yet. Create one by clicking 'Submit Comment' above.</p>
                        @elseif(count($spot->comments) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['comments']))
                                    <a class="btn btn-green w-75" href="?comments=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'challenges')
                    <div class="card-body bg-black">
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
                                            @if(Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium'))
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
                                            @endif
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
                            <p class="mb-0">This spot has no challenges yet. Create one by clicking 'Create Challenge' above.</p>
                        @elseif(count($spot->challenges) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['challenges']))
                                    <a class="btn btn-green w-75" href="?challenges=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('spot_view', $spot->id) }}#content">Less</a>
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
