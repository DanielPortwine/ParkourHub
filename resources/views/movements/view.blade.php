@extends('layouts.app')

@push('title'){{ $originalMovement->name }} | @endpush

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
        @if(!empty($originalMovement->video))
            <div class="content-wrapper">
                <video controls>
                    <source src="{{ $originalMovement->video }}" type="video/{{ $originalMovement->video_type }}">
                </video>
            </div>
        @elseif(!empty($originalMovement->youtube))
            <div class="content-wrapper">
                <div class="youtube" data-id="{{ $originalMovement->youtube }}" data-start="{{ $originalMovement->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                </div>
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0"><span class="text-movement-{{ $originalMovement->category->colour }}">[{{ $originalMovement->category->name }}]</span> {{ $originalMovement->name }} @if($originalMovement->official)<sup class="text-green" title="Official"><i class="fa fa-gavel"></i></sup> @endif </h1>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if(Auth()->id() !== 1)
                            <a class="btn text-white" href="{{ route('movement_report', $originalMovement->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @else
                            @if(count($originalMovement->reports) > 0)
                                <a class="btn text-white" href="{{ route('report_discard', ['id' => $originalMovement->id, 'type' => 'App\Movement']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                            @endif
                            <a class="btn text-white" href="{{ route('movement_report_delete', $originalMovement->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                            @if(!$originalMovement->official)
                                <a class="btn text-white" href="{{ route('movement_officialise', $originalMovement->id) }}" title="Officialise"><i class="fa fa-gavel"></i></a>
                            @else
                                <a class="btn text-white" href="{{ route('movement_unofficialise', $originalMovement->id) }}" title="Unofficialise"><i class="fa fa-gavel"></i></a>
                            @endif
                        @endif
                        <a class="btn text-white" href="{{ route('spot_listing', ['movement' => $originalMovement->id]) }}" title="Spots With Movement"><i class="fa fa-map-marker"></i></a>
                        @if ($originalMovement->user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('movement_edit', $originalMovement->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    @if($originalMovement->type_id === 1)
                        <span>{{ count($originalMovement->spots) . (count($originalMovement->spots) === 1 ? ' spot' : ' spots') }} | {{ $originalMovement->created_at->format('jS M, Y') }}</span>
                    @elseif($originalMovement->type_id === 2)
                        <span>{{ count($originalMovement->moves) . (count($originalMovement->moves) === 1 ? ' move' : ' moves') }} | {{ $originalMovement->created_at->format('jS M, Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($originalMovement->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="fragment-link" id="content"></div>
    <div class="section">
        <div class="container">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        @if($originalMovement->type_id === 1)
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab == null || $tab === 'spots')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => null]) }}#content">Spots</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'progressions')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'progressions']) }}#content">Progressions</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'advancements')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'advancements']) }}#content">Advancements</a>
                            </li>
                            @premium
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'exercises')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'exercises']) }}#content">Exercises</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'baseline')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'baseline']) }}#content">Baseline</a>
                                </li>
                            @endpremium
                        @elseif($originalMovement->type_id === 2)
                            @premium
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab == null || $tab === 'equipment')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => null]) }}#content">Equipment</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'progressions')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'progressions']) }}#content">Progressions</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'advancements')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'advancements']) }}#content">Advancements</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'moves')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'moves']) }}#content">Moves</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn-link @if($tab === 'baseline')active @endif" href="{{ route('movement_view', ['id' => $originalMovement->id, 'tab' => 'baseline']) }}#content">Baseline</a>
                                </li>
                            @endpremium
                        @endif
                    </ul>
                </div>
                @if(($tab == null && $originalMovement->type_id === 1) || $tab === 'spots')
                    <div class="card-body bg-black">
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @if (count($originalMovement->spots) === 0)
                            <p class="mb-0">This movement has not been linked to any spots yet.</p>
                        @elseif(count($originalMovement->spots) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['spots']))
                                    <a class="btn btn-green w-75" href="?spots=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif(($tab == null && $originalMovement->type_id === 2) || $tab === 'equipment')
                    <div class="card-body bg-black">
                        @premium
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('equipment') border-danger @enderror ">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Link A Piece Of Equipment
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('movement_equipment_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="movement" value="{{ $originalMovement->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Equipment</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-equipment" name="equipment"></select>
                                                        <small>Select a piece of equipment that is either required or helpful for completing this exercise.</small>
                                                        @error('progression')
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
                                            <div class="card @error('name') border-danger @enderror @error('description') border-danger @enderror @error('image') border-danger @enderror @error('required') border-danger @enderror">
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
                                                    <form method="POST" action="{{ route('equipment_create') }}" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="movement" value="{{ $originalMovement->id }}">
                                                        <div class="form-group row">
                                                            <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                                            <div class="col-md-8">
                                                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" autocomplete="name" maxlength="25" value="{{ old('name') }}" required>
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
                                                        <div class="form-group row">
                                                            <label for="image" class="col-md-2 col-form-label text-md-right">Image</label>
                                                            <div class="col-md-8">
                                                                <input type="file" id="image" class="form-control-file @error('image') is-invalid @enderror" name="image" required>
                                                                @error('image')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
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
                            @if(!empty($request['equipment']))
                                {{ $equipments->links() }}
                            @endif
                            @foreach($equipments->chunk(2) as $chunk)
                                <div class="row">
                                    @foreach($chunk as $equipment)
                                        <div class="col-md-6 mb-4">
                                            @include('components.equipment')
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            @if(!empty($request['equipment']))
                                {{ $equipments->links() }}
                            @endif
                            @if (count($originalMovement->equipment) === 0)
                                <p class="mb-0">This exercise has not been linked to any equipment yet.</p>
                            @elseif(count($originalMovement->equipment) > 4)
                                <div class="col text-center mb-4">
                                    @if(empty($request['equipments']))
                                        <a class="btn btn-green w-75" href="?equipments=1#content">More</a>
                                    @else
                                        <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                    @endif
                                </div>
                            @endif
                        @endpremium
                    </div>
                @elseif($tab === 'progressions')
                    <div class="card-body bg-black">
                        @premium
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('progression') border-danger @enderror ">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Link A Progression
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('movements_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="advancement" value="{{ $originalMovement->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Movement</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-movements" name="progression"></select>
                                                        <small>Select a movement that will make this movement easier by mastering it.</small>
                                                        @error('progression')
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
                                                        <input type="hidden" name="type" value="{{ $originalMovement->type_id }}">
                                                        <input type="hidden" name="progression" value="{{ $originalMovement->id }}">
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
                        @endpremium
                        @if(!empty($request['progressions']))
                            {{ $progressions->links() }}
                        @endif
                        @foreach($progressions->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $movement)
                                    <div class="col-md-6 mb-4">
                                        @include('components.movement')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['progressions']))
                            {{ $progressions->links() }}
                        @endif
                        @if (count($originalMovement->progressions) === 0)
                            <p class="mb-0">This movement has not been linked to any similar easier movements yet.</p>
                        @elseif(count($originalMovement->progressions) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['progressions']))
                                    <a class="btn btn-green w-75" href="?progressions=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'advancements')
                    <div class="card-body bg-black">
                        @premium
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('advancement') border-danger @enderror ">
                                        <div class="card-header bg-green sedgwick card-hidden-body">
                                            <div class="row">
                                                <div class="col">
                                                    Link An Advancement
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fa fa-caret-down"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-grey text-white">
                                            <form method="POST" action="{{ route('movements_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="progression" value="{{ $originalMovement->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Movement</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-movements" name="advancement"></select>
                                                        <small>Select a movement that will be made easier by mastering this movement.</small>
                                                        @error('progression')
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
                                                        <input type="hidden" name="type" value="{{ $originalMovement->type_id }}">
                                                        <input type="hidden" name="advancement" value="{{ $originalMovement->id }}">
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
                        @endpremium
                        @if(!empty($request['advancements']))
                            {{ $advancements->links() }}
                        @endif
                        @foreach($advancements->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $movement)
                                    <div class="col-md-6 mb-4">
                                        @include('components.movement')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['advancements']))
                            {{ $advancements->links() }}
                        @endif
                        @if (count($originalMovement->advancements) === 0)
                            <p class="mb-0">This movement has not been linked to any similar harder movements yet.</p>
                        @elseif(count($originalMovement->advancements) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['advancements']))
                                    <a class="btn btn-green w-75" href="?advancements=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'exercises')
                    <div class="card-body bg-black">
                        @premium
                            <div class="row mb-4">
                                <div class="col">
                                    <div class="card @error('exercise') border-danger @enderror @error('category') border-danger @enderror @error('name') border-danger @enderror @error('description') border-danger @enderror @error('video') border-danger @enderror @error('youtube') border-danger @enderror">
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
                                            <form method="POST" action="{{ route('movement_exercise_link') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="move" value="{{ $originalMovement->id }}">
                                                <div class="form-group row">
                                                    <label for="title" class="col-md-2 col-form-label text-md-right">Exercise</label>
                                                    <div class="col-md-8">
                                                        <select class="select2-exercises" name="exercise"></select>
                                                        <small>Select an exercise that will improve your ability to perform this movement.</small>
                                                        @error('exercise')
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
                                                        <input type="hidden" name="type" value="2">
                                                        <input type="hidden" name="move" value="{{ $originalMovement->id }}">
                                                        <div class="form-group row">
                                                            <label class="col-md-2 col-form-label text-md-right">Category</label>
                                                            <div class="col-md-8 vertical-center">
                                                                <select class="select2-exercise-category" name="category"></select>
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
                            </div>
                        @endpremium
                        @if(!empty($request['exercises']))
                            {{ $exercises->links() }}
                        @endif
                        @foreach($exercises->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $movement)
                                    <div class="col-md-6 mb-4">
                                        @include('components.movement')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['exercises']))
                            {{ $exercises->links() }}
                        @endif
                        @if (count($originalMovement->exercises) === 0)
                            <p class="mb-0">This movement has not been linked to any exercises yet.</p>
                        @elseif(count($originalMovement->exercises) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['exercises']))
                                    <a class="btn btn-green w-75" href="?exercises=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'moves')
                    <div class="card-body bg-black">
                        @premium
                        <div class="row mb-4">
                            <div class="col">
                                <div class="card @error('move') border-danger @enderror @error('category') border-danger @enderror @error('name') border-danger @enderror @error('description') border-danger @enderror @error('video') border-danger @enderror @error('youtube') border-danger @enderror">
                                    <div class="card-header bg-green sedgwick card-hidden-body">
                                        <div class="row">
                                            <div class="col">
                                                Link A Move
                                            </div>
                                            <div class="col-auto">
                                                <i class="fa fa-caret-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body bg-grey text-white">
                                        <form method="POST" action="{{ route('movement_exercise_link') }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="exercise" value="{{ $originalMovement->id }}">
                                            <div class="form-group row">
                                                <label for="title" class="col-md-2 col-form-label text-md-right">Move</label>
                                                <div class="col-md-8">
                                                    <select class="select2-movements" name="move"></select>
                                                    <small>Select a move that will be improved by training this exercise.</small>
                                                    @error('move')
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
                                                    <input type="hidden" name="type" value="1">
                                                    <input type="hidden" name="exercise" value="{{ $originalMovement->id }}">
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
                                                            <small>The video must contain a demonstration of the move and nothing else!</small>
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
                        </div>
                        @endpremium
                        @if(!empty($request['moves']))
                            {{ $moves->links() }}
                        @endif
                        @foreach($moves->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $movement)
                                    <div class="col-md-6 mb-4">
                                        @include('components.movement')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['moves']))
                            {{ $moves->links() }}
                        @endif
                        @if (count($originalMovement->moves) === 0)
                            <p class="mb-0">This movement has not been linked to any exercises yet.</p>
                        @elseif(count($originalMovement->moves) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['moves']))
                                    <a class="btn btn-green w-75" href="?moves=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $originalMovement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'baseline')
                    <div class="card-body bg-black">
                        <div class="card">
                            <div class="card-header bg-green sedgwick">
                                Set Your Baseline
                            </div>
                            <div class="card-body bg-grey">
                                <div class="form-group row">
                                    <div class="col-md-10 offset-md-1">
                                        Your baseline is the highest combination of values that you are able to comfortably achieve. You should not struggle to complete this movement with the values set as your baseline nor should it feel very easy.
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('set_movement_baseline') }}">
                                    @csrf
                                    <input type="hidden" name="movement" value="{{ $originalMovement->id }}">
                                    <div class="form-group row">
                                        <div class="col-md-10 offset-md-1 movement-entry-fields">
                                            <div class="row">
                                                @foreach($baselineFields as $field)
                                                    @if(!empty($field->name))
                                                        <div class="col-md">
                                                            <label>{{ $field->label }}</label><br>
                                                            <input class="form-control" type="{{ $field->type }}" name="fields[{{ $field->id }}]" placeholder="{{ $field->unit }}" @if(isset($field->pivot->value))value="{{ $field->pivot->value }}" @endif>
                                                            <small>{{ $field->small_text }}</small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-10 offset-md-1">
                                            <button type="submit" class="btn btn-green">Set</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
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
        @if(($tab == null && $originalMovement->type_id === 2) || $tab === 'equipment')
            $.ajax({
                url: '/movements/getMovements',
                data: {
                    link: 'exerciseEquipment',
                    id: {{ $originalMovement->id }},
                },
                success: function (response) {
                    $('.select2-equipment').select2({
                        data: response,
                        width: '100%',
                    });
                },
            });
        @elseif($tab === 'progressions' || $tab === 'advancements')
            $.ajax({
                url: '/movements/getMovements',
                data: {
                    link: 'progressionAdvancement',
                    id: {{ $originalMovement->id }},
                    type: '{{ $originalMovement->type_id }}'
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
                    types: [{{ $originalMovement->type_id }}]
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
        @elseif($tab === 'exercises')
            $.ajax({
                url: '/movements/getMovements',
                data: {
                    link: 'moveExercise',
                    id: {{ $originalMovement->id }},
                    type: 2,
                },
                success: function (response) {
                    $('.select2-exercises').select2({
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
                    $('.select2-exercise-category').select2({
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
        @elseif($tab === 'moves')
            $.ajax({
                url: '/movements/getMovements',
                data: {
                    link: 'exerciseMove',
                    id: {{ $originalMovement->id }},
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
        @endif
    </script>
@endpush
