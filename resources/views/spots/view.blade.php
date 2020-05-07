@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green">
                        <div class="row">
                            <span class="col sedgwick">{{ $spot->name }}</span>
                            <span class="col-auto">
                                @if ($spot->user->id === Auth()->id())
                                    <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                                @endif
                                @if(in_array($spot->id, array_keys($hitlist)))
                                    @if(empty($hitlist[$spot->id]))
                                        <a class="btn text-white" href="{{ route('tick_off_hitlist', $spot->id) }}" title="Tick Off Hitlist"><i class="fa fa-check"></i></a>
                                    @endif
                                @else
                                    <a class="btn text-white" href="{{ route('add_to_hitlist', $spot->id) }}" title="Add To Hitlist"><i class="fa fa-crosshairs"></i></a>
                                @endif
                                <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
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
                        <small>{{ $spot->private ? 'Private' : '' }}</small>
                        @if(!empty($spot->image))
                            <div class="row">
                                <div class="col">
                                    <img class="w-100" src="{{ $spot->image }}">
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col">
                                {{ $spot->description }}
                            </div>
                        </div>
                        <div class="row py-4">
                            <div class="col">
                                <h2 class="sedgwick">Challenges ({{ count($spot->challenges) }})</h2>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-green" href="{{ route('challenge_create', ['spot' => $spot->id]) }}" title="Create New Challenge"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        @foreach($spot->challenges->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-4">
                                        @include('components.card', ['card' => $challenge, 'type' => 'challenge', 'spot' => $challenge->spot_id])
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
