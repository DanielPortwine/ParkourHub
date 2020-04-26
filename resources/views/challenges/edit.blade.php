@extends('layouts.app')

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
                                    <textarea id="description" class="form-control" name="description">{{ $challenge->description }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Youtube link...</label>
                                <div class="col-8">
                                    @if(!empty($challenge->youtube))
                                        <div class="row">
                                            <div class="col">
                                                <div class="video-wrapper">
                                                    <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $challenge->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
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
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">...or .mp4 video</label>
                                <div class="col-8">
                                    @if(!empty($challenge->video))
                                        <div class="row">
                                            <div class="col">
                                                <div class="video-wrapper">
                                                    <video controls>
                                                        <source src="{{ $challenge->video }}" type="video/mp4">
                                                    </video>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" id="video" class="form-control-file" name="video">
                                    @error('video')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
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
