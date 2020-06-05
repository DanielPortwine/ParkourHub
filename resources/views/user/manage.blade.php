@extends('layouts.app')

@push('title')Account | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Manage Account</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <h3 class="separator sedgwick pb-2 mb-3">Account Details</h3>
                        <form id="account-form" method="POST">
                            @csrf
                            <input type="hidden" name="account-form" value="true">
                        </form>
                        <div class="form-group row">
                            <label for="name" class="col-md-2 col-form-label text-md-right">Username</label>
                            <div class="col-md-8">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user->name }}" required autocomplete="name" form="account-form">
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <a class="btn btn-danger require-confirmation">Obfuscate</a>
                                <a href="{{ route('obfuscate', 'name') }}" class="btn btn-danger d-none confirmation-button">Confirm</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-md-2 col-form-label text-md-right">Email Address</label>
                            <div class="col-md-8">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" required autocomplete="email" form="account-form">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <a class="btn btn-danger require-confirmation">Obfuscate</a>
                                <a href="{{ route('obfuscate', 'email') }}" class="btn btn-danger d-none confirmation-button">Confirm</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="hometown" class="col-md-2 col-form-label text-md-right">Hometown</label>
                            <div class="col-md-8">
                                <form id="hometown-form">
                                    <div class="input-group w-100">
                                        <input id="hometown" type="text" class="form-control @error('hometown') is-invalid @enderror" value="{{ $user->hometown_name }}" autocomplete="hometown">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-green input-group-text" id="hometown-search-button" title="Search"><i class="fa fa-search"></i></button>
                                        </div>
                                        @error('hometown')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2">
                                <a class="btn btn-danger require-confirmation">Remove</a>
                                <a class="btn btn-danger d-none confirmation-button" id="remove-hometown-button">Confirm</a>
                            </div>
                        </div>
                        <div class="form-group row d-none" id="hometown-results-container">
                            <div class="col-md-8 offset-md-2">
                                <span id="hometown-results-count"></span> results found.
                                <select class="form-control w-100" name="hometown" id="hometown-results" form="account-form"></select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-10 offset-md-2">
                                <div class="form-check">
                                    <input class="form-check-input @error('subscribed') is-invalid @enderror" type="checkbox" name="subscribed" id="subscribed" value="1" {{ $subscribed ? 'checked' : '' }} form="account-form">
                                    <label class="form-check-label" for="subscribed">Subscribed to email news</label>
                                    @error('subscribed')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
                                <input type="submit" class="btn btn-green" value="Save" form="account-form">
                            </div>
                        </div>
                        <br>
                        <h3 class="separator sedgwick pb-2 mb-3">Notifications</h3>
                        <form id="notification-form" method="POST">
                            @csrf
                            <input type="hidden" name="notification-form" value="true">
                        </form>
                        @foreach(config('notifications.notifications') as $notificationKey => $notification)
                            <div class="form-group row">
                                <label for="{{ $notificationKey }}" class="col-md-2 col-form-label text-md-right">{{ $notification['title'] }}</label>
                                <div class="col-md-8">
                                    <select id="{{ $notificationKey }}" class="form-control @error($notificationKey) is-invalid @enderror" name="notifications[{{ $notificationKey }}]" required form="notification-form">
                                        @foreach(config('notifications.channels') as $key => $name)
                                            <option value="{{ $key }}" @if(!empty($settings[$notificationKey]) && $settings[$notificationKey] === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error($notificationKey)
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <small>Decide how you would like to be notified when {{ $notification['description'] }}</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="form-group row mb-0">
                            <div class="col-md-10 offset-md-2">
                                <input type="submit" class="btn btn-green" value="Save" form="notification-form">
                            </div>
                        </div>
                        <br>
                        <h3 class="separator sedgwick pb-2 mb-3">Additional Options</h3>
                        <div class="form-group row">
                            <div class="col">
                                <a class="btn btn-danger require-confirmation">Delete Account</a>
                                <a href="{{ route('user_delete') }}" class="btn btn-danger d-none confirmation-button">Confirm</a>
                                <p class="mb-0 d-none text-danger confirmation-text">Are you sure you want to delete your account? This will also remove all your spots, challenges and events. <strong>There is no way to recover your account.</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
