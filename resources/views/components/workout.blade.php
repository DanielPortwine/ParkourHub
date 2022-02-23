<div class="card bg-grey">
    @if(!empty($workout->thumbnail))
        <div class="content-wrapper">
            <a href="{{ route('workout_view', $workout->id) }}">
                @if(isset($lazyload) ? $lazyload : true)
                    <img class="lazyload" data-src="{{ $workout->thumbnail }}" alt="Image of the {{ $workout->name }} workout.">
                @else
                    <img src="{{ $workout->thumbnail }}" alt="Image of the {{ $workout->name }} workout.">
                @endif
            </a>
        </div>
    @endif
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('workout_view', $workout->id) }}">{{ $workout->name  }}</a>
            </div>
            <div class="col-md-auto vertical-center">
                @if($workout->bookmarks->contains(Auth()->id()))
                    <a class="btn text-white" href="{{ route('workout_unbookmark', $workout->id) }}" title="Remove Bookmark"><i class="fa fa-bookmark"></i></a>
                @else
                    <a class="btn text-white" href="{{ route('workout_bookmark', $workout->id) }}" title="Bookmark"><i class="fa fa-bookmark-o"></i></a>
                @endif
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if ($workout->user_id === Auth()->id() && Auth()->user()->isPremium())
                        <a class="dropdown-item text-white" href="{{ route('workout_edit', $workout->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                    @endif
                    @auth
                        <a class="dropdown-item text-white" href="{{ route('workout_report', $workout->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @endauth
                    @if(count($workout->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('workout_report_discard', $workout->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('workout_remove', $workout->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                    @can('manage copyright')
                        @if($workout->copyright_infringed_at === null)
                            <a class="dropdown-item text-white" href="{{ route('workout_copyright_set', $workout->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                        @else
                            <a class="dropdown-item text-white" href="{{ route('workout_copyright_remove', $workout->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
        <div class="row border-subtle pb-1 mb-2">
            <div class="col">
                {!! nl2br(e($workout->description)) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($workout->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $workout->user->profile_image }}"><img src="{{ $workout->user->profile_image }}" alt="Profile image of the user named {{ $workout->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $workout->user->id) }}">{{ $workout->user->name }}</a>
            </div>
        </div>
        <div class="row">
            <div class="col">
                @php $movementsCount = $workout->movements_count @endphp
                {{ $movementsCount === 1 ? $movementsCount . ' movement' : $movementsCount . ' movements' }} | {{ $workout->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
