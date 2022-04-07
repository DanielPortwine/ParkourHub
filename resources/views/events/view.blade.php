@extends('layouts.app')

@push('title'){{ $event->name }} - Event | @endpush

@section('description')View the '{{ $event->name }}' event on Parkour Hub.@endsection
@section('twitter-card-type'){{ 'summary_large_image' }}@endsection
@section('meta-media-content'){{ url($event->thumbnail) }}@endsection

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
            @if(!empty($event->video))
                <video controls>
                    <source src="{{ $event->video }}" type="video/{{ $event->video_type }}">
                </video>
            @elseif(!empty($event->youtube))
                <div class="youtube" data-id="{{ $event->youtube }}" data-start="{{ $event->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                </div>
            @elseif(!empty($event->thumbnail))
                <img class="full-content-content" src="{{ $event->thumbnail }}" alt="Image of the {{ $event->name }} event.">
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col">
                    {{ Carbon\Carbon::parse($event->date_time)->format('D, d M H:i') }}
                </div>
            </div>
            <div class="row pb-2">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $event->name }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    @if($event->user_id === Auth()->id() && $event->deleted_at !== null)
                        <a class="btn text-white" href="{{ route('event_recover', $event->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                        <a class="btn text-white" href="{{ route('event_remove', $event->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
                    @endif
                    @if($event->deleted_at === null)
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
                                @if(count($event->reports) > 0)
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
                    @endif
                </div>
            </div>
            <div class="row pt-2">
                <div class="col vertical-center">
                    @if(!empty($event->user->profile_image))
                        <div class="profile-image-wrapper--component pr-3">
                            <a href="{{ $event->user->profile_image }}"><img src="{{ $event->user->profile_image }}" alt="Profile image of the user named {{ $event->user->name }}."></a>
                        </div>
                    @endif
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $event->user->id) }}">{{ $event->user->name }}</a>
                </div>
            </div>
            <div class="row py-2 border-subtle">
                <div class="col">
                    <span>{{ Carbon\Carbon::parse($event->date_time)->diffForHumans(['options' => Carbon\Carbon::ONE_DAY_WORDS]) }} | {{ count($event->spots) . (count($event->spots) === 1 ? ' spot' : ' spots') }} | {{ count($event->attendees) . (count($event->attendees) === 1 ? ' attendee' : ' attendees') }}</span>
                </div>
            </div>
            <div class="py-3 border-subtle">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($event->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
            <div class="row py-3">
                <div class="col">
                    @if(empty($userAttendance) && ($event->accept_method === 'none' || $event->user_id === Auth()->id()))
                        <form method="POST" action="{{ route('event_attendee_store', $event->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="event" value="{{ $event->id }}">
                            <input type="hidden" name="user" value="{{ Auth()->id() }}">
                            <input type="hidden" name="accepted" value="true">
                            <button type="submit" class="btn btn-green">Attend</button>
                        </form>
                    @elseif(empty($userAttendance) && $event->accept_method === 'accept')
                        <div class="card @error('apply_comment') border-danger @enderror ">
                            <div class="card-header bg-green sedgwick card-hidden-body">
                                <div class="row">
                                    <div class="col">
                                        Apply To Attend
                                    </div>
                                    <div class="col-auto">
                                        <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body bg-grey text-white">
                                <form method="POST" action="{{ route('event_attendee_store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="event" value="{{ $event->id }}">
                                    <input type="hidden" name="user" value="{{ Auth()->id() }}">
                                    <div class="form-group row">
                                        <label for="comment" class="col-md-2 col-form-label text-md-right">Comment</label>
                                        <div class="col-md-8">
                                            <textarea id="comment" class="form-control @error('comment') is-invalid @enderror" name="comment" maxlength="255">{{ old('comment') }}</textarea>
                                            @error('comment')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                            <small>Briefly explain why you want to attend this event.</small>
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
                    @elseif(empty($userAttendance) && $event->accept_method === 'invite')
                        You have not been invited to this event.
                    @elseif(!empty($userAttendance) && $userAttendance->pivot->accepted == false && $event->accept_method === 'invite')
                        <form method="POST" action="{{ route('event_attendee_update', $event->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="user" value="{{ Auth()->id() }}">
                            <input type="hidden" name="accepted" value="true">
                            <button type="submit" class="btn btn-green">Accept Invite</button>
                        </form>
                    @elseif(!empty($userAttendance) && $userAttendance->pivot->accepted == false && $event->accept_method === 'accept')
                        Your request to attend is under review.
                    @elseif(!empty($userAttendance) && $userAttendance->pivot->accepted == true && $event->accept_method === 'invite' && $event->user_id !== Auth()->id())
                        <form method="POST" action="{{ route('event_attendee_update', $event->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="user" value="{{ Auth()->id() }}">
                            <input type="hidden" name="accepted" value="false">
                            <button type="submit" class="btn btn-danger">Cancel Attendance</button>
                        </form>
                    @else
                        <a href="{{ route('event_attendee_delete', ['event' => $event->id, 'user' => Auth()->id()]) }}" class="btn btn-danger">Cancel Attendance</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container-fluid container-lg p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'spots')active @endif" href="{{ route('event_view', ['id' => $event->id, 'tab' => null]) }}">Spots</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'attendees')active @endif" href="{{ route('event_view', ['id' => $event->id, 'tab' => 'attendees']) }}">Attendees</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('event_view', ['id' => $event->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                        @if($event->user_id === Auth()->id() && $event->accept_method === 'accept')
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'applicants')active @endif" href="{{ route('event_view', ['id' => $event->id, 'tab' => 'applicants']) }}">Applicants</a>
                            </li>
                        @endif
                    </ul>
                </div>
                @if($tab === 'spots')
                    <div class="card-body bg-black">
                        {{ $spots->links() }}
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($event->spots) === 0)
                            <p>This event has no spots.</p>
                        @endif
                        {{ $spots->links() }}
                    </div>
                @elseif($tab === 'attendees')
                    <div class="card-body bg-black">
                        {{ $attendees->links() }}
                        @foreach($attendees->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if ($attendeesCount === 0)
                            <p>This event has no attendees.</p>
                        @endif
                        {{ $attendees->links() }}
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @if(auth()->check() && $event->deleted_at === null)
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
                                                <input type="hidden" name="commentable_type" value="Event">
                                                <input type="hidden" name="commentable_id" value="{{ $event->id }}">
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
                        @if (count($event->comments) === 0)
                            <p class="mb-0">This event has no comments yet.@auth Create one by clicking 'Submit Comment' above.@else <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create one. @endauth</p>
                        @endif
                    </div>
                @elseif($tab === 'applicants' && $event->user_id === Auth()->id() && $event->accept_method === 'accept')
                    <div class="card-body bg-black">
                        {{ $applicants->links() }}
                        @foreach($applicants->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if ($applicantsCount === 0)
                            <p>This event has no applicants.</p>
                        @endif
                        {{ $applicants->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
