@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">{{ $spot->name }}<span class="float-right">{{ $spot->private ? 'Private' : '' }}</span></div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if(!empty($spot->image))
                            <div class="row">
                                <div class="col">
                                    <img class="w-100" src="{{ $spot->image }}">
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col">
                                <p>{{ $spot->description }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <a class="btn btn-green" href="{{ route('spots', ['spot' => $spot->id]) }}">Locate</a>
                                @if ($spot->user->id === Auth()->id())
                                    <a class="btn btn-green" href="{{ route('spot_edit', $spot->id) }}">Edit</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
