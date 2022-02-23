@php
$hit = $spot->hits->where('user_id', Auth()->id() ?: null)->first()
@endphp

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
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($spot->user_id === Auth()->id())
                        <a class="dropdown-item text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        <a class="dropdown-item text-white" href="{{ route('spot_delete', $spot->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    @auth
                        <a class="dropdown-item text-white" href="{{ route('spot_report', $spot->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @endauth
                    @if(count($spot->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('spot_report_discard', $spot->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('spot_remove', $spot->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                    @can('manage copyright')
                        @if($spot->copyright_infringed_at === null)
                            <a class="dropdown-item text-white" href="{{ route('spot_copyright_set', $spot->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                        @else
                            <a class="dropdown-item text-white" href="{{ route('spot_copyright_remove', $spot->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                        @endif
                    @endcan
                    @auth
                        <a class="dropdown-item text-white tick-off-hitlist-button @if(!(!empty($hit) && $hit->completed_at == null))d-none @endif" id="hitlist-spot-{{ $spot->id }}-add" title="Tick Off Hitlist"><i class="fa fa-check nav-icon"></i>Tick Off Hitlist</a>
                        <a class="dropdown-item text-white add-to-hitlist-button @if(!empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-tick" title="Add To Hitlist"><i class="fa fa-crosshairs nav-icon"></i>Add To Hitlist</a>
                        <a class="dropdown-item text-white remove-from-hitlist-button @if(empty($hit))d-none @endif" id="hitlist-spot-{{ $spot->id }}-remove" title="Remove From Hitlist"><i class="fa fa-times nav-icon"></i>Remove From Hitlist</a>
                    @endauth
                    @if(empty($map) || !$map)
                        <a class="dropdown-item text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker nav-icon"></i>Locate</a>
                    @endif
                </div>
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
                @if(count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()))
                    <div>
                        @for($star = 1; $star <= 5; $star++)
                            <i class="rating-star pr-1 fa {{ $star <= $spot->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                        @endfor
                        <span>({{ count($spot->reviews()->withoutGlobalScope(\App\Scopes\VisibilityScope::class)->get()) }})</span>
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
