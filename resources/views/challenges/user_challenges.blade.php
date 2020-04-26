@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Challenges</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @foreach($challenges->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-green">
                                                <span class="sedgwick">{{ $challenge->name }}</span>
                                                <span class="float-right">
                                                    <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}"><i class="fa fa-pencil"></i></a>
                                                    <a class="btn text-white" href="{{ route('challenge_view', $challenge->id) }}"><i class="fa fa-eye"></i></a>
                                                </span>
                                            </div>
                                            @if(!empty($challenge->video))
                                                <div class="video-wrapper">
                                                    <video controls>
                                                        <source src="{{ $challenge->video }}" type="video/mp4">
                                                    </video>
                                                </div>
                                            @elseif(!empty($challenge->youtube))
                                                <div class="video-wrapper">
                                                    <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $challenge->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                </div>
                                            @endif
                                            <div class="card-body bg-grey text-white">
                                                {{ $challenge->description }}
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
