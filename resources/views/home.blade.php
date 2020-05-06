@extends('layouts.app')

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
                    <div class="col-md-{{ 12/count($hitlist) }} mb-2">
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
                    <h1 class="text-center sedgwick">Recent Spots</h1>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="row">
                        @foreach($recentSpots as $spot)
                            <div class="col-md-{{ 12/count($recentSpots) }} mb-2">
                                @include('components.card', ['card' => $spot, 'type' => 'spot', 'spot' => $spot->id])
                            </div>
                        @endforeach
                    </div>
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
                <div class="col user-stats">
                    <i class="fa fa-map-marker text-white"></i>
                    {{ $userStats['spotsCreated'] }}
                </div>
                <div class="col user-stats">
                    <i class="fa fa-bullseye text-white"></i>
                    {{ $userStats['challengesCreated'] }}
                </div>
                <div class="col user-stats">
                    <i class="fa fa-square-o text-white"></i>
                    {{ $userStats['uncompletedHits'] }}
                </div>
                <div class="col user-stats">
                    <i class="fa fa-check-square-o text-white"></i>
                    {{ $userStats['completedHits'] }}
                </div>
                <div class="col user-stats">
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
                    <h1 class="text-center subtitle sedgwick">Recent Challenges</h1>
                </div>
            </div>
            <div class="row">
                @foreach($recentChallenges as $challenge)
                    <div class="col-md-{{ 12/count($recentChallenges) }} mb-2">
                        @include('components.card', ['card' => $challenge, 'type' => 'challenge', 'spot' => $challenge->spot_id])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
