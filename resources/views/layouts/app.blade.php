<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Parkour Hub is a place for people to share their spots, challenges, moves and more. There is also a premium membership that boasts a full training system to help users develop their skills and achieve their goals.')">
    <meta name="twitter:card" content="@yield('twitter-card-type', 'summary')">
    <meta property="og:site-name" content="Parkour Hub">
    <meta property="og:title" content="@stack('title'){{ config('app.name') }}">
    <meta property="og:description" content="@yield('description', 'Parkour Hub is a place for people to share their spots, challenges, moves and more.')">
    <meta property="og:url" content="{{ Request()->url() }}">
    <meta property="og:@yield('meta-media-type', 'image')" content="@yield('meta-media-content', url('/favicon.png'))">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@stack('title'){{ config('app.name') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.js"></script>
    @stack('scripts')

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://kit.fontawesome.com/014efa2ea8.js" crossorigin="anonymous"></script>

    <!-- Favicon -->
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div id="app">
        <nav class="navbar sticky-top navbar-expand-md navbar-dark text-white bg-grey shadow-sm">
            <div class="container">
                <a class="navbar-brand sedgwick" href="{{ url('/') }}">
                    {{ config('app.name') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav site-search">
                        <form class="mt-2 mt-md-0 mr-md-2 w-100 @if(!empty($_GET['search']))d-none @endif d-md-block" id="site-search-form" action="{{ strpos(Route::currentRouteName(), '_listing') > 0 && Route::currentRouteName() !== 'report_listing' ? route(Route::currentRouteName()) : route('spot_listing') }}" method="GET">
                            <div class="input-group w-100">
                                <input type="text" class="form-control @error('search') is-invalid @enderror" name="search" placeholder="Search" aria-label="from" aria-describedby="from" value="{{ $_GET['search'] ?? '' }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-green input-group-text" title="Search"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('spots') }}"><i class="fa fa-map nav-icon"></i><span class="d-inline d-md-none">Map</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('spot_listing') }}"><i class="fa fa-map-marker nav-icon"></i><span class="d-inline d-md-none">Spots</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('challenge_listing') }}"><i class="fa fa-bullseye nav-icon"></i><span class="d-inline d-md-none">Challenges</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('event_listing') }}"><i class="fa fa-map-marked nav-icon"></i><span class="d-inline d-md-none">Events</span></a>
                        </li>
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('premium') }}"><i class="fa fa-diamond nav-icon text-premium"></i><span class="d-inline d-md-none">Premium</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}"><i class="fa fa-user nav-icon"></i><span class="d-inline d-md-none">{{ __('Login') }}</span></a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}"><i class="fa fa-user-plus nav-icon"></i><span class="d-inline d-md-none">{{ __('Register') }}</span></a>
                                </li>
                            @endif
                        @else
                            @premium
                                <li class="nav-item dropdown">
                                    <a id="premium-dropdown" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        <i class="fa fa-diamond nav-icon text-premium"></i><span class="d-inline d-md-none">Premium</span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right bg-grey" id="notification-menu" aria-labelledby="notification-dropdown">
                                        <a class="dropdown-item text-white" href="{{ route('workout_listing') }}"><i class="fa fa-running nav-icon"></i>Workouts</a>
                                        <a class="dropdown-item text-white" href="{{ route('movement_listing') }}"><i class="fa fa-child nav-icon"></i>Movements</a>
                                        <a class="dropdown-item text-white" href="{{ route('equipment_listing') }}"><i class="fa fa-dumbbell nav-icon"></i>Equipment</a>
                                        <a class="dropdown-item text-white" href="{{ route('workout_plan') }}"><i class="fa fa-calendar nav-icon"></i>Workout Plan</a>
                                        <a class="dropdown-item text-white" href="{{ route('premium') }}"><i class="fa fa-diamond nav-icon"></i>Manage</a>
                                    </div>
                                </li>
                            @endpremium
                            <li class="nav-item dropdown">
                                @php
                                    $unreadNotifications = Auth::user()->unreadNotifications;
                                @endphp
                                <a id="notification-dropdown" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fa {{ count($unreadNotifications) > 0 ? 'fa-bell' : 'fa-bell-o' }} nav-icon"></i><span class="d-md-none">Notifications</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right bg-grey" id="notification-menu" aria-labelledby="notification-dropdown">
                                    @if(count($unreadNotifications) == 0)
                                        <a class="dropdown-item text-white no-hover">No new notifications</a>
                                    @endif
                                    @foreach($unreadNotifications as $notification)
                                        @if(!empty($notification->data['review']))
                                            <a class="dropdown-item text-white" href="{{ route('spot_view', ['id' => $notification->data['review']['spot_id'], 'notification' => $notification->id]) }}">New review on {{ $notification->data['review']['spot']['name'] }}</a>
                                        @elseif(!empty($notification->data['comment']))
                                            <a class="dropdown-item text-white" href="{{ route($notification->data['route'], ['id' => $notification->data['comment']['commentable_id'], 'notification' => $notification->id]) }}">New comment on {{ $notification->data['commentableType'] }} '{{ $notification->data['commentableName'] }}'</a>
                                        @elseif(!empty($notification->data['challenge']))
                                            <a class="dropdown-item text-white" href="{{ route('challenge_view', ['id' => $notification->data['challenge']['id'], 'notification' => $notification->id]) }}">New challenge on {{ $notification->data['challenge']['spot']['name'] }}</a>
                                        @elseif(!empty($notification->data['entry']))
                                            <a class="dropdown-item text-white" href="{{ route('challenge_view', ['id' => $notification->data['entry']['challenge_id'], 'notification' => $notification->id]) }}">New entry on {{ $notification->data['entry']['challenge']['name'] }}</a>
                                        @elseif(!empty($notification->data['challenge_winner']))
                                            <a class="dropdown-item text-white" href="{{ route('challenge_view', ['id' => $notification->data['challenge_winner']['challenge_id'], 'notification' => $notification->id]) }}">You won challenge {{ $notification->data['challenge_winner_challenge']['name'] }}</a>
                                        @elseif(!empty($notification->data['new_spot']))
                                            <a class="dropdown-item text-white" href="{{ route('spot_view', ['id' => $notification->data['new_spot']['id'], 'notification' => $notification->id]) }}">New spot {{ $notification->data['new_spot']['name'] }} from {{ $notification->data['user']['name'] }}</a>
                                        @elseif(!empty($notification->data['new_challenge']))
                                            <a class="dropdown-item text-white" href="{{ route('challenge_view', ['id' => $notification->data['new_challenge']['id'], 'notification' => $notification->id]) }}">New challenge {{ $notification->data['new_challenge']['name'] }} from {{ $notification->data['user']['name'] }}</a>
                                        @elseif(!empty($notification->data['new_event']))
                                            <a class="dropdown-item text-white" href="{{ route('event_view', ['id' => $notification->data['new_event']['id'], 'notification' => $notification->id]) }}">New event {{ $notification->data['new_event']['name'] }} from {{ $notification->data['user']['name'] }}</a>
                                        @elseif(!empty($notification->data['event_update']))
                                            <a class="dropdown-item text-white" href="{{ route('event_view', ['id' => $notification->data['event_update']['id'], 'notification' => $notification->id]) }}">Event {{ $notification->data['event_update']['name'] }} updated</a>
                                        @elseif(!empty($notification->data['event_invite']))
                                            <a class="dropdown-item text-white" href="{{ route('event_view', ['id' => $notification->data['event_invite']['id'], 'notification' => $notification->id]) }}">You have been invited to attend {{ $notification->data['event_invite']['name'] }}</a>
                                        @elseif(!empty($notification->data['follower']))
                                            <a class="dropdown-item text-white" href="{{ route('user_view', ['id' => $notification->data['follower']['follower_id'], 'notification' => $notification->id]) }}">New follower {{ $notification->data['follower']['name'] }}</a>
                                        @elseif(!empty($notification->data['follow_requester']))
                                            <a class="dropdown-item text-white" href="{{ route('user_view', ['id' => $notification->data['follower']['follower_id'], 'notification' => $notification->id]) }}">New follow request {{ $notification->data['follower']['name'] }}</a>
                                        @elseif(!empty($notification->data['new_workout']))
                                            <a class="dropdown-item text-white" href="{{ route('workout_view', ['id' => $notification->data['new_workout']['id'], 'notification' => $notification->id]) }}">New workout from  {{ $notification->data['user']['name'] }}</a>
                                        @elseif(!empty($notification->data['workout_updated']))
                                            <a class="dropdown-item text-white" href="{{ route('workout_view', ['id' => $notification->data['workout_updated']['id'], 'notification' => $notification->id]) }}">Workout {{ $notification->data['workout_updated']['name'] }} updated</a>
                                        @endif
                                    @endforeach
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="user-dropdown" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="fa fa-user nav-icon"></i><span class="d-inline d-md-none">{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right bg-grey" id="user-menu" aria-labelledby="user-dropdown">
                                    @premium
                                    @else
                                        <a class="dropdown-item btn-premium" href="{{ route('premium') }}"><i class="fa fa-diamond nav-icon"></i>Premium</a>
                                    @endpremium
                                    <a class="dropdown-item text-white" href="{{ route('home') }}"><i class="fa fa-home nav-icon"></i>Home</a>
                                    <a class="dropdown-item text-white" href="{{ route('user_view', Auth()->id()) }}"><i class="fa fa-user nav-icon"></i>Profile</a>
                                    <a class="dropdown-item text-white" href="{{ route('user_manage') }}"><i class="fa fa-user-cog nav-icon"></i>Settings</a>
                                    @premium
                                        <a class="dropdown-item text-white dropdown-toggle" id="workouts-nav-item"><i class="fa fa-running nav-icon"></i>Training <span class="caret"></span></a>
                                        <div id="workouts-nav-items">
                                            <a class="dropdown-item text-white" href="{{ route('workout_listing', ['personal' => 'on']) }}"><i class="fa fa-running nav-icon nav-spacer"></i>Workouts</a>
                                            <a class="dropdown-item text-white" href="{{ route('workout_plan') }}"><i class="fa fa-calendar nav-icon nav-spacer"></i>Workout Plan</a>
                                            <a class="dropdown-item text-white" href="{{ route('workout_listing', ['bookmarked' => 'on']) }}"><i class="fa fa-bookmark nav-icon nav-spacer"></i>Bookmarked Workouts</a>
                                            <a class="dropdown-item text-white" href="{{ route('recorded_workout_listing') }}"><i class="fa fa-calendar-check-o nav-icon nav-spacer"></i>Recorded Workouts</a>
                                        </div>
                                    @endpremium
                                    @if(!empty(Auth::user()->hometown_name))
                                        <a class="dropdown-item text-white dropdown-toggle" id="hometown-nav-item"><i class="fa fa-street-view nav-icon"></i>Hometown <span class="caret"></span></a>
                                        <div id="hometown-nav-items">
                                            <a class="dropdown-item text-white" href="{{ route('spot_listing', ['in_hometown' => 'on']) }}"><i class="fa fa-map-marker nav-icon nav-spacer"></i>Spots</a>
                                            <a class="dropdown-item text-white" href="{{ route('challenge_listing', ['in_hometown' => 'on']) }}"><i class="fa fa-bullseye nav-icon nav-spacer"></i>Challenges</a>
                                        </div>
                                    @endif
                                    @can('manage reports')
                                        <a class="dropdown-item text-white dropdown-toggle" id="admin-nav-item"><i class="fa fa-tools nav-icon"></i>Admin <span class="caret"></span></a>
                                        <div id="admin-nav-items">
                                            <a class="dropdown-item text-white" href="{{ route('report_listing') }}"><i class="fa fa-flag nav-icon nav-spacer"></i>Reports</a>
                                        </div>
                                    @endcan
                                    <a class="dropdown-item text-white" href="{{ route('user_bin') }}"><i class="fa fa-trash nav-icon"></i>Bin</a>
                                    <a class="dropdown-item text-white" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="fa fa-sign-out nav-icon"></i>{{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>
    @yield('footer')
</body>
</html>
