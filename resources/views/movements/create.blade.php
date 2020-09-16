@extends('layouts.app')

@push('title')Create Movement | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Create Movement</div>
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
                            <input type="hidden" name="type" value="1">
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Category</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-movement-category" name="category"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="title" maxlength="25" value="{{ old('name') }}" required>
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
                                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" maxlength="255">{{ old('description') }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
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
                            <div class="form-group row">
                                <div class="col offset-md-2">
                                    <small>The video must contain a demonstration of the movement and nothing else!</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Fields</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-movement-fields" name="fields[]" multiple="multiple"></select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 offset-md-2">
                                    <button type="submit" class="btn btn-green">Create</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script defer>
        $.ajax({
            url: '/movements/getMovementCategories',
            data: {
                types: [1]
            },
            success: function (response) {
                $('.select2-movement-category').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
        $.ajax({
            url: '/movements/getMovementFields',
            success: function (response) {
                $('.select2-movement-fields').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
    </script>
@endpush
