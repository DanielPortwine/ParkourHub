@extends('layouts.app')

@push('title'){{ Auth()->user()->name }}'s Hitlist | @endpush

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
                <h1 class="sedgwick text-center mb-0">Your Hitlist</h1>
            </div>
        </div>
        <div class="card-header card-header-black">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link btn-link @if(Route::currentRouteName() == 'user_hitlist')active @endif" href="{{ route('user_hitlist') }}"><span class="sedgwick">Hitlist</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-link @if(Route::currentRouteName() == 'user_hitlist_completed')active @endif" href="{{ route('user_hitlist_completed') }}"><span class="sedgwick">Ticked Off</span></a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            @foreach($hits->chunk(4) as $chunk)
                <div class="row">
                    @foreach($chunk as $hit)
                        <div class="col-xl-3 col-md-6 mb-4">
                            @include('components.spot', ['spot' => $hit->spot])
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if(count($hits) === 0)
                @if(Route::currentRouteName() == 'user_hitlist')
                    You haven't added any spots to your Hitlist yet
                @elseif(Route::currentRouteName() == 'user_hitlist_completed')
                    You haven't ticked off any spots from your Hitlist yet
                @endif
            @endif
        </div>
    </div>
@endsection
