@extends('layouts.app')

@push('title'){{ $user->name }} - User | @endpush

@section('description')View user '{{ $user->name }}' on Parkour Hub.@endsection
@section('twitter-card-type'){{ 'summary_large_image' }}@endsection
@section('meta-media-content'){{ !empty($user->profile_image) ? url($user->profile_image) : '' }}@endsection

@section('content')
    @if (!empty(session('status')))
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
            @if(!empty($user->instagram) || !empty($user->youtube))
                <div class="bottom-right z-10 px-2 py-1 large-text" style="background:rgba(0, 0, 0, 0.1)">
                    @if(!empty($user->instagram))
                        <a href="https://www.instagram.com/{{ $user->instagram }}" target="_blank" title="Instagram"><i class="fa fa-instagram text-instagram mx-1"></i></a>
                    @endif
                    @if(!empty($user->youtube))
                        <a href="https://www.instagram.com/{{ $user->youtube }}" target="_blank" title="YouTube"><i class="fa fa-youtube text-youtube mx-1"></i></a>
                    @endif
                </div>
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
                                $followSetting = Auth()->check() ? setting('privacy_follow', 'nobody', Auth()->id()) : 'nobody';
                                $followRequests = Auth()->check() ? Auth()->user()->followers()->where('accepted', false)->pluck('follower_id')->toArray() : [];
                                $userFollowers = Auth()->check() ? Auth()->user()->followers()->where('accepted', true)->pluck('follower_id')->toArray() : [];
                            @endphp
                            @if(in_array($user->id, $followRequests))
                                <a class="accept-follower-button btn text-white" href="{{ route('user_accept_follower', $user->id) }}" title="Accept Follower"><i class="fa fa-check"></i></a>
                                <a class="reject-follower-button btn text-white" href="{{ route('user_reject_follower', $user->id) }}" title="Reject Follower"><i class="fa fa-times"></i></a>
                            @elseif(in_array($user->id, $userFollowers))
                                <a class="btn text-white" href="{{ route('user_remove_follower', $user->id) }}" title="Remove Follower"><i class="fa fa-ban"></i></a>
                            @endif
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
                            <a class="nav-link btn-link @if($tab === 'events')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'events']) }}" title="Events"><i class="fa fa-map-marked"></i></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'entries')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'entries']) }}" title="Challenge Entries"><i class="fa fa-trophy"></i></a>
                        </li>
                        @premium
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'workouts')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'workouts']) }}" title="Workouts"><i class="fa fa-running"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'movements')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'movements']) }}" title="Movements"><i class="fa fa-child"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'equipment')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'equipment']) }}" title="Equipment"><i class="fa fa-dumbbell"></i></a>
                            </li>
                        @endpremium
                        @php
                            $followListsSetting = setting('privacy_follow_lists', 'nobody', $user->id);
                            $settingFollowers = $user->followers()->pluck('follower_id')->toArray();
                        @endphp
                        @if($user->id === Auth()->id() || $followListsSetting === 'anybody' || ($followListsSetting === 'follower' && in_array(Auth()->id(), $settingFollowers)))
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'followers')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'followers']) }}" title="Followers"><i class="fa fa-users"></i></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-link @if($tab === 'following')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'following']) }}" title="Following"><i class="fa fa-user-friends"></i></a>
                            </li>
                        @endif
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
                        @endif
                        {{ $spots->links() }}
                    </div>
                @elseif($tab === 'hitlist')
                    <div class="card-body bg-black">
                        @foreach($hits->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    @php $spot = $spot->spot @endphp
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->hits) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no hits.</p>
                        @endif
                        {{ $hits->links() }}
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
                        @if ($userReviewsWithTextCount === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no reviews.</p>
                        @endif
                        {{ $reviews->links() }}
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @foreach($comments as $comment)
                            <div class="row">
                                <div class="col mb-4">
                                    @include('components.comment')
                                </div>
                            </div>
                        @endforeach
                        @if (count($user->comments) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no comments.</p>
                        @endif
                        {{ $comments->links() }}
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
                        @endif
                        {{ $challenges->links() }}
                    </div>
                @elseif($tab === 'events')
                    <div class="card-body bg-black">
                        @foreach($events->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $event)
                                    <div class="col-md-6 mb-4">
                                        @include('components.event')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->events) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no events.</p>
                        @endif
                        {{ $events->links() }}
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
                        @endif
                        {{ $entries->links() }}
                    </div>
                @elseif($tab === 'workouts')
                    <div class="card-body bg-black">
                        @foreach($workouts->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $workout)
                                    <div class="col-md-6 mb-4">
                                        @include('components.workout')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if (count($user->workouts) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no workouts.</p>
                        @endif
                        {{ $workouts->links() }}
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
                        @endif
                        {{ $movements->links() }}
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
                        @endif
                        {{ $equipments->links() }}
                    </div>
                @elseif($tab === 'followers' && ($user->id === Auth()->id() || $followListsSetting === 'anybody' || ($followListsSetting === 'follower' && in_array(Auth()->id(), $settingFollowers))))
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
                        @endif
                        {{ $followers->links() }}
                    </div>
                @elseif($tab === 'following' && ($user->id === Auth()->id() || $followListsSetting === 'anybody' || ($followListsSetting === 'follower' && in_array(Auth()->id(), $settingFollowers))))
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
                        @endif
                        {{ $following->links() }}
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
                        @endif
                        {{ $followRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
