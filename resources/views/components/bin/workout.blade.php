<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('workout_view', $workout->id) }}">{{ $workout->name  }}</a>
            </div>
            <div class="col-md-auto vertical-center">
                <div>
                    <a class="btn text-white" href="{{ route('workout_recover', $workout->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                    <a class="btn text-white" href="{{ route('workout_remove', $workout->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                </div>
            </div>
        </div>
        <div class="row border-subtle pb-1 mb-2">
            <div class="col">
                {!! nl2br(e($workout->description)) !!}
            </div>
        </div>
        <div class="row">
            <div class="col">
                Deleted {{ $workout->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
