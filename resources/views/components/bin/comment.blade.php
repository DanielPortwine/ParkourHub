<div class="card bg-grey">
    @if(!empty($comment->image))
        <div class="content-wrapper">
            <a href="{{ $comment->image }}">
                <img class="lazyload" data-src="{{ $comment->image }}" alt="Image of the {{ $comment->id }} comment.">
            </a>
        </div>
    @elseif(!empty($comment->video))
        <div class="content-wrapper">
            <video controls>
                <source src="{{ $comment->video }}" type="video/{{ $comment->video_type }}">
            </video>
        </div>
    @elseif(!empty($comment->youtube))
        <div class="content-wrapper">
            <div class="youtube" data-id="{{ $comment->youtube }}" data-start="{{ $comment->youtube_start }}">
                <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
            </div>
        </div>
    @endif
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col sedgwick">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('spot_view', $comment->spot_id) }}">{{ $comment->spot->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('spot_comment_recover', $comment->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                @if(!empty($linkSpotOnComment) && $linkSpotOnComment)
                    <a class="btn text-white" href="{{ route('spot_view', $comment->spot_id) }}" title="View Spot"><i class="fa fa-map-marker"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('spot_comment_remove', $comment->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        @if(!empty($comment->comment))
            <div class="row">
                <div class="col-md vertical-center">
                    <span>{{ $comment->comment }}</span>
                </div>
            </div>
        @endif
        <div class="row mt-2">
            <div class="col-md vertical-center">
                <span>Deleted {{ $comment->deleted_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
