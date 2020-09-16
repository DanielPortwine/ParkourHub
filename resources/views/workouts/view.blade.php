@extends('layouts.app')

@push('title'){{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100 z-10" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container">
        <div class="row pt-4">
            <div class="col vertical-center">
                <h1 class="sedgwick mb-0">{{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }}</h1>
            </div>
            <div class="col-auto vertical-center">
                <a class="btn text-white" href="{{ route('recorded_workout_create', $workout->id) }}" title="Record"><i class="fa fa-calendar-plus-o"></i></a>
                @if($workout->bookmarks->contains(Auth()->id()))
                    <a class="btn text-white" href="{{ route('workout_unbookmark', $workout->id) }}" title="Remove Bookmark"><i class="fa fa-bookmark"></i></a>
                @else
                    <a class="btn text-white" href="{{ route('workout_bookmark', $workout->id) }}" title="Bookmark"><i class="fa fa-bookmark-o"></i></a>
                @endif
                @if ($workout->user->id === Auth()->id())
                    <a class="btn text-white" href="{{ route('workout_edit', $workout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
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
    @if(count($workout->movements))
        <div class="container mt-3">
            @foreach($workout->movements as $workoutMovement)
                <div class="card mb-3">
                    <div class="card-header bg-grey sedgwick">
                        <a class="btn-link" href="{{ route('movement_view', $workoutMovement->movement->id) }}">{{ $workoutMovement->movement->name }}</a>
                    </div>
                    <div class="card-body bg-grey text-white">
                        <div class="row">
                            @foreach($workoutMovement->fields as $field)
                                <div class="col">{{ $field->field->label . ': ' . $field->value }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="container my-3">
        <div class="row pt-4">
            <div class="col vertical-center">
                <h3 class="sedgwick">Recorded Workouts</h3>
            </div>
        </div>
        <div class="row">
            <div class="col">
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
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
