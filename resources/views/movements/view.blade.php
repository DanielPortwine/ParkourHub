@extends('layouts.app')

@push('title'){{ $movement->name }} | @endpush

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
        @if(!empty($movement->video))
            <div class="content-wrapper">
                <video controls>
                    <source src="{{ $movement->video }}" type="video/{{ $movement->video_type }}">
                </video>
            </div>
        @elseif(!empty($movement->youtube))
            <div class="content-wrapper">
                <div class="youtube" data-id="{{ $movement->youtube }}" data-start="{{ $movement->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                </div>
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0 text-green"><span class="text-{{ $movement->category->class_name }}">[{{ $movement->category->name }}]</span> {{ $movement->name }} @if($movement->official)<sup class="text-green" title="Official"><i class="fa fa-gavel"></i></sup> @endif </h1>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if(Auth()->id() !== 1)
                            <a class="btn text-white" href="{{ route('movement_report', $movement->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @else
                            @if(count($movement->reports) > 0)
                                <a class="btn text-white" href="{{ route('report_discard', ['id' => $movement->id, 'type' => 'App\Movement']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                            @endif
                            <a class="btn text-white" href="{{ route('movement_report_delete', $movement->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                            @if(!$movement->official)
                                <a class="btn text-white" href="{{ route('movement_officialise', $movement->id) }}" title="Officialise"><i class="fa fa-gavel"></i></a>
                            @else
                                <a class="btn text-white" href="{{ route('movement_unofficialise', $movement->id) }}" title="Unofficialise"><i class="fa fa-gavel"></i></a>
                            @endif
                        @endif
                        <a class="btn text-white" href="{{ route('spot_listing', ['movement' => $movement->id]) }}" title="Spots With Movement"><i class="fa fa-map-marker"></i></a>
                        @if ($movement->user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('movement_edit', $movement->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ count($movement->spots) . (count($movement->spots) === 1 ? ' spot' : ' spots') }} | {{ $movement->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($movement->description)) !!}</p>
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
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'spots')active @endif" href="{{ route('movement_view', ['id' => $movement->id, 'tab' => null]) }}#content">Spots</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'progressions')active @endif" href="{{ route('movement_view', ['id' => $movement->id, 'tab' => 'progressions']) }}#content">Progressions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'advancements')active @endif" href="{{ route('movement_view', ['id' => $movement->id, 'tab' => 'advancements']) }}#content">Advancements</a>
                        </li>
                    </ul>
                </div>
                @if($tab == null || $tab === 'spots')
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
                        @if (count($movement->spots) === 0)
                            <p class="mb-0">This movement has not been linked to any spots yet.</p>
                        @elseif(count($movement->spots) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['spots']))
                                    <a class="btn btn-green w-75" href="?spots=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $movement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
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
                                                <input type="hidden" name="advancement" value="{{ $movement->id }}">
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
                                                    <form method="POST" action="{{ route('movement_create') }}" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="progression" value="{{ $movement->id }}">
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
                        @if (count($movement->progressions) === 0)
                            <p class="mb-0">This movement has not been linked to any similar easier movements yet.</p>
                        @elseif(count($movement->progressions) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['progressions']))
                                    <a class="btn btn-green w-75" href="?progressions=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $movement->id) }}#content">Less</a>
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
                                                <input type="hidden" name="progression" value="{{ $movement->id }}">
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
                                                    <form method="POST" action="{{ route('movement_create') }}" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="advancement" value="{{ $movement->id }}">
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
                        @if (count($movement->advancements) === 0)
                            <p class="mb-0">This movement has not been linked to any similar harder movements yet.</p>
                        @elseif(count($movement->advancements) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['advancements']))
                                    <a class="btn btn-green w-75" href="?advancements=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $movement->id) }}#content">Less</a>
                                @endif
                            </div>
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
