@extends('layouts.app')

@push('title'){{ $page }} | @endpush

@section('content')
    <div class="container-fluid pt-4">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row">
            <div class="col">
                <h1 class="sedgwick text-center pb-3">{{ $title }}</h1>
            </div>
        </div>
        @foreach($content->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $card)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.' . $component, array_merge([$component => $card], $options ?? []))
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
