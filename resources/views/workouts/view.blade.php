@extends('layouts.app')

@push('title'){{ $workout->name }} - Workout | @endpush

@section('description')View the '{{ $workout->name }}' workout on Parkour Hub.@endsection
@if(!empty($workout->thumbnail))
    @section('twitter-card-type'){{ 'summary_large_image' }}@endsection
    @section('meta-media-content'){{ url($workout->thumbnail) }}@endsection
@endif

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
            @if(!empty($workout->thumbnail))
                <img class="w-100 mb-2" src="{{ $workout->thumbnail }}" alt="Image of the {{ $workout->name }} workout.">
            @elseif(!empty($displayMovement->video))
                <video controls>
                    <source src="{{ $displayMovement->video }}" type="video/{{ $displayMovement->video_type }}">
                </video>
                <h4 class="sedgwick top-right z-10 text-shadow">{{ $displayMovement->name }}</h4>
            @elseif(!empty($displayMovement->youtube))
                <div class="youtube" data-id="{{ $displayMovement->youtube }}" data-start="{{ $displayMovement->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                    <h4 class="sedgwick top-right z-10 text-shadow">{{ $displayMovement->name }}</h4>
                </div>
            @endif
        </div>
    </div>
    <div class="grey-section section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $workout->name }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    @if($workout->deleted_at === null)
                        @if ($workout->user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('workout_delete', $workout->id) }}" title="Delete"><i class="fa fa-trash"></i></a>
                        @endif
                        @auth
                            <a class="btn text-white" href="{{ route('workout_report', $workout->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @endauth
                        @if(count($workout->reports) > 0)
                            @can('manage reports')
                                <a class="btn text-white" href="{{ route('workout_report_discard', $workout->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                            @endcan
                            @can('remove content')
                                <a class="btn text-white" href="{{ route('workout_remove', $workout->id) }}" title="Remove Content"><i class="fa fa-trash"></i></a>
                            @endcan
                        @endif
                        <a class="btn text-white" href="{{ route('recorded_workout_create', $workout->id) }}" title="Record"><i class="fa fa-calendar-plus-o"></i></a>
                        @if($workout->bookmarks->contains(Auth()->id()))
                            <a class="btn text-white" href="{{ route('workout_unbookmark', $workout->id) }}" title="Remove Bookmark"><i class="fa fa-bookmark"></i></a>
                        @else
                            <a class="btn text-white" href="{{ route('workout_bookmark', $workout->id) }}" title="Bookmark"><i class="fa fa-bookmark-o"></i></a>
                        @endif
                    @elseif($workout->user_id === Auth()->id())
                        <a class="btn text-white" href="{{ route('workout_recover', $workout->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                        <a class="btn text-white" href="{{ route('workout_remove', $workout->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                    @endif
                </div>
            </div>
            <div class="row pt-2">
                <div class="col vertical-center">
                    @if(!empty($workout->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $workout->user->profile_image }}"><img src="{{ $workout->user->profile_image }}" alt="Profile image of the user named {{ $workout->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $workout->user->id) }}">{{ $workout->user->name }}</a>
                </div>
                @if ($workout->user->id === Auth()->id() && $workout->deleted_at === null)
                    <div class="col-auto">
                        <a class="btn text-white" href="{{ route('workout_edit', $workout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    </div>
                @endif
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ $workout->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($workout->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container-fluid container-lg p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'movements')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => null]) }}">Movements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'recorded')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => 'recorded']) }}">Recorded Workouts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'spots')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => 'spots']) }}">Spots</a>
                        </li>
                    </ul>
                </div>
                @if($tab === 'movements')
                    <div class="card-body bg-black">
                        @if(count($workoutMovements))
                            @foreach($workoutMovements as $workoutMovement)
                                @include('components.workout_movement')
                            @endforeach
                        @else
                            There are no movements in this workout.
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $workout->deleted_at === null)
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
                                                <input type="hidden" name="commentable_type" value="Workout">
                                                <input type="hidden" name="commentable_id" value="{{ $workout->id }}">
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
                        @if (count($workout->comments) === 0)
                            <p class="mb-0">This spot has no comments yet.@auth Create one by clicking 'Submit Comment' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @elseif($tab === 'recorded')
                    <div class="card-body bg-black">
                        {{ $recordedWorkouts->links() }}
                        @foreach($recordedWorkouts->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $recorded_workout)
                                    <div class="col-md-6 mb-4">
                                        @include('components.recorded_workout')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $recordedWorkouts->links() }}
                        @if (count($recordedWorkouts) === 0)
                            <p class="mb-0">You haven't recorded any of this workout yet. You can click at the top of the page to record one.</p>
                        @endif
                    </div>
                @elseif($tab === 'spots')
                    <div class="card-body bg-black">
                        @if($workout->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('workout') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Link Spot
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('spot_workout_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="workout" value="{{ $workout->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Spot</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-5-results" name="spot">
                                                            @foreach($linkableSpots as $spot)
                                                                <option value="{{ $spot->id }}">{{ $spot->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <small>Select a spot that this workout can be completed at.</small>
                                                        @error('spot')
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
                        {{ $spots->links() }}
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        {{ $spots->links() }}
                        @if (count($workout->spots) === 0)
                            <p class="mb-0">This workout hasn't been linked to any spots yet. Link it to one above.</p>
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
