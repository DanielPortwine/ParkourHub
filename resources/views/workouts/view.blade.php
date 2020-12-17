@extends('layouts.app')

@push('title'){{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }} - Workout | @endpush

@section('description')View the '{{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }}' workout on Parkour Hub.@endsection

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
        @if(!empty($displayMovement->video))
            <div class="content-wrapper">
                <video controls>
                    <source src="{{ $displayMovement->video }}" type="video/{{ $displayMovement->video_type }}">
                </video>
                <h4 class="sedgwick top-right z-10">{{ $displayMovement->name }}</h4>
            </div>
        @elseif(!empty($displayMovement->youtube))
            <div class="content-wrapper">
                <div class="youtube" data-id="{{ $displayMovement->youtube }}" data-start="{{ $displayMovement->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                    <h4 class="sedgwick top-right z-10">{{ $displayMovement->name }}</h4>
                </div>
            </div>
        @endif
    </div>
    <div class="grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    @if ($workout->user->id === Auth()->id())
                        <a class="btn text-white" href="{{ route('workout_edit', $workout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('recorded_workout_create', $workout->id) }}" title="Record"><i class="fa fa-calendar-plus-o"></i></a>
                    @if($workout->bookmarks->contains(Auth()->id()))
                        <a class="btn text-white" href="{{ route('workout_unbookmark', $workout->id) }}" title="Remove Bookmark"><i class="fa fa-bookmark"></i></a>
                    @else
                        <a class="btn text-white" href="{{ route('workout_bookmark', $workout->id) }}" title="Bookmark"><i class="fa fa-bookmark-o"></i></a>
                    @endif
                </div>
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
        <div class="container-fluid container-md p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'movements')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => null]) }}">Movements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'recorded')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => 'recorded']) }}">Recorded Workouts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'spots')active @endif" href="{{ route('workout_view', ['id' => $workout->id, 'tab' => 'spots']) }}">Spots</a>
                        </li>
                    </ul>
                </div>
                @if($tab == null || $tab === 'movements')
                    <div class="card-body bg-black">
                        @if(count($workout->movements))
                            @foreach($workout->movements as $workoutMovement)
                                <div class="card mb-3">
                                    <div class="card-header bg-grey sedgwick">
                                        <a class="btn-link" href="{{ route('movement_view', $workoutMovement->movement->id) }}">{{ $workoutMovement->movement->name }}</a>
                                    </div>
                                    <div class="card-body bg-grey text-white">
                                        <div class="row">
                                            @foreach($workoutMovement->fields as $field)
                                                <div class="col-md">{{ $field->field->label . ': ' . $field->value }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            There are no movements in this workout.
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
                                                    <select class="select2-spots" name="spot"></select>
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

@push('scripts')
    <script defer>
        $.ajax({
            url: '/spots/getSpots',
            data: {
                workout: {{ $workout->id }}
            },
            success: function (response) {
                $('.select2-spots').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
    </script>
@endpush
