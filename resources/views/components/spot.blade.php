@php
$hit = $spot->hits->where('user_id', Auth()->id() ?: null)->first()
@endphp

<div class="card bg-grey">
    <div class="spot-icons">
        @if(isset($hit) && $hit->completed_at != null)
            <i class="fa fa-check-square-o text-shadow" title="{{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
        @endif
        @if($spot->private)
            <i class="fa fa-lock text-shadow" title="Private"></i>
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
                @if($spot->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                @auth
                    <a class="btn text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @endauth
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('spot_delete', [$spot->id, url()->full()]) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($spot->reports) > 0)
                        <a class="btn text-white" href="{{ route('spot_report_discard', $spot->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
                @auth
                    <a class="btn text-white tick-off-hitlist-button @if(!(!empty($hit) && $hit->completed_at == null))d-none @endif" id="hitlist-spot-{{ $spot->id }}-add" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                    <a class="btn text-white add-to-hitlist-button @if(!empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-tick" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                    <a class="btn text-white remove-from-hitlist-button @if(empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-remove" title="Remove From Hitlist"><i class="fa fa-times"></i></a>
                @endauth
                <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($spot->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $spot->user->profile_image }}"><img src="{{ $spot->user->profile_image }}" alt="Profile image of the user named {{ $spot->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $spot->user->id) }}">{{ $spot->user->name }}</a>
            </div>
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
                <span>{{ $spot->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
