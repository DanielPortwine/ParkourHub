<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div class="flex-center full-height section title-section">
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">Account</a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>

        <div class="text-center">
            <div class="page-title sedgwick">
                Parkour Hub
            </div>
        </div>

        <div class="text-center bottom-centre" id="scroll-arrow">
            <i class="fa fa-angle-double-down"></i>
        </div>
    </div>
    <div class="pb-md-5 pb-sm-4 section grey-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Spot Sharing</h1>
                </div>
            </div>
            <hr class="subtitle-line">
            <div class="row">
                <div class="col"></div>
                <div class="col-md-8">
                    <p class="text-center large-text">Parkour Hub is a place for traceurs to share their spots and set challenges for others to attempt.</p>
                    <p class="text-center large-text">Going to a new town and don't know if there are any spots? Check on Parkour Hub to see what the locals have uploaded.</p>
                    <p class="text-center large-text">Found a particularly tricky spot? Why not create a challenge there and see if anyone can complete it?</p>
                </div>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <div class="pb-4 section green-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Register Now</h1>
                </div>
            </div>
            <div class="row">
                <div class="col"></div>
                <div class="col-auto">
                    <a href="/register" class="register-button">Register</a>
                </div>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <div class="pb-md-5 pb-sm-4 section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center subtitle sedgwick">Features</h1>
                </div>
            </div>
            <div class="row my-md-4">
                <div class="col-md-4">
                    <i class="fa fa-bullseye feature-icon"></i>
                    <h3 class="sedgwick">Challenges</h3>
                    <p class="large-text">Set challenges at your own or others' spots and see if anyone can complete them.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa fa-check-square-o feature-icon"></i>
                    <h3 class="sedgwick">Hitlist</h3>
                    <p class="large-text">Add spots to your Hitlist and tick them off once you complete them.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa fa-calendar feature-icon"></i>
                    <h3 class="sedgwick">Events</h3>
                    <p class="large-text">Create an event at a spot to gather other traceurs for a jam or competition.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <i class="fa fa-youtube-play feature-icon"></i>
                    <h3 class="sedgwick">Third Party Integrations</h3>
                    <p class="large-text">Enhance your spots with clips or images from third party platforms such as Instagram or YouTube to showcase the spot or demonstrate a challenge.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa fa-edit feature-icon"></i>
                    <h3 class="sedgwick">Custom Athlete Pages</h3>
                    <p class="large-text">Create your own landing page with a custom URL to show off your favourite spots, challenges and upcoming events you'll be attending.</p>
                </div>
                <div class="col-md-4">
                    <i class="fa fa-group feature-icon"></i>
                    <h3 class="sedgwick">Groups</h3>
                    <p class="large-text">Athletes can join or create groups where each member can create spots, challenges, events etc. on behalf of the group.</p>
                </div>
            </div>
        </div>
    </div>
    @include('components.footer')
</body>
</html>
