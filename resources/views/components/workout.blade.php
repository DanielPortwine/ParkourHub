<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('workout_view', $workout->id) }}">{{ $workout->name ?: 'Workout ' . date('d/m/Y', strtotime($workout->created_at)) }}</a>
            </div>
            @if ($workout->user_id === Auth()->id())
                <div class="col-md-auto vertical-center">
                    <div>
                        <a class="btn text-white" href="{{ route('workout_edit', $workout->id) }}" title="Manage"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
            @endif
        </div>
        <div class="row border-subtle pb-1 mb-2">
            <div class="col">
                {!! nl2br(e($workout->description)) !!}
            </div>
        </div>
        <div class="row">
            <div class="col">
                {{ $workout->movements->count() > 1 ? $workout->movements->count() . ' movements' : $workout->movements->count() . ' movement' }} | {{ $workout->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
