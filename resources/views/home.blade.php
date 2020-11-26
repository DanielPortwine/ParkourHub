@extends('layouts.app')

@push('title')Home | @endpush

@section('content')
    <div class="container-fluid">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center subtitle sedgwick">Latest Spots From People You Follow</h1>
            </div>
        </div>
        @foreach($followedSpots->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $spot)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.spot')
                    </div>
                @endforeach
            </div>
            @break
        @endforeach
        <div class="row pb-4">
            <div class="col text-center">
                @if(count($followedSpots) > 4)
                    <a class="btn btn-green w-75" href="{{ route('spot_listing', ['following' => 'true']) }}">View All</a>
                @elseif(count($followedSpots) === 0)
                    There are no spots from people you follow.
                @endif
            </div>
        </div>
    </div>
    <div class="container-fluid section grey-section">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center sedgwick">Latest Challenges From People You Follow</h1>
            </div>
        </div>
        @foreach($followedChallenges->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $challenge)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.challenge')
                    </div>
                @endforeach
            </div>
            @break
        @endforeach
        <div class="row pb-4">
            <div class="col text-center">
                @if(count($followedChallenges) > 4)
                    <a class="btn btn-green w-75" href="{{ route('challenge_listing', ['following' => 'true']) }}">View All</a>
                @elseif(count($followedChallenges) === 0)
                    There are no challenges from people you follow.
                @endif
            </div>
        </div>
    </div>
    <div class="container-fluid section green-section">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center sedgwick">Stats</h1>
            </div>
        </div>
        <div class="row pb-3 text-center">
            <div class="col user-stats" title="Number Of Spots Created">
                <i class="fa fa-map-marker text-white"></i>
                {{ $userStats['spotsCreated'] }}
            </div>
            <div class="col user-stats" title="Number Of Challenges Created">
                <i class="fa fa-bullseye text-white"></i>
                {{ $userStats['challengesCreated'] }}
            </div>
            <div class="col user-stats" title="Number Of Spots On Hitlist Not Ticked Off">
                <i class="fa fa-square-o text-white"></i>
                {{ $userStats['uncompletedHits'] }}
            </div>
            <div class="col user-stats" title="Number Of Spots On Hitlist Ticked Off">
                <i class="fa fa-check-square-o text-white"></i>
                {{ $userStats['completedHits'] }}
            </div>
            <div class="col user-stats" title="Followers">
                <i class="fa fa-group text-white"></i>
                {{ $userStats['followers'] }}
            </div>
            <div class="col user-stats" title="Following">
                <i class="fa fa-group text-white"></i>
                {{ $userStats['following'] }}
            </div>
            <div class="col user-stats" title="Number Of Days Since Registration">
                <i class="fa fa-clock-o text-white"></i>
                {{ $userStats['age'] }}
            </div>
        </div>
    </div>
    @if(!empty(Auth()->user()->hometown))
    <div class="container-fluid section">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center sedgwick">Latest Spots From {{ $hometownName }}</h1>
            </div>
        </div>
        @foreach($hometownSpots->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $spot)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.spot')
                    </div>
                @endforeach
            </div>
            @break
        @endforeach
        <div class="row pb-4">
            <div class="col text-center">
                @if(count($hometownSpots) > 4)
                    <a class="btn btn-green w-75" href="{{ route('hometown_spots') }}">View All</a>
                @elseif(count($hometownSpots) === 0)
                    There are no spots in your hometown yet.
                @endif
            </div>
        </div>
    </div>
    <div class="container-fluid section grey-section">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center subtitle sedgwick">Latest Challenges From {{ $hometownName }}</h1>
            </div>
        </div>
        @foreach($hometownChallenges->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $challenge)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.challenge')
                    </div>
                @endforeach
            </div>
            @break
        @endforeach
        <div class="row pb-4">
            <div class="col text-center">
                @if(count($hometownChallenges) > 4)
                    <a class="btn btn-green w-75" href="{{ route('hometown_challenges') }}">View All</a>
                @elseif(count($hometownChallenges) === 0)
                    There are no challenges in your hometown yet.
                @endif
            </div>
        </div>
    </div>
    @endif
    <div class="container-fluid section">
        <div class="row my-3">
            <div class="col">
                <h1 class="text-center subtitle sedgwick">From your Hitlist</h1>
            </div>
        </div>
        @foreach($hitlist->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $spot)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.spot')
                    </div>
                @endforeach
            </div>
        @endforeach
        <div class="row pb-4">
            <div class="col text-center">
                @if(count($hitlist) > 4)
                    <a class="btn btn-green w-75" href="{{ route('user_hitlist') }}">View All</a>
                @elseif(count($hitlist) === 0)
                    You haven't added any spots to your Hitlist yet.
                @endif
            </div>
        </div>
    </div>
@endsection
