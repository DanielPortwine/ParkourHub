@extends('layouts.app')

@push('title'){{ $challenge->name }} | @endpush

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
        <div id="full-content-title">
            @if(!empty($challenge->thumbnail))
                <div class="content-wrapper">
                    <img class="full-content-content" src="{{ $challenge->thumbnail }}" alt="Image of the {{ $challenge->name }} challenge.">
                </div>
            @endif
        </div>
        <div class="d-none" id="full-content-video">
            <div class="content-wrapper">
                @if(!empty($challenge->video))
                    <video controls>
                        <source src="{{ $challenge->video }}" type="video/{{ $challenge->video_type }}">
                    </video>
                @elseif(!empty($challenge->youtube))
                    <div class="youtube" data-id="{{ $challenge->youtube }}" data-start="{{ $challenge->youtube_start }}">
                        <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4 pb-2">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $challenge->name }}</h1>
                </div>
                <div class="col-auto vertical-center d-md-flex d-none">
                    <div>
                        @for($circle = 1; $circle <= 5; $circle++)
                            <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                        @endfor
                    </div>
                </div>
                <div class="col-auto verical-center">
                    <a class="btn text-white" href="{{ route('challenge_report', $challenge->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                    @if(Auth()->id() === 1)
                        <a class="btn text-white" href="{{ route('challenge_delete', $challenge->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                        @if(count($challenge->reports) > 0)
                            <a class="btn text-white" href="{{ route('challenge_report_discard', $challenge->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                        @endif
                    @endif
                    <a class="btn text-white" id="switch-title-button" title="Watch Video"><i class="fa fa-film"></i></a>
                    <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot->id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                </div>
            </div>
            <div class="row pb-3 border-subtle">
                <div class="col-auto vertical-center d-md-none d-flex">
                    <div>
                        @for($circle = 1; $circle <= 5; $circle++)
                            <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="row pt-2">
                <div class="col vertical-center">
                    <a class="btn-link large-text sedgwick" href="{{ route('user_view', $challenge->user->id) }}">{{ $challenge->user->name }}</a>
                </div>
                @if ($challenge->user->id === Auth()->id())
                    <div class="col-auto">
                        <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    </div>
                @endif
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ count($challenge->views) . (count($challenge->views) === 1 ? ' view' : ' views') }} | {{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | {{ $challenge->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($challenge->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="fragment-link" id="entries"></div>
    <div class="section">
        <div class="container">
            <div class="row py-4">
                <div class="col">
                    <h2 class="sedgwick subtitle mb-0">Entries</h2>
                </div>
            </div>
            @if(!empty($winner))
                <div class="row mb-4">
                    <div class="col">
                        @include('components.entry', ['entry' => $winner, 'winnerHighlight' => true])
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

                                    @premium
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Youtube or Video</label>
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
                                    @else
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Youtube</label>
                                            <div class="col-md-4">
                                                <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s" value="{{ old('youtube') }}">
                                                @error('youtube')
                                                <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endpremium
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
            {{ $entries->links() }}
            @foreach($entries->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $entry)
                        <div class="col-md-6 mb-4">
                            @include('components.entry')
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if (count($challenge->entries) === 0)
                <p>This challenge has no entries yet. Create one by clicking 'Enter Challenge' above.</p>
            @endif
            {{ $entries->links() }}
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
