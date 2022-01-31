<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Parkour Hub is a place for people to share their spots, challenges, moves and more. There is also a premium membership that boasts a full training system to help users develop their skills and achieve their goals.">
    <meta name="description" content="Parkour Hub is a place for people to share their spots, challenges, moves and more. There is also a premium membership that boasts a full training system to help users develop their skills and achieve their goals.">
    <meta name="twitter:card" content="summary">
    <meta property="og:site-name" content="Parkour Hub">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="Parkour Hub is a place for people to share their spots, challenges, moves and more.">
    <meta property="og:url" content="{{ Request()->url() }}">
    <meta property="og:image" content="{{ url('/favicon.png') }}">

    <title>{{ config('app.name') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://kit.fontawesome.com/014efa2ea8.js" crossorigin="anonymous"></script>

    <!-- Favicon -->
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div class="flex-center full-height section">
        <div class="position-absolute links d-flex justify-content-between justify-content-lg-end w-100 px-4">
            <a href="{{ route('spots') }}">Spots</a>
            <a href="{{ route('premium') }}">Premium</a>
            @auth
                <a href="{{ route('home') }}">Home</a>
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
                    <p class="text-center large-text">Parkour Hub is a place for athletes to share their spots and set challenges for others to attempt.</p>
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
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-map feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Spots Map</h3>
                    </div>
                    <p class="large-text">See spots that other users have created on a global map.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-bullseye feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Challenges</h3>
                    </div>
                    <p class="large-text">Set challenges at your own or others' spots and see if anyone can complete them.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-crosshairs feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Hitlist</h3>
                    </div>
                    <p class="large-text">Add spots to your Hitlist and tick them off once you complete them.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-comment feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Comments</h3>
                    </div>
                    <p class="large-text">Comment on a spot with text, image or video to share your experiences at the spot.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-star feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Reviews</h3>
                    </div>
                    <p class="large-text">Review a spot to let others know what you think of it.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-youtube-play feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">YouTube</h3>
                    </div>
                    <p class="large-text">Embed YouTube videos to showcase spots or demonstrate challenges.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-md-5 pb-sm-4 section grey-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Premium</h1>
                </div>
            </div>
            <hr class="subtitle-line-premium">
            <div class="row">
                <div class="col"></div>
                <div class="col-md-8">
                    <p class="text-center large-text">Premium is a place for athletes to create and share moves, exercises and workouts to help themselves and others get better at parkour.</p>
                    <p class="text-center large-text">Need some more structure to your training? Add workouts to your plan in advance so you can jump straight into it on the day.</p>
                    <p class="text-center large-text">Want to learn a particular move? Create a plan of workouts with progressions of that move to build up to it safely.</p>
                </div>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <div class="pb-md-3 pb-sm-2 section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center text-premium sedgwick"><i class="fa fa-diamond d-none d-md-inline-block"></i> Premium Features <i class="fa fa-diamond d-none d-md-inline-block"></i></h1>
                </div>
            </div>
            <div class="row my-md-4">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-running feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Workouts</h3>
                    </div>
                    <p class="large-text">Create workouts using moves & exercises you or other users have created.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-calendar feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Training Planner</h3>
                    </div>
                    <p class="large-text">Use a simple calendar to plan which workouts you want to do when.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-file-video-o feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">File Uploads</h3>
                    </div>
                    <p class="large-text">Upload videos up to 500MB and upload larger images up to 5MB to showcase your content.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-4 section green-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Sign Up For Premium Now</h1>
                </div>
            </div>
            <div class="row">
                <div class="col"></div>
                <div class="col-auto">
                    <a href="{{ route('premium') }}" class="premium-button">Premium</a>
                </div>
                <div class="col"></div>
            </div>
        </div>
    </div>
    @include('components.footer')
</body>
</html>
