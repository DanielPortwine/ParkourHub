@extends('layouts.app')

@push('title')Record a Workout {{ '- ' . $workout->name }} | @endpush

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
                    <div class="card-header bg-green sedgwick">Record a Workout{{ ' - ' . $workout->name }}</div>
                    <div class="card-body bg-grey text-white">
                        <div class="mb-3">
                            <form method="POST">
                                @csrf
                                <div class="movement-entries-container">
                                    @php $count = 1 @endphp
                                    @foreach($workout->movements as $workoutMovement)
                                        <input type="hidden" name="movements[{{ $count }}][id]" value="{{ $workoutMovement->id }}">
                                        <div class="movement-entry" id="movement-entry-{{ $count }}">
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Movement</label>
                                                <div class="col-md-8 vertical-center">
                                                    <input type="hidden" name="movements[{{ $count }}][movement]" value="{{ $workoutMovement->movement_id }}">
                                                    <input type="text"
                                                           class="form-control"
                                                           value="{{ $workoutMovement->movement->type->name . ': [' . $workoutMovement->movement->category->name . '] ' . $workoutMovement->movement->name }}"
                                                           disabled
                                                    >
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-md-8 offset-md-2 movement-entry-fields-{{ $count }}">
                                                    <div class="row">
                                                        @foreach($workoutMovement->fields as $field)
                                                            @if(!empty($field->field->name))
                                                                <div class="col-md">
                                                                    <label>{{ $field->field->label }}</label><br>
                                                                    <input class="form-control" type="{{ $field->field->type }}" name="movements[{{ $count }}][fields][{{ $field->field->id }}]" placeholder="{{ $field->field->unit }}" value="{{ $field->value }}">
                                                                    <small>{{ $field->field->small_text }}</small>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $count++ @endphp
                                    @endforeach
                                </div>
                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-2">
                                        <input type="submit" class="btn btn-green" value="Record" title="Record Workout">
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
