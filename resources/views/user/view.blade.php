@extends('layouts.app')

@push('title'){{ $user->name }} - User | @endpush

@section('description')View user '{{ $user->name }}' on Parkour Hub.@endsection

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        <div class="cover-image-wrapper">
            @if(!empty($user->cover_image))
                <img src="{{ $user->cover_image }}" alt="Cover image of the user named {{ $user->name }}.">
            @endif
        </div>
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row py-3">
                @if(!empty($user->profile_image))
                    <div class="col-auto vertical-center">
                        <div class="profile-image-wrapper">
                            <a href="{{ $user->profile_image }}"><img src="{{ $user->profile_image }}" alt="Profile image of the user named {{ $user->name }}."></a>
                        </div>
                    </div>
                @endif
                <div class="col vertical-center">
                    <div class="container-fluid p-0">
                        <div class="row">
                            <div class="col">
                                <h2 class="sedgwick mb-0 large-text">{{ $user->name }}</h2>
                            </div>
                        </div>
                        @if($showHometown)
                            <div class="row d-none d-md-flex">
                                <div class="col">
                                    <p class="mb-0">{{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if ($user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('user_manage') }}" title="Manage"><i class="fa fa-pencil"></i></a>
                        @else
                            @php
                                $followSetting = setting('privacy_follow', 'nobody', $user->id);
                                $userFollowers = $user->followers()->pluck('follower_id')->toArray();
                            @endphp
                            @if(in_array(Auth()->id(), $userFollowers))
                                <a class="btn text-white" href="{{ route('user_unfollow', $user->id) }}" title="Unfollow"><i class="fa fa-user-times"></i></a>
                            @else
                                @if($followSetting === 'anybody')
                                    <a class="btn text-white" href="{{ route('user_follow', $user->id) }}" title="Follow"><i class="fa fa-user-plus"></i></a>
                                @elseif($followSetting === 'request')
                                    <a class="btn text-white" href="{{ route('user_follow', $user->id) }}" title="Request to follow"><i class="fa fa-user-plus"></i></a>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container-fluid container-lg p-0">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs justify-content-between">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'spots')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => null]) }}" title="Spots"><i class="fa fa-map-marker"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'hitlist')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'hitlist']) }}" title="Hitlist"><i class="fa fa-crosshairs"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'reviews')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'reviews']) }}" title="Reviews"><i class="fa fa-star"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'comments']) }}" title="Comments"><i class="fa fa-comment"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'challenges')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'challenges']) }}" title="Challenges"><i class="fa fa-bullseye"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'entries')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'entries']) }}" title="Challenge Entries"><i class="fa fa-trophy"></i></a>
                        </li>
                        @premium
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'movements')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'movements']) }}" title="Movements"><i class="fa fa-child"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'equipment')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'equipment']) }}" title="Equipment"><i class="fa fa-dumbbell"></i></a>
                            </li>
                        @endpremium
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'followers')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'followers']) }}" title="Followers"><i class="fa fa-users"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'following')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'following']) }}" title="Following"><i class="fa fa-user-friends"></i></a>
                        </li>
                        @if($user->id === Auth()->id())
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'follow_requests')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'follow_requests']) }}" title="Follow Requests"><i class="fa fa-user-clock"></i></a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="row">
                    <div class="col">
                        <h1 class="sedgwick text-center pt-3">{{ ucwords(str_replace('_', ' ', $tab ?? 'Spots')) }}</h1>
                    </div>
                </div>
                @if($tab == null || $tab === 'spots')
                    <div class="card-body bg-black">
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->spots) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no spots.</p>
                        @elseif(count($user->spots) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_spots') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'hitlist')
                    <div class="card-body bg-black">
                        @foreach($hits->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->hits) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no hits.</p>
                        @elseif(count($user->hits) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_hitlist') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'reviews')
                    <div class="card-body bg-black">
                        @foreach($reviews->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $review)
                                    <div class="col-md-6 mb-4">
                                        @include('components.review')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if ($user->reviews()->withText()->count() === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no reviews.</p>
                        @elseif($user->reviews()->withText()->count() > 6)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_reviews') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @foreach($comments->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $comment)
                                    <div class="col-md-6 mb-4">
                                        @include('components.comment')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->spotComments) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no comments.</p>
                        @elseif(count($user->spotComments) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_comments') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'challenges')
                    <div class="card-body bg-black">
                        @foreach($challenges->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-6 mb-4">
                                        @include('components.challenge')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->challenges) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no challenges.</p>
                        @elseif(count($user->challenges) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_challenges') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'entries')
                    <div class="card-body bg-black">
                        @foreach($entries->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $entry)
                                    <div class="col-md-6 mb-4">
                                        @include('components.entry')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->challengeEntries) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}not entered any challenges.</p>
                        @elseif(count($user->challengeEntries) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_entries') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'movements')
                    <div class="card-body bg-black">
                        @foreach($movements->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $movement)
                                    <div class="col-md-6 mb-4">
                                        @include('components.movement')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->movements) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no movements.</p>
                        @elseif(count($user->movements) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_movements') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'equipment')
                    <div class="card-body bg-black">
                        @foreach($equipments->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $equipment)
                                    <div class="col-md-6 mb-4">
                                        @include('components.equipment')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->equipment) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no equipment.</p>
                        @elseif(count($user->equipment) > 4)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_equipment') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'followers')
                    @php $pageUser = $user @endphp
                    <div class="card-body bg-black">
                        @foreach($followers->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @php $user = $pageUser @endphp
                        @if ($user->followers()->where('accepted', true)->count() === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no followers.</p>
                        @elseif($user->followers()->where('accepted', true)->count() > 10)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_followers') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'following')
                    @php $pageUser = $user @endphp
                    <div class="card-body bg-black">
                        @foreach($following->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @php $user = $pageUser @endphp
                        @if (count($user->following) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You are ' : 'This user is ' }}not following anyone.</p>
                        @elseif(count($user->following) > 10)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_following') }}">More</a>
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'follow_requests' && Auth()->id() === $user->id)
                    @php $pageUser = $user @endphp
                    <div class="card-body bg-black">
                        @foreach($followRequests->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @php $user = $pageUser @endphp
                        @if ($user->followers()->where('accepted', false)->count() === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no follow requests.</p>
                        @elseif($user->followers()->where('accepted', false)->count() > 10)
                            <div class="col text-center mb-4">
                                <a class="btn btn-green w-75" href="{{ route('user_follow_requests') }}">More</a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
