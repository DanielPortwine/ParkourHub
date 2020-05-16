@extends('layouts.app')

@push('title'){{ $challenge->name }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="flex-center" id="full-content-title">
        <div class="text-center position-absolute">
            <h1 class="page-title sedgwick title-shadow">{{ $challenge->name }}</h1>
        </div>
        <div class="text-center bottom-centre title-shadow" id="scroll-arrow">
            <i class="fa fa-angle-double-down"></i>
        </div>
        <div class="full-content-section">
            @if(!empty($challenge->thumbnail))
                <img class="full-content-content" src="{{ $challenge->thumbnail }}" alt="Image of the {{ $challenge->name }} challenge.">
            @endif
        </div>
    </div>
    <div class="full-content-section d-none" id="full-content-video">
        <div class="full-content-content">
            @if(!empty($challenge->video))
                <div class="video-wrapper">
                    <video class="full-content-content" controls>
                        <source src="{{ $challenge->video }}" type="video/mp4">
                    </video>
                </div>
            @elseif(!empty($challenge->youtube))
                <div class="video-wrapper">
                    <iframe class="full-content-content" width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $challenge->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row py-3 border-subtle">
                <div class="col vertical-center">
                    <span>{{ $views . ($views === 1 ? ' view' : ' views') }} | {{ $challenge->created_at->format('jS M, Y') }}</span>
                </div>
                <div class="col-auto">
                    <a class="btn text-white" id="switch-title-button" title="Watch Video"><i class="fa fa-film"></i></a>
                    <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot->id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
            <div class="row py-3">
                <div class="col vertical-center">
                    <span class="large-text sedgwick">{{ $challenge->user->name }}</span>
                </div>
                <div class="col-auto">
                    @if ($challenge->user->id === Auth()->id())
                        <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @endif
                </div>
            </div>
            <div class="pb-4">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($challenge->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container">
            <div class="row py-4">
                <div class="col">
                    <h2 class="sedgwick subtitle mb-0">Entries</h2>
                </div>
            </div>
            @if(!empty($winner))
                <div class="card mb-4">
                    @if(!empty($winner->video))
                        <div class="video-wrapper">
                            <video controls>
                                <source src="{{ $winner->video }}" type="video/mp4">
                            </video>
                        </div>
                    @elseif(!empty($winner->youtube))
                        <div class="video-wrapper">
                            <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $winner->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    @endif
                    <div class="card-footer card-header-bottom bg-green" style="font-size: 25px">
                        <div class="row">
                            <span class="col-auto"><i class="fa fa-trophy"></i></span>
                            <span class="col text-center sedgwick">{{ $winner->user->name }}</span>
                            <span class="col-auto text-right"><i class="fa fa-trophy"></i></span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row mb-4">
                <div class="col">
                    <div class="card">
                        <div class="card-header bg-green sedgwick card-hidden-body">
                            <div class="row">
                                <div class="col">
                                    Enter Challenge
                                </div>
                                <div class="col-auto">
                                    <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-grey text-white">
                            @if ($entered)
                                <p class="mb-0">You have already entered this challenge.</p>
                            @else
                                <form method="POST" action="{{ route('challenge_enter', $challenge->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group row">
                                        <label class="col-md-2 col-form-label text-md-right">Youtube or Video</label>
                                        <div class="col-md-4">
                                            <input id="youtube" type="text" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
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
                                    <div class="row">
                                        <div class="col-md-8 offset-2">
                                            <small>You may only enter a challenge once so please make sure you select the correct video.</small>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-8 offset-2">
                                            <button type="submit" class="btn btn-green">Enter</button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            @foreach($challenge->entries->sortByDesc('created_at')->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $entry)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                @if(!empty($entry->video))
                                    <div class="video-wrapper">
                                        <video controls>
                                            <source src="{{ $entry->video }}" type="video/mp4">
                                        </video>
                                    </div>
                                @elseif(!empty($entry->youtube))
                                    <div class="video-wrapper">
                                        <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $entry->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                @endif
                                <div class="card-footer card-header-bottom bg-green">
                                    @if($entry->winner)
                                        <div class="row">
                                            <span class="col-auto"><i class="fa fa-trophy"></i></span>
                                            <span class="col text-center sedgwick">{{ $entry->user->name }}</span>
                                            <span class="col-auto text-right"><i class="fa fa-trophy"></i></span>
                                        </div>
                                    @else
                                        <div class="row">
                                            <span class="col sedgwick">{{ $entry->user->name }}</span>
                                            <span class="col-auto">
                                                @if($challenge->user_id === Auth()->id() && !$challenge->won)
                                                    <a class="btn text-white" href="{{ route('challenge_win', $entry->id) }}" title="Select Winner"><i class="fa fa-trophy"></i></a>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if (count($challenge->entries) === 0)
                <p>This challenge has no entries yet. Create one by clicking 'Enter Challenge' above.</p>
            @endif
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
