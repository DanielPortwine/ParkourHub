<div class="card">
    @if(isset($user))
        <div class="spot-icons">
            @if(isset($hit) && $hit->completed_at != null)
                <i class="fa fa-check-square-o text-shadow" title="{{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
            @endif
            @if($review->spot->private)
                <i class="fa fa-lock text-shadow" title="Private"></i>
            @endif
        </div>
    @endif
    @if(isset($user) && $user === true)
        <div class="content-wrapper">
            @if(!empty($review->spot->image))
                <a href="{{ route('spot_view', $review->spot->id) }}">
                    <img class="lazyload" data-src="{{ $review->spot->image }}">
                </a>
            @endif
        </div>
    @endif
    <div class="card-header bg-grey card-hidden-body">
        <div class="row">
            <div class="col sedgwick">
                @if(isset($user) && $user === true)
                    <a class="btn-link" href="{{ route('spot_view', $review->spot_id) }}">{{ $review->spot->name }}</a>
                @else
                    <span>{{ $review->title }}</span>
                @endif
            </div>
            <div class="col-auto d-md-block d-none">
                <div class="rating-stars">
                    @for($star = 1; $star <= 5; $star++)
                        <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                    @endfor
                </div>
            </div>
            <div class="col-auto">
                <i class="fa fa-caret-down"></i>
            </div>
        </div>
        <div class="d-md-none d-block row">
            <div class="col">
                <div class="rating-stars">
                    @for($star = 1; $star <= 5; $star++)
                        <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    <div class="card-body bg-grey">
        <div class="row">
            @if(isset($user))
                <span class="col h4 sedgwick">{{ $review->title }}</span>
            @else
                <a class="col h4 sedgwick btn-link" href="{{ route('user_view', $review->user->id) }}">{{ $review->user->name }}</a>
            @endif
            <div class="col-auto">
                @if($review->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                @if(Auth()->id() !== 1)
                    <a class="btn text-white" href="{{ route('review_report', $review->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @else
                    @if(count($review->reports) > 0)
                        <a class="btn text-white" href="{{ route('report_discard', ['id' => $review->id, 'type' => 'App\Review']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('review_report_delete', $review->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col">
                {!! nl2br(e($review->review)) !!}
            </div>
        </div>
    </div>
</div>
