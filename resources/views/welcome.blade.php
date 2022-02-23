<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <script src="{{ asset('js/lazyload.js') }}"></script>

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
                    <p class="text-center large-text">Parkour Hub is based on the sharing of parkour spots via the spots map. Other features build on this concept such as setting challenges at spots, hosting events at spots and adding spots to your hitlist.</p>
                    <p class="text-center large-text">Going to a new town and don't know if there are any spots? Check on Parkour Hub to see what the locals have uploaded.</p>
                    <p class="text-center large-text">Found a good spot in your local area? Add it to Parkour Hub so others can find it.</p>
                    <p class="text-center large-text">Want to meet up with other freerunners? See what events are happening at spots in your area.</p>
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
            <div class="row my-md-4 text-center">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-map feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Spots Map</h3>
                    </div>
                    <p class="large-text">See spots that users have created on a global map and create your own.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-bullseye feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Challenges</h3>
                    </div>
                    <p class="large-text">Enter challenges at spots and see if you can win.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-map-marked feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Events</h3>
                    </div>
                    <p class="large-text">Find out what events are being held at spots you go to.</p>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-crosshairs feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Hitlist</h3>
                    </div>
                    <p class="large-text">Add spots to your Hitlist and tick them off once you've visited.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-house-user feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Locals</h3>
                    </div>
                    <p class="large-text">Mark yourself as a Local at spots you frequently attend so that other locals or people passing through can get in touch to meet up.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-star feature-icon"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Reviews</h3>
                    </div>
                    <p class="large-text">Review a spot to let others know what you think of it.</p>
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
                    <p class="text-center large-text">
                        With Premium you can create your own challenges and events as well as moves, exercises, equipment and workouts to help yourself and others get better at parkour or general fitness.
                        You can also upload videos directly (up to 500MB) rather than use a YouTube link and your images can be up to 5MB rather than 500KB.
                    </p>
                    <p class="text-center large-text">Found a fun challenge at a spot but nobody has set it yet? Create your own and crown a winner.</p>
                    <p class="text-center large-text">Need some more structure to your training? Add workouts to your plan in advance so you can jump straight into it on the day.</p>
                    <p class="text-center large-text">Hosting a parkour jam? Add it to Parkour Hub to let other freerunners find out about it.</p>
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
            <div class="row my-md-4 text-center">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-bullseye feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Challenges</h3>
                    </div>
                    <p class="large-text">Create & manage your own challenges.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-map-marked feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Events</h3>
                    </div>
                    <p class="large-text">Organise your own events such as a training session, jam or competition.</p>
                </div>
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-file-video-o feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">File Uploads</h3>
                    </div>
                    <p class="large-text">Upload videos up to 500MB and upload larger images up to 5MB to showcase your content.</p>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="flex d-md-block vertical-center">
                        <i class="fa fa-child feature-icon text-premium"></i>
                        <h3 class="sedgwick ml-2 ml-md-0">Moves</h3>
                    </div>
                    <p class="large-text">Create moves and link them to spots where they can be performed.</p>
                </div>
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
