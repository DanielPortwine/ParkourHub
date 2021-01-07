<div class="card bg-grey">
    <div class="spot-icons">
        @if(isset($hit) && $hit->completed_at != null)
            <i class="fa fa-check-square-o text-shadow" title="{{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
        @endif
    </div>
    <div class="content-wrapper">
        @if(!empty($spot->image))
            <a href="{{ route('spot_view', $spot->id) }}">
                @if(isset($lazyload) ? $lazyload : true)
                    <img class="lazyload" data-src="{{ $spot->image }}" alt="Image of the {{ $spot->name }} spot.">
                @else
                    <img src="{{ $spot->image }}" alt="Image of the {{ $spot->name }} spot.">
                @endif
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="d-block d-lg-flex col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('spot_view', $spot->id) }}">{{ $spot->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('spot_recover', $spot->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('spot_remove', $spot->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-auto vertical-center pt-2 pt-lg-0">
                @if(count($spot->reviews))
                    <div>
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pr-1 fa {{ $star <= $spot->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews) }})</span>
                    </div>
                @else
                    <span>No reviews</span>
                @endif
            </div>
        </div>
        <div class="row pt-lg-2">
            <div class="col vertical-center">
                <span>
                    {{ count($spot->comments) . (count($spot->comments) === 1 ? ' comment' : ' comments') }} |
                    {{ count($spot->challenges) . (count($spot->challenges) === 1 ? ' challenge' : ' challenges') }} |
                    Deleted {{ $spot->deleted_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
</div>
