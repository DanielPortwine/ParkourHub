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
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $entry->challenge->id) }}">
                    @if($entry->winner)
                        <i class="fa fa-trophy"></i>
                    @endif
                    {{ $entry->challenge->name }}
                </a>
            </div>
            <div class="col-md-auto vertical-center">
                <div>
                    @if($entry->challenge->user_id === Auth()->id() && !$entry->challenge->won)
                        <a class="btn text-white" href="{{ route('challenge_win', $entry->id) }}" title="Select Winner"><i class="fa fa-trophy"></i></a>
                    @endif
                    <a class="btn text-white d-md-inline-block d-none" href="{{ route('spots', ['spot' => $entry->challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col vertical-center">
                <p class="mb-0"><span class="large-text sedgwick">{{ $entry->user->name }} </span> | {{ $entry->created_at->diffForHumans() }}</p>
            </div>
            <div class="col-auto">
                <a class="btn text-white d-md-none d-inline-block" href="{{ route('spots', ['spot' => $entry->challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
    </div>
</div>
