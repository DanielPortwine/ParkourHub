@extends('layouts.app')

@push('title'){{ $spot->name }} | @endpush

@section('content')
    <div class="flex-center">
        <div class="text-center position-absolute">
            <h1 class="page-title sedgwick title-shadow">{{ $spot->name }}</h1>
        </div>
        <div class="text-center bottom-centre title-shadow d-md-block d-none" id="scroll-arrow">
            <i class="fa fa-angle-double-down"></i>
        </div>
        <div class="full-content-section">
            @if(!empty($spot->image))
                <img class="full-content-content" src="{{ $spot->image }}" alt="Image of the {{ $spot->name }} spot.">
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-3">
                <div class="col vertical-center">
                    <span>{{ $views . ($views === 1 ? ' view' : ' views') }} | {{ $spot->created_at->format('jS M, Y') }}</span>
                </div>
                @if(count($spot->reviews))
                    <div class="col-auto vertical-center d-md-flex d-none">
                        <div>
                            @for($star = 1; $star <= 5; $star++)
                                <i class="rating-star pl-1 fa {{ $star <= $spotRating ? 'fa-star' : 'fa-star-o' }}"></i>
                            @endfor
                            <span>({{ count($spot->reviews) }})</span>
                        </div>
                    </div>
                @endif
                <div class="col-auto">
                    @if(in_array($spot->id, array_keys($hitlist)))
                        @if(empty($hitlist[$spot->id]))
                            <a class="btn text-white" href="{{ route('tick_off_hitlist', $spot->id) }}" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                        @endif
                    @else
                        <a class="btn text-white" href="{{ route('add_to_hitlist', $spot->id) }}" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
            <div class="row pb-3 border-subtle">
                @if(count($spot->reviews))
                    <div class="col vertical-center d-md-none d-flex">
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pl-1 fa {{ $star <= $spotRating ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews) }})</span>
                    </div>
                @endif
            </div>
            <div class="row py-3">
                <div class="col vertical-center">
                    <span class="large-text sedgwick">{{ $spot->user->name }}</span>
                </div>
                <div class="col-auto">
                    @if ($spot->user->id === Auth()->id())
                        <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @endif
                </div>
            </div>
            <div class="pb-4">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($spot->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container">
            <div class="row py-4">
                <div class="col">
                    <h2 class="sedgwick subtitle mb-0">Reviews</h2>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
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
                                        <div class="rating-stars @error('rating') is-invalid @enderror">
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
        </div>
        <div class="container">
            @foreach($spot->reviews->whereNotNull('title')->sortByDesc('created_at')->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $review)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-grey card-hidden-body">
                                    <div class="row">
                                        <span class="col sedgwick">{{ $review->title }}</span>
                                        <div class="col-auto d-md-block d-none">
                                            <div class="rating-stars">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="d-md-none d-block row">
                                        <div class="col">
                                            <div class="rating-stars">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-grey">
                                    <div class="row">
                                        <span class="col h4 sedgwick">{{ $review->user->name }}</span>
                                        <div class="col-auto">
                                            @if($review->user_id === Auth()->id())
                                                <a class="btn text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            {!! nl2br(e($review->review)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if (count($spot->reviews) === 0)
                <p>This spot has no reviews yet. Create one by clicking 'Submit Review' above.</p>
            @endif
        </div>
    </div>
    <div class="section">
        <div class="container">
            <div class="row py-4">
                <div class="col">
                    <h2 class="sedgwick subtitle mb-0">Challenges</h2>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
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
                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
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
                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" maxlength="255">{{ old('description') }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
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
        </div>
        <div class="container">
            @foreach($spot->challenges->sortByDesc('created_at')->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $challenge)
                        <div class="col-md-6 mb-4">
                            @include('components.card', ['card' => $challenge, 'type' => 'challenge', 'spot' => $challenge->spot_id])
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if (count($spot->challenges) === 0)
                <p>This spot has no challenges yet. Create one by clicking 'Create Challenge' above.</p>
            @endif
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
