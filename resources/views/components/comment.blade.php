@php
$like = Auth()->user()->spotCommentLikes->where('spot_comment_id', $comment->id)->first()
@endphp

<div class="card bg-grey">
    @if(!empty($comment->image))
        <div class="content-wrapper">
            <a href="{{ $comment->image }}">
                <img src="{{ $comment->image }}">
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
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $comment->user->id) }}">{{ $comment->user->name }}</a>
            </div>
            <div class="col-md-auto">
                @if($comment->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('spot_comment_edit', $comment->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                <a class="btn text-white like-spot-comment @if(!empty($like))d-none @endif" id="like-spot-comment-{{ $comment->id }}" title="Like"><i class="fa fa-thumbs-o-up"></i></a>
                <a class="btn text-white unlike-spot-comment @if(empty($like))d-none @endif" id="unlike-spot-comment-{{ $comment->id }}" title="Unlike"><i class="fa fa-thumbs-up"></i></a>
            </div>
        </div>
        <div class="row @if(!empty($comment->comment))border-subtle mb-2 @endif">
            <div class="col-md vertical-center">
                <span><span id="spot-comment-likes-{{ $comment->id }}">{{ count($comment->likes) . (count($comment->likes) === 1 ? ' like' : ' likes') }}</span> | {{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @if(!empty($comment->comment))
            <div class="row">
                <div class="col-md vertical-center">
                    <span>{{ $comment->comment }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
