@extends('layouts.app')

@push('title'){{ Auth()->user()->name }}'s Spots | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Spots</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @foreach($spots->chunk(3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-4 mb-4">
                                        @include('components.card', ['card' => $spot, 'type' => 'spot', 'spot' => $spot->id])
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
