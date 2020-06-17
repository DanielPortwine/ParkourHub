<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@stack('title'){{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div id="app">
        <nav class="navbar sticky-top navbar-dark text-white bg-grey shadow-sm">
            <div class="container">
                <a class="navbar-brand sedgwick" href="{{ url('/') }}">
                    {{ config('app.name') }}
                </a>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('spots') }}"><i class="fa fa-map nav-icon"></i>Map</a>
                    </li>
                </ul>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>
    </div>
</body>
