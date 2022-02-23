@extends('layouts.app')

@push('title'){{ $challenge->name }} - Challenge | @endpush

@section('description')View the '{{ $challenge->name }}' parkour challenge on Parkour Hub.@endsection
@section('twitter-card-type'){{ 'summary_large_image' }}@endsection
@section('meta-media-content'){{ url($challenge->thumbnail) }}@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        <div class="content-wrapper">
            @if(!empty($challenge->video))
                <video controls>
                    <source src="{{ $challenge->video }}" type="video/{{ $challenge->video_type }}">
                </video>
            @elseif(!empty($challenge->youtube))
                <div class="youtube" data-id="{{ $challenge->youtube }}" data-start="{{ $challenge->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                </div>
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4 pb-2">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $challenge->name }}</h1>
                </div>
                <div class="col-auto vertical-center d-md-flex d-none">
                    <div>
                        @for($circle = 1; $circle <= 5; $circle++)
                            <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                        @endfor
                    </div>
                </div>
                <div class="col-auto vertical-center">
                    @if($challenge->user_id === Auth()->id() && $challenge->deleted_at !== null)
                        <a class="btn text-white" href="{{ route('challenge_recover', $challenge->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                        <a class="btn text-white" href="{{ route('challenge_remove', $challenge->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                    @endif
                    @if($challenge->deleted_at === null)
                        @if(!empty($challenge->spot))
                            <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot->id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                        @endif
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
                            @if(count($challenge->reports) > 0)
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
                        </div>
                    @endif
                </div>
            </div>
            <div class="row pb-3 border-subtle">
                <div class="col-auto vertical-center d-md-none d-flex">
                    <div>
                        @for($circle = 1; $circle <= 5; $circle++)
                            <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="row pt-2">
                <div class="col vertical-center">
                    @if(!empty($challenge->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $challenge->user->profile_image }}"><img src="{{ $challenge->user->profile_image }}" alt="Profile image of the user named {{ $challenge->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $challenge->user->id) }}">{{ $challenge->user->name }}</a>
                </div>
            </div>
            <div class="row py-2 border-subtle">
                <div class="col">
                    <span>{{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | {{ $challenge->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($challenge->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="fragment-link" id="entries"></div>
    <div class="section">
        <div class="container-fluid container-lg p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'entries')active @endif" href="{{ route('challenge_view', ['id' => $challenge->id, 'tab' => null]) }}">Entries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('challenge_view', ['id' => $challenge->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                    </ul>
                </div>
                @if($tab === 'entries')
                    <div class="card-body bg-black">
                        @if(!empty($winner))
                            <div class="row mb-4">
                                <div class="col">
                                    @include('components.entry', ['entry' => $winner, 'winnerHighlight' => true])
                                </div>
                            </div>
                        @endif
                        @if(auth()->check() && $challenge->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Enter Challenge
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            @if ($entered)
                                                <p class="mb-0">You have already entered this challenge.</p>
                                            @else
                                                <form method="POST" action="{{ route('entry_store') }}" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="challenge" value="{{ $challenge->id }}">
                                                    @premium
                                                        <div class="form-group row">
                                                            <label class="col-md-2 col-form-label text-md-right">Youtube or Video</label>
                                                            <div class="col-md-4">
                                                                <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                                @error('youtube')
                                                                <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-4">
                                                                <input type="file" id="video" class="form-control-file @error('video') is-invalid @enderror" name="video">
                                                                @error('video')
                                                                <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="form-group row">
                                                            <label class="col-md-2 col-form-label text-md-right">Youtube</label>
                                                            <div class="col-md-4">
                                                                <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                                @error('youtube')
                                                                <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    @endpremium
                                                    <div class="row">
                                                        <div class="col-md-8 offset-2">
                                                            <small>You may only enter a challenge once so please make sure you select the correct video.</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-md-8 offset-2">
                                                            <button type="submit" class="btn btn-green">Enter</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($challenge->deleted_at === null)
                            <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to enter.
                        @endif
                        {{ $entries->links() }}
                        @foreach($entries->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $entry)
                                    <div class="col-md-6 mb-4">
                                        @include('components.entry')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($challenge->entries) === 0)
                            <p>This challenge has no entries yet.@auth Create one by clicking 'Enter Challenge' above.@endauth </p>
                        @endif
                        {{ $entries->links() }}
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $challenge->deleted_at === null)
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('comment') border-danger @enderror @error('image') border-danger @enderror @error('youtube') border-danger @enderror @error('video') border-danger @enderror">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Submit Comment
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('comment_store') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="commentable_type" value="Challenge">
                                                <input type="hidden" name="commentable_id" value="{{ $challenge->id }}">
                                                <div class="form-group row">
                                                    <label for="comment" class="col-md-2 col-form-label text-md-right">Comment</label>
                                                    <div class="col-md-8">
                                                        <textarea id="comment" class="form-control @error('comment') is-invalid @enderror" name="comment" maxlength="255">{{ old('comment') }}</textarea>
                                                        @error('comment')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    @premium
                                                    <label class="col-md-2 col-form-label text-md-right">Youtube, Video or Image</label>
                                                    @else
                                                        <label class="col-md-2 col-form-label text-md-right">Youtube or Image</label>
                                                        @endpremium
                                                        <div class="col-md-4">
                                                            <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                            @error('youtube')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="file" id="video_image" class="form-control-file @error('video_image') is-invalid @enderror" name="video_image">
                                                            @error('video_image')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                            @enderror
                                                        </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                                    <div class="col-md-8">
                                                        <select name="visibility" class="form-control select2-no-search">
                                                            @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                                                <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-md-8 offset-md-2">
                                                        <button type="submit" class="btn btn-green">Submit</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{ $comments->links() }}
                        @foreach($comments as $comment)
                            <div class="row">
                                <div class="col mb-4">
                                    @include('components.comment')
                                </div>
                            </div>
                        @endforeach
                        {{ $comments->links() }}
                        @if (count($challenge->comments) === 0)
                            <p class="mb-0">This spot has no comments yet.@auth Create one by clicking 'Submit Comment' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
