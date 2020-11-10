@extends('layouts.app')

@push('title'){{ $user->name }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100 z-10" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        @if(!empty($user->image))
            <div class="content-wrapper">
                <img class="full-content-content" src="{{ $user->image }}" alt="Image of the user named {{ $user->name }}.">
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0">{{ $user->name }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if ($user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('user_manage') }}" title="Manage"><i class="fa fa-pencil"></i></a>
                        @else
                            @php $userFollowers = $user->followers()->pluck('follower_id')->toArray(); @endphp
                            <a class="follow-user-button btn text-white @if(in_array(Auth()->id(), $userFollowers))d-none @endif" id="follow-user-{{ $user->id }}" title="Follow"><i class="fa fa-user-plus"></i></a>
                            <a class="unfollow-user-button btn text-white @if(!in_array(Auth()->id(), $userFollowers))d-none @endif" id="unfollow-user-{{ $user->id }}" title="Unfollow"><i class="fa fa-user-times"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row border-subtle pb-1 mb-2">
                @if(!empty($user->hometown_name) && (
                        (
                            setting('privacy_hometown', null, $user->id) === 'anybody' || (
                                setting('privacy_hometown', null, $user->id) === 'follower' &&
                                !empty($user->followers->firstWhere('id', Auth()->id()))
                            )
                        ) ||
                        $user->id === Auth()->id()
                    ))
                    <div class="col">
                        {{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}
                    </div>
                @endif
            </div>
            <div class="row text-center user-stats py-3">
                <div class="col" title="Number Of Spots Created">
                    <i class="fa fa-map-marker text-white"></i>
                    {{ count($user->spots) }}
                </div>
                <div class="col" title="Number Of Challenges Created">
                    <i class="fa fa-bullseye text-white"></i>
                    {{ count($user->challenges) }}
                </div>
                <div class="col" title="Number Of Spots Reviewed">
                    <i class="fa fa-star text-white"></i>
                    {{ count($user->reviews) }}
                </div>
                <div class="col" title="Number Of Comments On Spots">
                    <i class="fa fa-comment text-white"></i>
                    {{ count($user->spotComments) }}
                </div>
                <div class="col" title="Followers">
                    <i class="fa fa-group text-white"></i>
                    {{ $user->followers_quantified }}
                </div>
                <div class="col" title="Following">
                    <i class="fa fa-group text-white"></i>
                    {{ count($user->following) }}
                </div>
                <div class="col" title="Number Of Days Since Registration">
                    <i class="fa fa-clock-o text-white"></i>
                    {{ Carbon\Carbon::parse($user->email_verified_at)->diffInDays() }}
                </div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="container">
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
                        @if (count($user->textReviews) === 0)
                            <p class="mb-0">{{ $user->id === Auth()->id() ? 'You have ' : 'This user has ' }}no reviews.</p>
                        @elseif(count($user->textReviews) > 4)
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
