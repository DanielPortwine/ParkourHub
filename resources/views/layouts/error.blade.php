<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Parkour Hub is a place for people to share their spots, challenges, moves and more. There is also a premium membership that boasts a full training system to help users develop their skills and achieve their goals.')">

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
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('spots') }}"><i class="fa fa-map nav-icon"></i>Map</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('spot_listing') }}"><i class="fa fa-map-marker nav-icon"></i>Spots</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('challenge_listing') }}"><i class="fa fa-bullseye nav-icon"></i>Challenges</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
</div>
</body>
</html>
