@extends('layouts.app')

@push('title'){{ $equipment->name }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100 z-10" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        @if(!empty($equipment->image))
            <div class="content-wrapper">
                <img class="full-content-content" src="{{ $equipment->image }}" alt="Image of the {{ $equipment->name }} challenge.">
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $equipment->name }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if(Auth()->id() !== 1)
                            <a class="btn text-white" href="{{ route('equipment_report', $equipment->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @else
                            @if(count($equipment->reports) > 0)
                                <a class="btn text-white" href="{{ route('report_discard', ['id' => $equipment->id, 'type' => 'App\Equipment']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                            @endif
                            <a class="btn text-white" href="{{ route('equipment_report_delete', $equipment->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                        @endif
                        <a class="btn text-white" href="{{ route('movement_listing', ['equipment' => $equipment->id]) }}" title="View Exercises With Equipment"><i class="fa fa-child"></i></a>
                        @if ($equipment->user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('equipment_edit', $equipment->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ count($equipment->movements) . (count($equipment->movements) === 1 ? ' exercise' : ' exercises') }} | {{ $equipment->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($equipment->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="fragment-link" id="content"></div>
    <div class="section">
        <div class="container">
            <div class="row mt-3">
                <div class="col">
                    <h2>Exercises</h2>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <div class="card @error('movement') border-danger @enderror @error('category') border-danger @enderror @error('name') border-danger @enderror @error('description') border-danger @enderror @error('video') border-danger @enderror @error('youtube') border-danger @enderror ">
                        <div class="card-header bg-green sedgwick card-hidden-body">
                            <div class="row">
                                <div class="col">
                                    Link An Exercise
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-grey text-white">
                            <form method="POST" action="{{ route('movement_equipment_link') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="equipment" value="{{ $equipment->id }}">
                                <div class="form-group row">
                                    <label for="title" class="col-md-2 col-form-label text-md-right">Exercise</label>
                                    <div class="col-md-8">
                                        <select class="select2-movements" name="movement"></select>
                                        <small>Select an exercise that uses this equipment.</small>
                                        @error('movement')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-green">Link</button>
                                    </div>
                                </div>
                            </form>
                            <div class="card @error('category') border-danger @enderror @error('name') border-danger @enderror @error('description') border-danger @enderror @error('video') border-danger @enderror @error('youtube') border-danger @enderror">
                                <div class="card-header bg-green sedgwick card-hidden-body">
                                    <div class="row">
                                        <div class="col">
                                            Can't find what you're looking for?
                                        </div>
                                        <div class="col-auto">
                                            <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-grey text-white">
                                    <form method="POST" action="{{ route('movement_store') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="equipment" value="{{ $equipment->id }}">
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
                                                <small>The video must contain a demonstration of the exercise and nothing else!</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-8 offset-md-2">
                                                <button type="submit" class="btn btn-green">Create & Link</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(!empty($request['movements']))
                {{ $movements->links() }}
            @endif
            @foreach($movements->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $movement)
                        <div class="col-md-6 mb-4">
                            @include('components.movement')
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if(!empty($request['movements']))
                {{ $movements->links() }}
            @endif
            <div class="row mb-4">
                @if (count($equipment->movements) === 0)
                    <div class="col">
                        <p class="mb-0">This equipment has not been linked to any exercises yet.</p>
                    </div>
                @elseif(count($equipment->movements) > 4)
                    <div class="col text-center">
                        @if(empty($request['movements']))
                            <a class="btn btn-green w-75" href="?movements=1#content">More</a>
                        @else
                            <a class="btn btn-green w-75" href="{{ route('equipment_view', $equipment->id) }}#content">Less</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection

@push('scripts')
    <script defer>
        var urlParams = new URLSearchParams(window.location.search);
        $.ajax({
            url: '/movements/getMovements',
            data: {
                link: 'equipmentExercise',
                id: {{ $equipment->id }},
                type: 2,
            },
            success: function (response) {
                $('.select2-movements').select2({
                    data: response,
                    width: '100%',
                });
            },
        });

        $.ajax({
            url: '/movements/getMovementCategories',
            data: {
                types: [2]
            },
            success: function (response) {
                $('.select2-movement-category').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
    </script>
@endpush
