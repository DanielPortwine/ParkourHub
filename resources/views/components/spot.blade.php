@php
$hit = Auth()->user()->hits->where('spot_id', $spot->id)->first()
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
                <img src="{{ $spot->image }}">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('spot_view', $spot->id) }}">{{ $spot->name }}</a>
            </div>
            <div class="col-md-auto">
                @if($spot->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                @if(isset($hit))
                    @if($hit->completed_at == null)
                        <a class="btn text-white" href="{{ route('tick_off_hitlist', $spot->id) }}" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                    @endif
                @else
                    <a class="btn text-white" href="{{ route('add_to_hitlist', $spot->id) }}" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                <span class="large-text sedgwick">{{ $spot->user->name }}</span>
            </div>
            @if(count($spot->reviews))
                <div class="col-md-auto vertical-center">
                    <div>
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pr-1 fa {{ $star <= $spot->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews) }})</span>
                    </div>
                </div>
            @else
                <div class="col-md-auto vertical-center">
                    <span>No reviews</span>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col vertical-center">
                <span>{{ count($spot->views) . (count($spot->views) === 1 ? ' view' : ' views') }} | {{ $spot->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
