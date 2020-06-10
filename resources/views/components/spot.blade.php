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
                @if(Auth()->id() !== 1)
                    <a class="btn text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @else
                    @if(count($spot->reports) > 0)
                        <a class="btn text-white" href="{{ route('report_discard', ['id' => $spot->id, 'type' => 'App\Spot']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('spot_report_delete', $spot->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                @endif
                <a class="btn text-white tick-off-hitlist-button @if(!(!empty($hit) && $hit->completed_at == null))d-none @endif" id="hitlist-spot-{{ $spot->id }}-add" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                <a class="btn text-white add-to-hitlist-button @if(!empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-tick" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $spot->user->id) }}">{{ $spot->user->name }}</a>
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
