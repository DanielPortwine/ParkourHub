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
        <div class="row">
            <div class="col vertical-center">
                <div class="row vertical-center">
                    @if(!empty($comment->user->profile_image))
                        <div class="col-auto pr-0">
                            <div class="profile-image-wrapper--component">
                                <a href="{{ $comment->user->profile_image }}"><img src="{{ $comment->user->profile_image }}" alt="Profile image of the user named {{ $comment->user->name }}."></a>
                            </div>
                        </div>
                    @endif
                    <div class="col">
                        <a class="btn-link large-text sedgwick" href="{{ route('user_view', $comment->user->id) }}">{{ $comment->user->name }}</a>
                    </div>
                </div>
            </div>
            <div class="col-auto vertical-center pl-0">
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($comment->user_id === Auth()->id())
                        <a class="dropdown-item text-white" href="{{ route('comment_edit', $comment->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        <a class="dropdown-item text-white" href="{{ route('comment_delete', $comment->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    @if(!empty($linkSpotOnComment) && $linkSpotOnComment)
                        <a class="dropdown-item text-white"
                           href="{{ route(strtolower(str_replace('App\Models\\', '', $comment->commentable_type)) . '_view', $comment->commentable_id) }}"
                           title="View {{ str_replace('App\Models\\', '', $comment->commentable_type) }}"
                        >
                            <i class="fa fa-eye nav-icon"></i>View {{ str_replace('App\Models\\', '', $comment->commentable_type) }}
                        </a>
                    @endif
                    @auth
                        <a class="dropdown-item text-white" href="{{ route('comment_report', $comment->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @endauth
                    @if(count($comment->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('comment_report_discard', $comment->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('comment_remove', $comment->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <div class="row @if(!empty($comment->comment))border-subtle mb-2 @endif">
            <div class="col-md vertical-center">
                <span>{{ $comment->created_at->diffForHumans() }}</span>
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
