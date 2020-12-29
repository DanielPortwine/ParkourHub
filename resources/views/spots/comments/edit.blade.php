@extends('layouts.app')

@push('title')Edit Spot Comment | @endpush

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
                    <div class="card-header bg-green sedgwick">Edit Spot Comment</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" action="{{ route('spot_comment_update', $comment->id) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="spot" value="{{ $comment->spot->id }}">
                            <div class="form-group row">
                                <label for="comment" class="col-md-2 col-form-label text-md-right">Comment</label>
                                <div class="col-md-8">
                                    <textarea id="comment" class="form-control @error('comment') is-invalid @enderror" name="comment" maxlength="255">{{ $comment->comment }}</textarea>
                                    @error('comment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Youtube{{ Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium') ? ', Video' : '' }} or Image</label>
                                <div class="col-md-8">
                                    @if(!empty($comment->youtube))
                                        <div class="form-group row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <div class="youtube" data-id="{{ $comment->youtube }}" data-start="{{ $comment->youtube_start }}">
                                                        <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(!empty($comment->video))
                                        <div class="form-group row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <video controls>
                                                        <source src="{{ $comment->video }}" type="video/{{ $comment->video_type }}">
                                                    </video>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(!empty($comment->image))
                                        <div class="form-group row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <img src="{{ $comment->image }}" alt="Image of the {{ $comment->id }} comment.">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-lg-6 mb-2 mb-lg-0">
                                            <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                            @error('youtube')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="file" id="video_image" class="form-control-file @error('video_image') is-invalid @enderror" name="video_image">
                                            @error('video_image')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                <div class="col-md-8">
                                    <select name="visibility" class="form-control visibility-select">
                                        @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                            <option value="{{ $key }}" @if($comment->visibility === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 offset-md-2">
                                    <button type="submit" class="btn btn-green">Submit</button>
                                    <a class="btn btn-danger require-confirmation float-right">Delete</a>
                                    <a class="btn btn-danger d-none confirmation-button float-right" href="{{ route('spot_comment_delete', $comment->id) }}">Confirm</a>
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
