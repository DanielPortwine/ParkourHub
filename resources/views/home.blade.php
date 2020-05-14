@extends('layouts.app')

@push('title')Home | @endpush

@section('content')
    <div class="pb-md-5 pb-sm-4">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center subtitle sedgwick">From your Hitlist</h1>
                </div>
            </div>
            <div class="row">
                @foreach($hitlist as $spot)
                    <div class="col-md-{{ 12/count($hitlist) }} mb-4">
                        @include('components.card', ['card' => $spot, 'type' => 'spot', 'spot' => $spot->spot_id])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="pb-md-5 pb-sm-4 section grey-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Latest Spots From {{ $hometownName }}</h1>
                </div>
            </div>
            @foreach($hometownSpots->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $spot)
                        <div class="col-md-{{ 12/count($chunk) }} mb-4">
                            @include('components.card', ['card' => $spot, 'type' => 'spot', 'spot' => $spot->id])
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="row">
                <div class="col text-center">
                    @if(count($hometownSpots) > 0)
                        <a class="btn btn-green w-75 mb-4" href="{{ route('hometown_spots') }}">View All</a>
                    @else
                        There are no spots in your hometown yet.
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="pb-4 section green-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Stats</h1>
                </div>
            </div>
            <div class="row text-center">
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
                <div class="col user-stats" title="Number Of Days Since Registration">
                    <i class="fa fa-clock-o text-white"></i>
                    {{ $userStats['age'] }}
                </div>
            </div>
        </div>
    </div>
    <div class="pb-md-5 pb-sm-4 section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center subtitle sedgwick">Latest Challenges From {{ $hometownName }}</h1>
                </div>
            </div>
            @foreach($recentChallenges->chunk(2) as $chunk)
                <div class="row">
                    @foreach($chunk as $challenge)
                        <div class="col-md-{{ 12/count($recentChallenges) }} mb-4">
                            @include('components.card', ['card' => $challenge, 'type' => 'challenge', 'spot' => $challenge->spot_id])
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="row">
                <div class="col text-center">
                    @if(count($recentChallenges) > 0)
                        <a class="btn btn-green w-75 mb-4" href="{{ route('hometown_challenges') }}">View All</a>
                    @else
                        There are no challenges in your hometown yet.
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
