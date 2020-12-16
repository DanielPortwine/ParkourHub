@extends('layouts.app')

@push('title')Create Workout | @endpush

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
                    <div class="card-header bg-green sedgwick">Create a Workout</div>
                    <div class="card-body bg-grey text-white">
                        <div class="mb-3">
                            <form method="POST">
                                @csrf
                                <div class="form-group row">
                                    <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                    <div class="col-md-8">
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="name" maxlength="25">
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
                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"></textarea>
                                        @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input @error('public') is-invalid @enderror" type="checkbox" name="public" id="public" value="1">
                                            <label class="form-check-label" for="public">Public</label>
                                            @error('private')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h3 class="separator sedgwick pb-2 mb-3">Movements</h3>
                                </div>
                                <div class="movements-container">
                                    {{-- dynamic select boxes --}}
                                </div>
                                <div class="form-group row mb-0">
                                    <div class="col-md-10 offset-md-2">
                                        <input type="submit" class="btn btn-green" name="create" value="Create" title="Create Workout">
                                        <input type="submit" class="btn btn-green" name="create-record" value="Create & Record" title="Create And Record Workout">
                                    </div>
                                </div>
                            </form>
                        </div>
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
        var count = 1,
            movements;
        function addMovementSelection(buttonCount = 0) {
            if (buttonCount > 0) {
                $('.btn-' + buttonCount).remove();
            }
            var currentCount = count;
            $('.movements-container').append(
                '<div class="movement" id="movement-' + currentCount + '">\n' +
                '    <div class="form-group row">\n' +
                '        <label class="col-md-2 col-form-label text-md-right">Movement</label>\n' +
                '        <div class="col-md-8 vertical-center">\n' +
                '            <select class="select2-movements-' + currentCount + '" name="movements[' + currentCount + '][movement]"></select>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div class="form-group row">' +
                '        <div class="col-md-8 offset-md-2 movement-fields-' + currentCount + '">\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>'
            );
            $('.select2-movements-' + currentCount).select2({
                data: movements,
                width: '100%',
            })
            .change(function () {
                var movement = $(this).val();
                $.ajax({
                    url: '/workouts/getMovementFields',
                    data: {
                        movement: movement,
                    },
                    success: function (response) {
                        $('.movement-fields-' + currentCount).html('');
                        if (response) {
                            $fieldsContainer = $('.movement-fields-' + currentCount)
                            $fieldsContainer.append(
                                '<div class="row"></div>'
                            );
                            for (field in response) {
                                var field = response[field];
                                $('.movement-fields-' + currentCount + ' .row').append(
                                    '<div class="col-md">\n' +
                                    '    <label>' + field.label + '</label><br>\n' +
                                    '    <input class="form-control" type="' + field.type + '" name="movements[' + currentCount + '][fields][' + field.id + ']" placeholder="' + field.unit + '">\n' +
                                    '    <small>' + field.smallText + '</small>\n' +
                                    '</div>'
                                );
                            }
                            if (currentCount === count - 1) {
                                $fieldsContainer.append(
                                    '<a class="btn btn-sm btn-green btn-' + currentCount + '" title="Add Movement" onclick="addMovementSelection(' + currentCount + ')"><i class="fa fa-plus"></i></a>'
                                );
                            }
                        }
                    },
                });
            });
            count++;
        }
        $.ajax({
            url: '/movements/getMovements',
            data: {
                link: 'AllMovements',
            },
            success: function (response) {
                movements = response;
                addMovementSelection();
            },
        });
    </script>
@endpush
