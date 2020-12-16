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
                        <div class="row">
                            <div class="col">
                                @if($showHometown)
                                    <p class="mb-0">{{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}</p>
                                @endif
                            </div>
                        </div>
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
        <div class="container-fluid container-md p-0 p-md-4">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'spots')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => null]) }}">Spots</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'challenges')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'challenges']) }}">Challenges</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'reviews')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'reviews']) }}">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'comments')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'comments']) }}">Comments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'followers')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'followers']) }}">Followers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab === 'following')active @endif" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'following']) }}">Following</a>
                        </li>
                    </ul>
                </div>
                @if($tab == null || $tab === 'spots')
                    <div class="card-body bg-black">
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @if (count($user->spots) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no spots.</p>
                        @elseif(count($user->spots) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['spots']))
                                    <a class="btn btn-green w-75" href="?spots=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'spots']) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'reviews')
                    <div class="card-body bg-black">
                        @if(!empty($request['reviews']))
                            {{ $reviews->links() }}
                        @endif
                        @foreach($reviews->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $review)
                                    <div class="col-md-6 mb-4">
                                        @include('components.review')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['reviews']))
                            {{ $reviews->links() }}
                        @endif
                        @if ($user->reviews()->withText()->count() === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no reviews.</p>
                        @elseif($user->reviews()->withText()->count() > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['reviews']))
                                    <a class="btn btn-green w-75" href="?reviews=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'reviews']) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'comments')
                    <div class="card-body bg-black">
                        @if(!empty($request['comments']))
                            {{ $comments->links() }}
                        @endif
                        @foreach($comments->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $comment)
                                    <div class="col-md-6 mb-4">
                                        @include('components.comment')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['comments']))
                            {{ $comments->links() }}
                        @endif
                        @if (count($user->spotComments) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no comments.</p>
                        @elseif(count($user->spotComments) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['comments']))
                                    <a class="btn btn-green w-75" href="?comments=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'comments']) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'challenges')
                    <div class="card-body bg-black">
                        @if(!empty($request['challenges']))
                            {{ $challenges->links() }}
                        @endif
                        @foreach($challenges->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $challenge)
                                    <div class="col-md-6 mb-4">
                                        @include('components.challenge')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['challenges']))
                            {{ $challenges->links() }}
                        @endif
                        @if (count($user->challenges) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no challenges.</p>
                        @elseif(count($user->challenges) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['challenges']))
                                    <a class="btn btn-green w-75" href="?challenges=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'challenges']) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'followers')
                    @php $pageUser = $user @endphp
                    <div class="card-body bg-black">
                        @if(!empty($request['followers']))
                            {{ $followers->links() }}
                        @endif
                        @foreach($followers->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['followers']))
                            {{ $followers->links() }}
                        @endif
                        @php $user = $pageUser @endphp
                        @if (count($user->followers) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no followers.</p>
                        @elseif(count($user->followers) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['followers']))
                                    <a class="btn btn-green w-75" href="?followers=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'followers']) }}">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @elseif($tab === 'following')
                    @php $pageUser = $user @endphp
                    <div class="card-body bg-black">
                        @if(!empty($request['following']))
                            {{ $following->links() }}
                        @endif
                        @foreach($following->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $user)
                                    <div class="col-md-6 mb-4">
                                        @include('components.user')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['following']))
                            {{ $following->links() }}
                        @endif
                        @php $user = $pageUser @endphp
                        @if (count($user->following) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You are ' : 'This user is ' }}not following anyone.</p>
                        @elseif(count($user->following) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['following']))
                                    <a class="btn btn-green w-75" href="?following=1">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('user_view', ['id' => $user->id, 'tab' => 'following']) }}">Less</a>
                                @endif
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
