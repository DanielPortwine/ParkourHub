@extends('layouts.app')

@push('title')Edit Workout | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Edit Workout</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="mb-3">
                            <form method="POST">
                                @csrf
                                <div class="form-group row">
                                    <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                    <div class="col-md-8">
                                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="name" value="{{ $workout->name }}">
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
                                        <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ $workout->description }}</textarea>
                                        @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h3 class="separator sedgwick pb-2 mb-3">Movements</h3>
                                </div>
                                <div class="movement-entries-container">
                                    @php $count = 1 @endphp
                                    @foreach($workout->movements as $workoutMovement)
                                        <input type="hidden" name="movements[{{ $count }}][id]" value="{{ $workoutMovement->id }}">
                                        <div class="movement-entry" id="movement-entry-{{ $count }}">
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Movement</label>
                                                <div class="col-md-8 vertical-center">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="movements[{{ $count }}][movement]"
                                                           value="{{ $workoutMovement->movement->type->name . ': [' . $workoutMovement->movement->category->name . '] ' . $workoutMovement->movement->name }}"
                                                           disabled
                                                    >
                                                    <a class="btn btn-danger" href="{{ route('workout_movement_delete', $workoutMovement->id) }}"><i class="fa fa-trash"></i></a>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-8 offset-md-2 movement-entry-fields-{{ $count }}">
                                                    <div class="row">
                                                        @foreach($workoutMovement->movement->fields as $field)
                                                            @if(!empty($field->name))
                                                                @php $fieldName = $field->name @endphp
                                                                <div class="col-md">
                                                                    <label>{{ $field->label }}</label><br>
                                                                    <input class="form-control" type="{{ $field->type }}" name="movements[{{ $count }}][{{ $field->name }}]" placeholder="{{ $field->unit }}" value="{{ $workoutMovement->$fieldName }}">
                                                                    <small>{{ $field->small_text }}</small>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $count++ @endphp
                                    @endforeach
                                    {{-- dynamic select boxes --}}
                                </div>
                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-2">
                                        <input type="submit" class="btn btn-green" value="Save">
                                        <a class="btn btn-danger require-confirmation float-right">Delete</a>
                                        <a class="btn btn-danger d-none confirmation-button float-right" href="{{ route('workout_delete', $workout->id) }}">Confirm</a>
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

@push('scripts')
    <script defer>
        var count = {{ count($workout->movements) + 1 ?: 1 }},
            movements;
        function addMovementSelection(buttonCount = 0) {
            if (buttonCount > 0) {
                $('.btn-' + buttonCount).remove();
            }
            var currentCount = count;
            $('.movement-entries-container').append(
                '<div class="movement-entry" id="movement-entry-' + currentCount + '">\n' +
                '    <div class="form-group row">\n' +
                '        <label class="col-md-2 col-form-label text-md-right">Movement</label>\n' +
                '        <div class="col-md-8 vertical-center">\n' +
                '            <select class="select2-movements-' + currentCount + '" name="movements[' + currentCount + '][movement]"></select>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '    <div class="form-group row">' +
                '        <div class="col-md-8 offset-md-2 movement-entry-fields-' + currentCount + '">\n' +
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
                    url: '/workout/getMovementFields',
                    data: {
                        movement: movement,
                    },
                    success: function (response) {
                        if (response) {
                            $fieldsContainer = $('.movement-entry-fields-' + currentCount)
                            $fieldsContainer.append(
                                '<div class="row"></div>'
                            );
                            for (field in response) {
                                var field = response[field];
                                $('.movement-entry-fields-' + currentCount + ' .row').append(
                                    '<div class="col-md">\n' +
                                    '    <label>' + field.label + '</label><br>\n' +
                                    '    <input class="form-control" type="' + field.type + '" name="movements[' + currentCount + '][' + field.name + ']" placeholder="' + field.unit + '">\n' +
                                    '    <small>' + field.smallText + '</small>\n' +
                                    '</div>'
                                );
                            }
                            if (currentCount === count - 1) {
                                $fieldsContainer.append(
                                    '<a class="btn btn-sm btn-green btn-' + currentCount + '" title="Add Movement" onclick="addMovementSelection(' + currentCount + ')"><i class="fa fa-plus"></i></a>'
                                );
                            }
                        } else {
                            $('.movement-entry-fields-' + currentCount).html('');
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
                if ({{ count($workout->movements) }} > 0) {
                    for (x=1;x<={{ count($workout->movements) }};x++) {
                        $('.select2-movements-' + x).select2({
                            data: movements,
                            width: '100%',
                        })
                        .change(function () {
                            console.log(x);
                            var movement = $(this).val();
                            $.ajax({
                                url: '/workout/getMovementFields',
                                data: {
                                    movement: movement,
                                },
                                success: function (response) {
                                    if (response) {
                                        $fieldsContainer = $('.movement-entry-fields-' + x)
                                        $fieldsContainer.append(
                                            '<div class="row"></div>'
                                        );
                                        for (field in response) {
                                            var field = response[field];
                                            $('.movement-entry-fields-' + x + ' .row').append(
                                                '<div class="col-md">\n' +
                                                '    <label>' + field.label + '</label><br>\n' +
                                                '    <input class="form-control" type="' + field.type + '" name="movements[' + x + '][' + field.name + ']" placeholder="' + field.unit + '">\n' +
                                                '    <small>' + field.smallText + '</small>\n' +
                                                '</div>'
                                            );
                                        }
                                        if (x === count - 1) {
                                            $fieldsContainer.append(
                                                '<a class="btn btn-sm btn-green btn-' + x + '" title="Add Movement" onclick="addMovementSelection(' + x + ')"><i class="fa fa-plus"></i></a>'
                                            );
                                        }
                                    } else {
                                        $('.movement-entry-fields-' + x).html('');
                                    }
                                },
                            });
                        });
                    }
                }
                addMovementSelection();
            },
        });
    </script>
@endpush
