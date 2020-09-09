<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('workout_log_view', $workout_log->id) }}">{{ $workout_log->name ?: 'Workout ' . date('d/m/Y', strtotime($workout_log->created_at)) }}</a>
            </div>
            @if ($workout_log->user_id === Auth()->id())
                <div class="col-md-auto vertical-center">
                    <div>
                        <a class="btn text-white" href="{{ route('workout_log_edit', $workout_log->id) }}" title="Manage"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
            @endif
        </div>
        <div class="row border-subtle pb-1 mb-2">
            <div class="col">
                {!! nl2br(e($workout_log->description)) !!}
            </div>
        </div>
        <div class="row">
            <div class="col">
                {{ $workout_log->movementEntries->count() > 1 ? $workout_log->movementEntries->count() . ' movements' : $workout_log->movementEntries->count() . ' movement' }} | {{ $workout_log->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
