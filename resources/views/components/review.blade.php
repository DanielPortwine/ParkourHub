<div class="card">
    @if(isset($user))
        <div class="spot-icons">
            @if(isset($hit) && $hit->completed_at != null)
                <i class="fa fa-check-square-o text-shadow" title="{{ Carbon\Carbon::parse($hit->completed_at)->diffForHumans() }}"></i>
            @endif
        </div>
    @endif
    @if(isset($user) && $user === true)
        <div class="content-wrapper">
            @if(!empty($review->spot) && !empty($review->spot->image))
                <a href="{{ route('spot_view', $review->spot->id) }}">
                    <img class="lazyload" data-src="{{ $review->spot->image }}" alt="Image of spot {{ $review->spot->name }} for review {{ $review->title }}.">
                </a>
            @endif
        </div>
    @endif
    <div class="card-header bg-grey card-hidden-body">
        <div class="row">
            <div class="col sedgwick">
                @if(isset($user) && !empty($review->spot))
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
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($review->user_id === Auth()->id())
                        <a class="dropdown-item text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        <a class="dropdown-item text-white" href="{{ route('review_delete', $review->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    @auth
                        <a class="dropdown-item text-white" href="{{ route('review_report', $review->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @endauth
                    @if(count($review->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('review_report_discard', $review->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('review_remove', $review->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body bg-grey">
        <div class="row">
            <div class="col vertical-center">
                @if(empty($user))
                    @if(!empty($review->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $review->user->profile_image }}"><img src="{{ $review->user->profile_image }}" alt="Profile image of the user named {{ $review->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $review->user->id) }}">{{ $review->user->name }}</a>
                @else
                    <h4 class="sedgwick text-large">{{ $review->title }}</h4>
                @endif
            </div>
            <div class="col-auto d-none d-lg-flex">
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($review->user_id === Auth()->id())
                        <a class="dropdown-item text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        <a class="dropdown-item text-white" href="{{ route('review_delete', $review->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    @auth
                        <a class="dropdown-item text-white" href="{{ route('review_report', $review->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @endauth
                    @if(count($review->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('review_report_discard', $review->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('review_remove', $review->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                {!! nl2br(e($review->review)) !!}
            </div>
        </div>
    </div>
</div>
