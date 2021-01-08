<div class="card bg-grey">
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
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $entry->challenge->id) }}">
                    @if($entry->winner)
                        <i class="fa fa-trophy"></i>
                    @endif
                    {{ $entry->challenge->name }}
                </a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <div>
                    <a class="btn text-white" href="{{ route('entry_recover', $entry->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                    <a class="btn text-white" href="{{ route('entry_remove', $entry->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-lg vertical-center">
                Deleted {{ $entry->deleted_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>
