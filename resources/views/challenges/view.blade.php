@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green">
                        <div class="row">
                            <span class="col sedgwick">{{ $challenge->name }}</span>
                            <span class="col-auto">
                                @if ($challenge->user->id === Auth()->id())
                                    <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                                @endif
                                <a class="btn text-white" href="{{ route('spot_view', $challenge->spot_id) }}" title="View"><i class="fa fa-eye"></i></a>
                                <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
                            </span>
                        </div>
                    </div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if(!empty($challenge->video))
                            <div class="row">
                                <div class="col">
                                    <div class="video-wrapper">
                                        <video controls>
                                            <source src="{{ $challenge->video }}" type="video/mp4">
                                        </video>
                                    </div>
                                </div>
                            </div>
                        @elseif(!empty($challenge->youtube))
                            <div class="row">
                                <div class="col">
                                    <div class="video-wrapper">
                                        <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $challenge->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-8">
                                {{ $challenge->description }}
                            </div>
                        </div>
                        <div class="row pt-4">
                            <div class="col">
                                <h2 class="sedgwick">Entries ({{ count($challenge->entries) }})</h2>
                            </div>
                        </div>
                        @if(!empty($winner))
                            <div class="card">
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
                        @if ($entered)
                            <p class="mb-3">You have entered this challenge.</p>
                        @else
                            <div class="card mb-4">
                                <div class="card-header bg-green sedgwick">Enter challenge</div>
                                <div class="card-body bg-grey text-white">
                                    <form method="POST" action="{{ route('challenge_enter', $challenge->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">Youtube link...</label>
                                            <div class="col-8">
                                                <input id="youtube" type="text" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s">
                                                @error('youtube')
                                                <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label text-md-right">...or .mp4 video</label>
                                            <div class="col-8">
                                                <input type="file" id="video" class="form-control-file" name="video">
                                                @error('video')
                                                <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-md-8 offset-2">
                                                <button type="submit" class="btn btn-green">Enter</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8 offset-2">
                                                <small>You may only enter once so please make sure you select the correct video.</small>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                        @foreach($challenge->entries->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $entry)
                                    <div class="col-md-4">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
