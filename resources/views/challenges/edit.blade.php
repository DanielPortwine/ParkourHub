@extends('layouts.app')

@push('title')Edit Challenge | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Edit Challenge</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name" value="{{ $challenge->name }}">
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
                                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ $challenge->description }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <input type="hidden" id="difficulty" name="difficulty"  value="{{ $challenge->difficulty }}">
                                <label class="col-md-2 col-form-label text-md-right">Difficulty</label>
                                <div class="col-md-8 vertical-center">
                                    <div>
                                        <div class="rating-buttons w-100 @error('difficulty') is-invalid @enderror">
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-1"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-2"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-3"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-4"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-5"></i>
                                        </div>
                                        @error('difficulty')
                                        <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Youtube</label>
                                <div class="col-md-8">
                                    @if(!empty($challenge->youtube))
                                        <div class="form-group row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <div class="youtube" data-id="{{ $challenge->youtube }}" data-start="{{ $challenge->youtube_start }}">
                                                        <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <input id="youtube" type="text" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s">
                                    @error('youtube')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            @premium
                                <div class="form-group row">
                                    <label class="col-md-2 col-form-label text-md-right">or Video</label>
                                    <div class="col-md-8">
                                        @if(!empty($challenge->video))
                                            <div class="form-group row">
                                                <div class="col">
                                                    <div class="content-wrapper">
                                                        <video controls>
                                                            <source src="{{ $challenge->video }}" type="video/{{ $challenge->video_type }}">
                                                        </video>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <input type="file" id="video" class="form-control-file @error('video') is-invalid @enderror" name="video">
                                        @error('video')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            @endpremium
                            <div class="form-group row">
                                <div class="col-md-8 offset-2">
                                    <button type="submit" class="btn btn-green">Save</button>
                                    <a class="btn btn-danger require-confirmation float-right">Delete</a>
                                    <a class="btn btn-danger d-none confirmation-button float-right" href="{{ route('challenge_delete', $challenge->id) }}">Confirm</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
