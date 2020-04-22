@extends('layouts.app')

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
                        <form method="POST">
                            @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Username</label>
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $user->name }}" required autocomplete="name">
                                    @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <a class="btn btn-danger require-confirmation">Obfuscate</a>
                                    <a href="{{ route('obfuscate', 'name') }}" class="btn btn-danger d-none confirmation-button">Confirm</a>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-md-2 col-form-label text-md-right">Email Address</label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" required autocomplete="email">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <a class="btn btn-danger require-confirmation">Obfuscate</a>
                                    <a href="{{ route('obfuscate', 'email') }}" class="btn btn-danger d-none confirmation-button">Confirm</a>
                                    <p class="mb-0 d-none text-danger confirmation-text position-absolute">We will no longer be able to contact you and you won't receive notifications.</p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-10 offset-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="subscribed" id="subscribed" {{ $subscribed ? 'checked' : '' }}>
                                        <label class="form-check-label" for="subscribed">Subscribed to email news</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-10 offset-md-2">
                                    <button type="submit" class="btn btn-green">Save</button>
                                </div>
                            </div>
                        </form>
                        <br>
                        <h3 class="separator sedgwick pb-2 mb-3">Additional Options</h3>
                        <div class="form-group row">
                            <div class="col-md-8 offset-md-2">
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
