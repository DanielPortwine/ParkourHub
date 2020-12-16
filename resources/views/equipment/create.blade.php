@extends('layouts.app')

@push('title')Create Equipment | @endpush

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
                    <div class="card-header bg-green sedgwick">Create Equipment</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
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
                                <label class="col-md-2 col-form-label text-md-right">Image</label>
                                <div class="col-md-8">
                                    <input type="file" id="image" class="form-control-file @error('image') is-invalid @enderror" name="image" value="">
                                    @error('image')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col offset-md-2">
                                    <small>The image must clearly show the equipment!</small>
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
