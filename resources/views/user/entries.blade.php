@extends('layouts.app')

@push('title'){{ Auth()->user()->name }}'s Challenge Entries | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Challenge Entries</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @foreach($entries->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $entry)
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-header bg-green">
                                                <div class="row">
                                                    <span class="col sedgwick">{{ $entry->challenge->name }}</span>
                                                    <span class="col-auto">
                                                        <a class="btn text-white" href="{{ route('challenge_view', $entry->challenge->id) }}" title="View"><i class="fa fa-eye"></i></a>
                                                    </span>
                                                </div>
                                            </div>
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
                                                    <span class="sedgwick">{{ $entry->user->name }}</span>
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
