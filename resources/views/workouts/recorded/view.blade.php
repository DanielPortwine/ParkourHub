@extends('layouts.app')

@push('title')
    @if(empty($recordedWorkout->workout))
        Deleted Workout - Workout
    @else
        Workout {{ $recordedWorkout->workout->name }} - Workout
    @endif |
@endpush

@section('description')View a workout on Parkour Hub.@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container">
        <div class="row pt-4">
            <div class="col vertical-center">
                <h1 class="sedgwick mb-0">
                    @if(empty($recordedWorkout->workout))
                        Deleted Workout
                    @else
                        Workout {{ $recordedWorkout->workout->name }}
                    @endif
                </h1>
            </div>
            <div class="col-auto vertical-center">
                @if ($recordedWorkout->user->id === Auth()->id())
                    @premium
                        <a class="btn text-white" href="{{ route('recorded_workout_edit', $recordedWorkout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @endpremium
                    <a class="btn text-white" href="{{ route('recorded_workout_delete', $recordedWorkout->id) }}" title="Delete"><i class="fa fa-trash"></i></a>
                @endif
                @if(!empty($recordedWorkout->workout))
                    <a class="btn text-white" href="{{ route('workout_view', $recordedWorkout->workout->id) }}" title="View Workout"><i class="fa fa-eye"></i></a>
                @endif
            </div>
        </div>
        <div class="row pb-2 border-subtle">
            <div class="col">
                <span>{{ $recordedWorkout->created_at->format('jS M, Y') }} | {{ $recordedWorkout->time }}</span>
            </div>
        </div>
        <div class="py-3">
            <div id="description-box">
                <p class="mb-0" id="description-content">{!! nl2br(e(!empty($recorded_workout->workout) && !empty($recorded_workout->workout->description) ? $recorded_workout->workout->description : '')) !!}</p>
            </div>
            <a class="btn btn-link" id="description-more">More</a>
        </div>
    </div>
    @if(count($recordedWorkout->movements))
        <div class="container">
            @foreach($recordedWorkout->movements as $workoutMovement)
                @include('components.workout_movement')
            @endforeach
        </div>
    @endif
@endsection

@section('footer')
    @include('components.footer')
@endsection
