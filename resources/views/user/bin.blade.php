@extends('layouts.app')

@push('title')Bin | @endpush

@section('description')View your bin on Parkour Hub.@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <nav class="navbar navbar-expand-md navbar-dark text-white bg-grey shadow-sm">
        <div class="container">
            <ul class="navbar-nav flex-row justify-content-around w-100">
                <li class="nav-item px-2">
                    <a class="nav-link @if(empty($tab) || $tab === 'spots')active @endif" href="{{ route('user_bin') }}">
                        <i class="fa fa-map-marker nav-icon"></i>
                        <span class="d-none d-lg-inline">Spots</span>
                    </a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link @if($tab === 'reviews')active @endif" href="{{ route('user_bin', ['tab' => 'reviews']) }}">
                        <i class="fa fa-star nav-icon"></i>
                        <span class="d-none d-lg-inline">Reviews</span>
                    </a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link @if($tab === 'comments')active @endif" href="{{ route('user_bin', ['tab' => 'comments']) }}">
                        <i class="fa fa-comment nav-icon"></i>
                        <span class="d-none d-lg-inline">Comments</span>
                    </a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link @if($tab === 'challenges')active @endif" href="{{ route('user_bin', ['tab' => 'challenges']) }}">
                        <i class="fa fa-bullseye nav-icon"></i>
                        <span class="d-none d-lg-inline">Challenges</span>
                    </a>
                </li>
                <li class="nav-item px-2">
                    <a class="nav-link @if($tab === 'entries')active @endif" href="{{ route('user_bin', ['tab' => 'entries']) }}">
                        <i class="fa fa-trophy nav-icon"></i>
                        <span class="d-none d-lg-inline">Entries</span>
                    </a>
                </li>
                @premium
                    <li class="nav-item px-2">
                        <a class="nav-link @if($tab === 'workouts')active @endif" href="{{ route('user_bin', ['tab' => 'workouts']) }}">
                            <i class="fa fa-running nav-icon"></i>
                            <span class="d-none d-lg-inline">Workouts</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($tab === 'movements')active @endif" href="{{ route('user_bin', ['tab' => 'movements']) }}">
                            <i class="fa fa-child nav-icon"></i>
                            <span class="d-none d-lg-inline">Movements</span>
                        </a>
                    </li>
                    <li class="nav-item px-2">
                        <a class="nav-link @if($tab === 'equipment')active @endif" href="{{ route('user_bin', ['tab' => 'equipment']) }}">
                            <i class="fa fa-dumbbell nav-icon"></i>
                            <span class="d-none d-lg-inline">Equipment</span>
                        </a>
                    </li>
                @endpremium
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h1 class="sedgwick text-center pt-3">{{ ucwords(str_replace('_', ' ', $tab ?? 'Spots')) }}</h1>
            </div>
        </div>
        @if($tab == null || $tab === 'spots')
            <div class="card-body bg-black">
                @foreach($spots->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $spot)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.spot')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                {{ $spots->links() }}
                @if (count($spots) === 0)
                    <p class="mb-0">You haven't got any deleted spots.</p>
                @endif
            </div>
        @elseif($tab === 'reviews')
            <div class="card-body bg-black">
                @foreach($reviews->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $review)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.review')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($reviews) === 0)
                    <p class="mb-0">You haven't got any deleted reviews.</p>
                @endif
            </div>
        @elseif($tab === 'comments')
            <div class="card-body bg-black">
                @foreach($comments->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $comment)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.comment')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($comments) === 0)
                    <p class="mb-0">You haven't  got any deleted comments.</p>
                @endif
            </div>
        @elseif($tab === 'challenges')
            <div class="card-body bg-black">
                @foreach($challenges->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $challenge)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.challenge')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($challenges) === 0)
                    <p class="mb-0">You haven't got any deleted challenges.</p>
                @endif
            </div>
        @elseif($tab === 'entries')
            <div class="card-body bg-black">
                @foreach($entries->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $entry)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.entry')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($entries) === 0)
                    <p class="mb-0">You haven't got any deleted challenge entries.</p>
                @endif
            </div>
        @elseif($tab === 'movements')
            <div class="card-body bg-black">
                @foreach($movements->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $movement)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.movement')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($movements) === 0)
                    <p class="mb-0">You haven't got any deleted movements.</p>
                @endif
            </div>
        @elseif($tab === 'equipment')
            <div class="card-body bg-black">
                @foreach($equipments->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $equipment)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.equipment')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($equipments) === 0)
                    <p class="mb-0">You haven't got any deleted equipment.</p>
                @endif
            </div>
        @elseif($tab === 'workouts')
            <div class="card-body bg-black">
                @foreach($workouts->chunk(4) as $chunk)
                    <div class="row">
                        @foreach($chunk as $workout)
                            <div class="col-xl-3 col-md-6 mb-4">
                                @include('components.bin.workout')
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if (count($workouts) === 0)
                    <p class="mb-0">You haven't got any deleted workouts.</p>
                @endif
            </div>
        @endif
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
