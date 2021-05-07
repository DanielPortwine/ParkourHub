<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sign up for the Premium membership to access exclusive features such as the training system and increased file upload sizes.">

    <title>Premium | {{ config('app.name') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.js"></script>
    <script src="https://js.stripe.com/v3/"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sedgwick+Ave+Display&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://kit.fontawesome.com/014efa2ea8.js" crossorigin="anonymous"></script>

    <!-- Favicon -->
    <link rel="icon" href="/favicon.png" type="image/png">
</head>
<body>
    <div class="flex-center full-height premium-section">
        <div class="position-absolute links d-flex justify-content-end w-100 px-4">
            @auth
                <a href="{{ route('home') }}">Home</a>
            @endauth
            @guest
                <a href="{{ route('welcome') }}">Home</a>
            @endguest
        </div>
        <div class="text-center">
            <div class="page-title sedgwick">
                Parkour Hub <span class="text-premium">Premium</span>
            </div>
        </div>
        <div class="text-center bottom-centre" id="scroll-arrow">
            <i class="fa fa-angle-double-down"></i>
        </div>
    </div>
    <div class="pb-md-3 pb-2 premium-section grey-section">
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
                    <p class="text-center large-text">A premium membership gives you access to the training system along with an increased image upload size limit and the ability to upload video files directly from your device.</p>
                </div>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <div class="pb-md-3 pb-2 premium-section bg-premium">
        <div class="container">
            <div class="row mt-3">
                <div class="col">
                    <h1 class="text-center sedgwick">Only £5/month - Cancel anytime</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-md-3 pb-2 premium-section">
        <div class="container">
            <div class="row my-3">
                <div class="col">
                    <h1 class="text-center subtitle text-premium sedgwick">Features</h1>
                </div>
            </div>
            <div class="row my-md-4">
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-file-video-o feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">Video Upload</h3>
                    </div>
                    <p class="large-text">Upload videos of up to 50MB directly from your device without needing to upload to YouTube first.</p>
                </div>
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-file-image-o feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">Image Upload</h3>
                    </div>
                    <p class="large-text">Upload images of up to 5MB, a significant increase over the standard 500KB.</p>
                </div>
            </div>
            <div class="row my-md-4">
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-child feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">Movements</h3>
                    </div>
                    <p class="large-text">Create and share movements - either a parkour move such as a vault or an exercise such as push-ups.</p>
                </div>
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-running feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">Workouts</h3>
                    </div>
                    <p class="large-text">Combine movements into a workout to record your training and track your progress.</p>
                </div>
            </div>
            <div class="row my-md-4">
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-calendar feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">Training Planner</h3>
                    </div>
                    <p class="large-text">Add workouts to your calendar to plan your journey to achieving your parkour or fitness goals.</p>
                </div>
                <div class="col-md-6">
                    <div class="flex d-md-block vertical-center">
                        <div class="text-md-center"><i class="fa fa-ellipsis-h feature-icon-premium"></i></div>
                        <h3 class="sedgwick ml-2 ml-md-0 text-md-center">More</h3>
                    </div>
                    <p class="large-text">There are more amazing features coming in future.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="pb-md-3 pb-2 premium-section grey-section">
        <div class="container">
            @guest
                <div class="row my-3">
                    <div class="col">
                        <h1 class="text-center subtitle text-premium sedgwick">Register or Login to Sign Up</h1>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col text-center">
                        <a class="btn btn-premium" href="/register">Register</a>
                    </div>
                </div>
            @else
                @if(!Auth()->user()->hasDefaultPaymentMethod())
                    @if(!Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')  || Auth()->user()->subscription('premium')->ended())
                        <div class="row my-3">
                            <div class="col">
                                <h1 class="text-center subtitle text-premium sedgwick">Sign Up Now</h1>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col text-center">
                                <p class="mb-0">Become a <span class="text-premium">premium member</span> now and get instant access to all the above features for just £5/month.</p>
                            </div>
                        </div>
                        <div class="row my-4">
                            <div class="col">
                                <div class="form-group row">
                                    <label for="card-holder-name" class="col-md-4 col-form-label text-md-right">Name on Card</label>
                                    <div class="col-md-4">
                                        <input id="card-holder-name" type="text" class="form-control" name="card-holder-name" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="card-element" class="col-md-4 col-form-label text-md-right">Card Details</label>
                                    <div class="col-md-4">
                                        <div id="card-element"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-md-4 offset-md-4">
                                        <a id="card-button" class="btn btn-premium" data-secret="{{ $intent->client_secret }}">Sign Up</a>
                                    </div>
                                </div>

                                <script>
                                    const stripe = Stripe('{{ env('STRIPE_KEY') }}');
                                    const elements = stripe.elements();
                                    const cardElement = elements.create('card');
                                    const cardHolderName = document.getElementById('card-holder-name');
                                    const cardButton = document.getElementById('card-button');
                                    const clientSecret = cardButton.dataset.secret;

                                    cardElement.mount('#card-element');

                                    cardButton.addEventListener('click', async (e) => {
                                        cardButton.innerHTML = 'Signing Up...';
                                        cardButton.removeAttribute('id');
                                        const { setupIntent, error } = await stripe.confirmCardSetup(
                                            clientSecret, {
                                                payment_method: {
                                                    card: cardElement,
                                                    billing_details: { name: cardHolderName.value }
                                                }
                                            }
                                        );

                                        if (error) {
                                            cardButton.innerHTML = 'Sign Up';
                                            cardButton.id = 'card-button';
                                        } else {
                                            $.ajax({
                                                url: '{{ route('premium_register') }}',
                                                type: 'POST',
                                                data: {
                                                    "_token": '{{ csrf_token() }}',
                                                    paymentMethod: setupIntent.payment_method
                                                },
                                                success: function() {
                                                    location.reload();
                                                }
                                            })
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    @endif
                @else
                    @if(!Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium'))
                        <div class="row my-3">
                            <div class="col">
                                <h1 class="text-center subtitle text-premium sedgwick">Restart Membership</h1>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col text-center">
                                <p class="mb-0">Restart your membership for just £5/month.</p>
                            </div>
                        </div>
                        <div class="row my-4">
                            <div class="col text-center">
                                <a class="btn btn-premium require-confirmation">Restart</a>
                                <a class="btn btn-premium d-none confirmation-button" href="{{ route('premium_restart') }}">Confirm Restart</a>
                            </div>
                        </div>
                    @elseif(Auth()->user()->subscription('premium')->onGracePeriod())
                        <div class="row my-3">
                            <div class="col">
                                <h1 class="text-center subtitle text-premium sedgwick">Resume Membership</h1>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col text-center">
                                <p class="mb-0">You have until {{ $endDate }} to resume your current membership at no additional cost.</p>
                            </div>
                        </div>
                        <div class="row my-4">
                            <div class="col text-center">
                                <a class="btn btn-premium require-confirmation">Resume</a>
                                <a class="btn btn-premium d-none confirmation-button" href="{{ route('premium_resume') }}">Confirm Resume</a>
                            </div>
                        </div>
                    @elseif(Auth()->user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium'))
                        <div class="row my-3">
                            <div class="col">
                                <h1 class="text-center subtitle text-premium sedgwick">Cancel Membership</h1>
                            </div>
                        </div>
                        <div class="row my-3">
                            <div class="col text-center">
                                <p class="mb-0">Your membership will auto-renew at £5 on {{ $nextInvoiceDate }}.</p>
                            </div>
                        </div>
                        <div class="row my-4">
                            <div class="col text-center">
                                <a class="btn btn-premium require-confirmation">Cancel</a>
                                <a class="btn btn-premium d-none confirmation-button" href="{{ route('premium_cancel') }}">Confirm Cancel</a>
                            </div>
                        </div>
                    @endif
                    <div class="row my-3">
                        <div class="col-md-6 offset-md-2">
                            <i class="fa fa-cc-{{ str_replace(' ', '_', strtolower($cardBrand)) }}"></i> {{ $card }}
                        </div>
                        <div class="col-md-2 text-md-right">
                            <a class="btn btn-sm btn-premium" id="change-payment-card">Change</a>
                        </div>
                    </div>
                    <div class="row my-3 d-none" id="update-card-form">
                        <div class="col">
                            <div class="form-group row">
                                <label for="card-holder-name" class="col-md-4 col-form-label text-md-right">Name on Card</label>
                                <div class="col-md-4">
                                    <input id="card-holder-name" type="text" class="form-control" name="card-holder-name" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="card-element" class="col-md-4 col-form-label text-md-right">Card Details</label>
                                <div class="col-md-4">
                                    <div id="card-element"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4 offset-md-4">
                                    <a id="card-button" class="btn btn-premium" data-secret="{{ $intent->client_secret }}">Update</a>
                                </div>
                            </div>

                            <script>
                                const stripe = Stripe('{{ env('STRIPE_KEY') }}');
                                const elements = stripe.elements();
                                const cardElement = elements.create('card');
                                const cardHolderName = document.getElementById('card-holder-name');
                                const cardButton = document.getElementById('card-button');
                                const clientSecret = cardButton.dataset.secret;
                                const changeCardButton = document.getElementById('change-payment-card');
                                const cardUpdateForm = document.getElementById('update-card-form');

                                changeCardButton.addEventListener('click', function() {
                                    cardUpdateForm.classList.remove('d-none');
                                    changeCardButton.remove();
                                });

                                cardElement.mount('#card-element');

                                cardButton.addEventListener('click', async (e) => {
                                    cardButton.innerHTML = 'Updating...';
                                    cardButton.removeAttribute('id');
                                    const { setupIntent, error } = await stripe.confirmCardSetup(
                                        clientSecret, {
                                            payment_method: {
                                                card: cardElement,
                                                billing_details: { name: cardHolderName.value }
                                            }
                                        }
                                    );

                                    if (error) {
                                        cardButton.innerHTML = 'Update';
                                        cardButton.id = 'card-button';
                                    } else {
                                        $.ajax({
                                            url: '{{ route('premium_update') }}',
                                            type: 'POST',
                                            data: {
                                                "_token": '{{ csrf_token() }}',
                                                paymentMethod: setupIntent.payment_method
                                            },
                                            success: function() {
                                                location.reload();
                                            }
                                        })
                                    }
                                });
                            </script>
                        </div>
                    </div>
                @endif
                @if(count($payments))
                    <div class="row my-3">
                        <div class="col-md-8 offset-md-2">
                            <h4 class="text-premium mb-0">Payment History</h4>
                        </div>
                    </div>
                    <div class="row my-3">
                        <div class="col-md-8 offset-md-2">
                            @foreach($payments as $payment)
                                <div class="text-white row mb-2 pb-2 border-subtle">
                                    <div class="col-auto">{{ $payment['date'] }}</div>
                                    <div class="col">{{ $payment['name'] }}</div>
                                    <div class="col-auto">{{ $payment['amount'] }}</div>
                                    <div class="col-auto">
                                        <a class="btn btn-sm btn-premium" href="{{ $payment['pdf'] }}">
                                            <i class="fa fa-download"></i> Invoice
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endguest
        </div>
    </div>
</body>
</html>
