<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($challenge->thumbnail))
            <a href="{{ route('challenge_view', $challenge->id) }}">
                <img class="lazyload" data-src="{{ $challenge->thumbnail }}" alt="Image of the {{ $challenge->name }} challenge.">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="d-block d-lg-flex col-lg vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $challenge->id) }}">{{ $challenge->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('challenge_recover', $challenge->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('challenge_remove', $challenge->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-auto vertical-center pt-2 pt-lg-0">
                <div>
                    @for($circle = 1; $circle <= 5; $circle++)
                        <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="row pt-lg-2">
            <div class="col-lg vertical-center">
                <span>{{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | Deleted {{ $challenge->deleted_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
