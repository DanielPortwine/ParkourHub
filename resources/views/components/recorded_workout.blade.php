<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('recorded_workout_view', $recorded_workout->id) }}">
                    @if(empty($recorded_workout->workout))
                        Deleted Workout
                    @else
                        Workout {{ $recorded_workout->workout->name }}
                    @endif
                </a>
            </div>
            @if ($recorded_workout->user_id === Auth()->id())
                <div class="col-md-auto vertical-center">
                    <div>
                        <a class="btn text-white" href="{{ route('recorded_workout_edit', $recorded_workout->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    </div>
                </div>
            @endif
        </div>
        <div class="row border-subtle pb-1 mb-2">
            <div class="col">
                {!! nl2br(e(!empty($recorded_workout->workout) && !empty($recorded_workout->workout->description) ? $recorded_workout->workout->description : '')) !!}
            </div>
        </div>
        <div class="row">
            <div class="col">
                {{ $recorded_workout->movements->count() === 1 ? $recorded_workout->movements->count() . ' movement' : $recorded_workout->movements->count() . ' movements' }} | {{ $recorded_workout->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
