@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">My Spots</div>
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
                            <div class="card-deck mb-4">
                                @foreach($chunk as $spot)
                                    {{--<a href="{{ route('spot_view', $spot->id) }}"></a>--}}
                                    <div class="card my-spot-card" onclick="window.location = '{{ route('spot_view', $spot->id) }}'">
                                        <div class="card-header bg-green sedgwick">{{ $spot->name }}</div>
                                        <img src="{{ $spot->image }}" class="card-image-top w-100">
                                        <div class="card-body bg-grey text-white">
                                            <p class="card-text mt-auto">{{ $spot->description }}</p>
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
