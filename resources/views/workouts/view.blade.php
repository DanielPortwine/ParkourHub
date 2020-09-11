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
            @if ($workout->user->id === Auth()->id())
                <div class="col-auto vertical-center">
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
    @if(count($workout->movements))
        <div class="container mt-3">
            @foreach($workout->movements as $workoutMovement)
                <div class="card mb-3">
                    <div class="card-header bg-grey sedgwick">
                        <a class="btn-link" href="{{ route('movement_view', $workoutMovement->movement->id) }}">{{ $workoutMovement->movement->name }}</a>
                    </div>
                    <div class="card-body bg-grey text-white">
                        <div class="row">
                            @if(isset($workoutMovement->reps))<div class="col">{{ $workoutMovement->reps }} reps</div> @endif
                            @if(isset($workoutMovement->weight))<div class="col">{{ $workoutMovement->weight }}kg</div> @endif
                            @if(isset($workoutMovement->duration))<div class="col">{{ $workoutMovement->duration }}s</div> @endif
                            @if(isset($workoutMovement->distance))<div class="col">{{ $workoutMovement->distance }}cm</div> @endif
                            @if(isset($workoutMovement->height))<div class="col">{{ $workoutMovement->height }}cm</div> @endif
                            @if(isset($workoutMovement->feeling))<div class="col">{{ $workoutMovement->feeling }}</div> @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@section('footer')
    @include('components.footer')
@endsection
