@extends('layouts.app')

@push('title')Edit Review | @endpush

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
                    <div class="card-header bg-green sedgwick">Edit Review</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="rating" name="rating" value="{{ $review->rating }}">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Rating</label>
                                <div class="col-md-8 vertical-center">
                                    <div class="rating-stars @error('rating') is-invalid @enderror">
                                        <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                        <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                        <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                        <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                        <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                    </div>
                                    @error('rating')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="title" class="col-md-2 col-form-label text-md-right">Title</label>
                                <div class="col-md-8">
                                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" required autocomplete="title" maxlength="25" value="{{ $review->title }}">
                                    @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="review" class="col-md-2 col-form-label text-md-right">Review</label>
                                <div class="col-md-8">
                                    <textarea id="review" class="form-control @error('review') is-invalid @enderror" name="review" maxlength="255">{{ $review->review }}</textarea>
                                    @error('review')
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
                                            <option value="{{ $key }}" @if($review->visibility === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 offset-md-2">
                                    <button type="submit" class="btn btn-green">Submit</button>
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
