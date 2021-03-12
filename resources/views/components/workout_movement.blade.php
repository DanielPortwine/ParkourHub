<div class="card mb-3">
    @if(!empty($tab) && $tab === 'history')
        <div class="card-header bg-grey sedgwick">
            <div class="row">
                <div class="col-sm">
                    <a class="btn-link" href="{{ route('recorded_workout_view', $workoutMovement->recordedWorkout->id) }}">{{ $workoutMovement->workout->name }}</a>
                </div>
                <div class="col-sm-auto">
                    <span class="h5">{{ $workoutMovement->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="card-header bg-grey sedgwick">
            <a class="btn-link" href="{{ route('movement_view', $workoutMovement->movement->id) }}">{{ $workoutMovement->movement->name }}</a>
        </div>
    @endif
    <div class="card-body bg-grey text-white">
        <div class="row">
            @foreach($workoutMovement->fields as $field)
                <div class="col-md">{{ $field->field->label . ': ' . $field->value . $field->field->unit }}</div>
            @endforeach
        </div>
    </div>
</div>
