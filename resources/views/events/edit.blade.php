@extends('layouts.app')

@push('title')Edit Event | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Edit Event</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="name" maxlength="25" required value="{{ $event->name }}">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="description" class="col-md-2 col-form-label text-md-right">Description</label>
                                <div class="col-md-8">
                                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ $event->description }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="date_time" class="col-md-2 col-form-label text-md-right">Date & Time</label>
                                <div class="col-md-8">
                                    <input id="date_time" type="datetime-local" class="@error('date_time') is-invalid @enderror" name="date_time" required value="{{ Carbon\Carbon::parse($event->date_time)->format('Y-m-d H:i') }}">
                                    @error('date_time')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @if(!empty($event->thumbnail))
                                <div class="row">
                                    <label class="col-md-2 col-form-label text-md-right">Thumbnail</label>
                                    <div class="col-md-8">
                                        <img class="w-100 mb-2" src="{{ $event->thumbnail }}" alt="Image of the {{ $event->name }} event.">
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row">
                                @if(empty($event->thumbnail))<label class="col-md-2 col-form-label text-md-right">Thumbnail</label> @endif
                                <div class="col-lg-4 col-md-8 @if(!empty($event->thumbnail))offset-md-2 @endif">
                                    <input type="file" id="thumbnail" class="form-control-file @error('thumbnail') is-invalid border-danger @enderror" name="thumbnail">
                                    @error('thumbnail')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @if(!empty($event->video))
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
                                    <div class="col-md-8">
                                        <div class="content-wrapper">
                                            <video controls>
                                                <source src="{{ $event->video }}" type="video/{{ $event->video_type }}">
                                            </video>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!empty($event->youtube))
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
                                    <div class="col-md-8">
                                        <div class="content-wrapper">
                                            <div class="youtube" data-id="{{ $event->youtube }}" data-start="{{ $event->youtube_start }}">
                                                <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group row">
                                @if(empty($event->video) && empty($event->youtube))<label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>@endif
                                <div class="col-lg-4 col-md-8 @if(!empty($event->video) || !empty($event->youtube))offset-md-2 @endif">
                                    <input type="text"
                                           id="youtube"
                                           class="form-control @error('youtube') is-invalid border-danger @enderror"
                                           name="youtube"
                                           autocomplete="youtube"
                                           placeholder="e.g. https://youtu.be/QDIVrf2ZW0s"
                                    >
                                    @error('youtube')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="col-lg-4 col-md-8 offset-md-2 offset-lg-0">
                                    <input type="file" id="video" class="form-control-file @error('video') is-invalid border-danger @enderror" name="video">
                                    @error('video')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-2 col-form-label text-md-right">Spots</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-5-results form-control" name="spots[]" multiple="multiple">
                                        @foreach($spots as $spot)
                                            <option value="{{ $spot->id }}" @if(in_array($spot->id, $currentSpots))selected @endif>{{ $spot->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col offset-md-2">
                                    <small>You can only add public spots so that everyone can see them.</small>
                                </div>
                            </div>
                            <div class="row">
                                <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                <div class="col-md-8">
                                    <select name="visibility" class="form-control select2-no-search">
                                        @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                            <option value="{{ $key }}" @if($event->visibility === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-10 offset-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('link_access') is-invalid @enderror" type="checkbox" name="link_access" id="link_access" value="1" @if($event->link_access) checked @endif>
                                        <label class="form-check-label" for="link_access">Anyone with link can view</label>
                                        @error('link_access')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="accept_method" class="col-md-2 col-form-label text-md-right">Accept Method</label>
                                <div class="col-md-8">
                                    <select id="accept_method" name="accept_method" class="form-control select2-no-search">
                                        <option value="accept" @if($event->accept_method === 'accept')selected @endif>Accept</option>
                                        <option value="invite" @if($event->accept_method === 'invite')selected @endif>Invite</option>
                                        <option value="none" @if($event->accept_method === 'none')selected @endif>Anyone</option>
                                    </select>
                                </div>
                            </div>
                            <div id="invites" class="form-group row" style="display:none">
                                <label for="users" class="col-md-2 col-form-label text-md-right">Invite Users</label>
                                <div class="col-md-8 vertical-center">
                                    <select id="users" class="select2-5-results form-control" name="users[]" multiple="multiple">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @if(in_array($user->id, $attendees))selected @endif>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 offset-2">
                                    <button type="submit" class="btn btn-green">Save</button>
                                    <a class="btn btn-danger require-confirmation float-right">Delete</a>
                                    <input type="hidden" name="redirect" value="{{ session('redirect') ?? url()->previous() }}">
                                    <input type="submit" class="btn btn-danger d-none confirmation-button float-right" name="delete" value="Confirm">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection

@push('scripts')
    <script>
        function toggleInvites() {
            let method = $('#accept_method').val();
            if (method === 'invite') {
                $('#invites').show();
            } else {
                $('#invites').hide();
                $('#users').val('');
            }
        }

        $(document).ready(function () {
            $('#invites').hide();
            $('#accept_method').change(toggleInvites);
            toggleInvites();
        });
    </script>
@endpush
