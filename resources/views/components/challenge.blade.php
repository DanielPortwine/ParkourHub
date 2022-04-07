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
            <div class="col vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $challenge->id) }}">{{ $challenge->name }}</a>
            </div>
            <div class="col-auto vertical-center pl-0">
                @if(empty(Auth()->user()->email_verified_at))
                    @if(!empty($challenge->spot))
                        <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                    @endif
                @else
                    <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right bg-grey">
                        @if($challenge->user_id === Auth()->id())
                            @premium
                                <a class="dropdown-item text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                            @endpremium
                            <a class="dropdown-item text-white" href="{{ route('challenge_delete', $challenge->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                        @endif
                        @auth
                            <a class="dropdown-item text-white" href="{{ route('challenge_report', $challenge->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                        @endauth
                        @if(count($challenge->reports) > 0 && Route::currentRouteName() === 'report_listing')
                            @can('manage reports')
                                <a class="dropdown-item text-white" href="{{ route('challenge_report_discard', $challenge->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                            @endcan
                            @can('remove content')
                                <a class="dropdown-item text-white" href="{{ route('challenge_remove', $challenge->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                            @endcan
                        @endif
                        @can('manage copyright')
                            @if($challenge->copyright_infringed_at === null)
                                <a class="dropdown-item text-white" href="{{ route('challenge_copyright_set', $challenge->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                            @else
                                <a class="dropdown-item text-white" href="{{ route('challenge_copyright_remove', $challenge->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                            @endif
                        @endcan
                        @if(!empty($challenge->spot))
                            <a class="dropdown-item text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker nav-icon"></i>Locate</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($challenge->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $challenge->user->profile_image }}"><img src="{{ $challenge->user->profile_image }}" alt="Profile image of the user named {{ $challenge->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $challenge->user->id) }}">{{ $challenge->user->name }}</a>
            </div>
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
                <span>{{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | {{ $challenge->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
