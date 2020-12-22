@extends('layouts.app')

@push('title')Create Movement | @endpush

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
                    <div class="card-header bg-green sedgwick">Create Movement</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Type</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-movement-type @error('type') is-invalid border-danger @enderror" name="type">
                                        <option value="1">Move</option>
                                        <option value="2">Exercise</option>
                                    </select>
                                    @error('type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Category</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-movement-category @error('category') is-invalid border-danger @enderror" name="category"></select>
                                    @error('category')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid border-danger @enderror" name="name" autocomplete="title" maxlength="25" value="{{ old('name') }}" required>
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
                                    <textarea id="description" class="form-control @error('description') is-invalid border-danger @enderror" name="description" maxlength="255">{{ old('description') }}</textarea>
                                    @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-2 col-form-label text-md-right">YouTube or Video</label>
                                <div class="col-lg-4 col-md-8">
                                    <input type="text" id="youtube" class="form-control @error('youtube') is-invalid border-danger @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
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
                            <div class="form-group row">
                                <div class="col offset-md-2">
                                    <small>The video must contain a demonstration of the movement and nothing else!</small>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Fields</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-movement-fields @error('fields') is-invalid border-danger @enderror" name="fields[]" multiple="multiple"></select>
                                    @error('fields')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="visibility" class="col-12 col-form-label">Visibility</label>
                                <div class="col-12">
                                    <select name="visibility" class="form-control">
                                        @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                            <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
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

@section('footer')
    @include('components.footer')
@endsection

@push('scripts')
    <script defer>
        function updateCategoriesSelect(type) {
            $('.select2-movement-category').children('option').each(function() {
                $(this).remove();
            });
            $.ajax({
                url: '/movements/getMovementCategories',
                data: {
                    types: [type]
                },
                success: function (response) {
                    $('.select2-movement-category').select2({
                        data: response,
                        width: '100%',
                    });
                },
            });
        }
        $(document).ready(function() {
            $('.select2-movement-category').select2({width: '100%'});
            updateCategoriesSelect(1);
            $('.select2-movement-type').select2({width: '100%'}).change(function () {
                var type = $(this).val();
                updateCategoriesSelect(type);
            });
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
