<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($event->thumbnail))
            <a href="{{ route('event_view', $event->id) }}">
                <img class="lazyload" data-src="{{ $event->thumbnail }}" alt="Image of the {{ $event->name }} event.">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('event_view', $event->id) }}">{{ $event->name }}</a>
            </div>
            <div class="col-auto vertical-center pl-0">
                @if(!empty(Auth()->user()->email_verified_at))
                    <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right bg-grey">
                        @if($event->user_id === Auth()->id())
                            @premium
                                <a class="dropdown-item text-white" href="{{ route('event_edit', $event->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                            @endpremium
                            <a class="dropdown-item text-white" href="{{ route('event_delete', $event->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                        @endif
                        @auth
                            <a class="dropdown-item text-white" href="{{ route('event_report', $event->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                        @endauth
                        @if(count($event->reports) > 0 && Route::currentRouteName() === 'report_listing')
                            @can('manage reports')
                                <a class="dropdown-item text-white" href="{{ route('event_report_discard', $event->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                            @endcan
                            @can('remove content')
                                <a class="dropdown-item text-white" href="{{ route('event_remove', $event->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                            @endcan
                        @endif
                        @can('manage copyright')
                            @if($event->copyright_infringed_at === null)
                                <a class="dropdown-item text-white" href="{{ route('event_copyright_set', $event->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                            @else
                                <a class="dropdown-item text-white" href="{{ route('event_copyright_remove', $event->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                            @endif
                        @endcan
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($event->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $event->user->profile_image }}"><img src="{{ $event->user->profile_image }}" alt="Profile image of the user named {{ $event->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $event->user->id) }}">{{ $event->user->name }}</a>
            </div>
        </div>
        <div class="row pt-lg-2">
            <div class="col-lg vertical-center">
                {{ Carbon\Carbon::parse($event->date_time)->format('D, d M H:i') }} | {{ Carbon\Carbon::parse($event->date_time)->diffForHumans(['options' => Carbon\Carbon::ONE_DAY_WORDS]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                <span>{{ count($event->spots) . (count($event->spots) === 1 ? ' spot' : ' spots') }} | {{ count($event->attendees) . (count($event->attendees) === 1 ? ' attendee' : ' attendees') }}</span>
            </div>
        </div>
    </div>
</div>
