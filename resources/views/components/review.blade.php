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
                    <img class="lazyload" data-src="{{ $review->spot->image }}" alt="Image of spot {{ $review->spot }} for review {{ $review->title }}.">
                </a>
            @endif
        </div>
    @endif
    <div class="card-header bg-grey card-hidden-body">
        <div class="row">
            <div class="col sedgwick">
                @if(isset($user))
                    <a class="btn-link" href="{{ route('spot_view', $review->spot_id) }}">{{ $review->spot->name }}</a>
                @else
                    <span>{{ $review->title }}</span>
                @endif
            </div>
            <div class="col-auto d-lg-block d-none">
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
        <div class="d-lg-none d-flex row">
            <div class="col">
                <div class="rating-stars">
                    @for($star = 1; $star <= 5; $star++)
                        <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                    @endfor
                </div>
            </div>
            <div class="col-auto d-flex d-lg-none">
                @if($review->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('review_report', $review->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('review_delete', $review->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($review->reports) > 0)
                        <a class="btn text-white" href="{{ route('review_report_discard', $review->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <div class="card-body bg-grey">
        <div class="row">
            <div class="col-md vertical-center">
                @if(!isset($user))
                    @if(!empty($spot->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $spot->user->profile_image }}"><img src="{{ $spot->user->profile_image }}" alt="Profile image of the user named {{ $spot->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $spot->user->id) }}">{{ $spot->user->name }}</a>
                @endif
            </div>
            <div class="col-auto d-none d-lg-flex">
                @if($review->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('review_report', $review->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('review_delete', $review->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($review->reports) > 0)
                        <a class="btn text-white" href="{{ route('review_report_discard', $review->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
            </div>
        </div>
        @if(isset($user))
            <div class="row mt-2">
                <div class="col">
                    <h4 class="sedgwick text-large">{{ $review->title }}</h4>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col">
                {!! nl2br(e($review->review)) !!}
            </div>
        </div>
    </div>
</div>
