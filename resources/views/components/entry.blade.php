<div class="card bg-grey">
    @if(isset($winnerHighlight) && $winnerHighlight === true)
        <div class="card-header bg-green sedgwick">
            Winner
        </div>
    @endif
    <div class="content-wrapper">
        @if(!empty($entry->video))
            <video controls>
                <source src="{{ $entry->video }}" type="video/{{ $entry->video_type }}">
            </video>
        @elseif(!empty($entry->youtube))
            <div class="youtube" data-id="{{ $entry->youtube }}" data-start="{{ $entry->youtube_start }}">
                <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
            </div>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle">
            <div class="col-lg vertical-center">
                @if(!isset($challenge) && !empty($entry->challenge))
                    <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $entry->challenge->id) }}">
                        @if($entry->winner)
                            <i class="fa fa-trophy"></i>
                        @endif
                        {{ $entry->challenge->name }}
                    </a>
                @else
                    @if(!empty($entry->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $entry->user->profile_image }}"><img src="{{ $entry->user->profile_image }}" alt="Profile image of the user named {{ $entry->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $entry->user->id) }}">
                        @if($entry->winner)
                            <i class="fa fa-trophy"></i>
                        @endif
                        {{ $entry->user->name }}
                    </a>
                @endif
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <div>
                    @if(!empty($challenge) && $challenge->user_id === Auth()->id() && !$challenge->won)
                        <a class="btn text-white" href="{{ route('challenge_win', $entry->id) }}" title="Select Winner"><i class="fa fa-trophy"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('entry_report', $entry->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                    @can('delete content')
                        <a class="btn text-white" href="{{ route('entry_delete', $entry->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                        @if(count($entry->reports) > 0)
                            <a class="btn text-white" href="{{ route('entry_report_discard', $entry->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                        @endif
                    @endcan
                    @if(!empty($entry->challenge) && !empty($entry->challenge->spot))
                        <a class="btn text-white" href="{{ route('spots', ['spot' => $entry->challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-lg vertical-center">
                {{ $entry->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
