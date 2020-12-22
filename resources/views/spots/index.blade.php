@extends('layouts.app')

@push('title')Spots Map | @endpush

@section('description')Travel the world and find or create spots on Parkour Hub.@endsection

@section('content')
    <div class="vh-100-nav">
        <div class="p-0 map-search">
            <div class="p-1 p-md-3">
                <form id="map-search-form">
                    <div class="input-group w-100">
                        <input type="text" class="form-control @error('search') is-invalid @enderror" id="map-search-input" placeholder="Search a place or spot" aria-label="from" aria-describedby="from" value="{{ !empty($_GET['search']) ? $_GET['search'] : '' }}">
                        <i class="fa fa-times d-none" id="map-search-clear"></i>
                        <div class="input-group-append">
                            <a class="btn btn-green input-group-text" id="map-search-button" title="Search"><i class="fa fa-search"></i></a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="position-absolute z-10 w-100 p-0 px-md-3">
                @error('search')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="popup card h-50 d-none mt-md-3" id="map-search-results">
                    <div class="card-header bg-green">
                        <span class="sedgwick">Results</span>
                        <a class="btn close-popup-button float-right" title="Close"><i class="fa fa-times"></i></a>
                    </div>
                    <div class="card-body bg-grey text-white">
                        <div id="accordion">
                            <div class="card border-0 d-none" id="map-search-results-addresses">
                                <div class="card-header bg-green" id="heading-one" data-toggle="collapse" data-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                    <h5 class="mb-0 collapsed">Places (<span id="map-search-results-addresses-count"></span>)<span class="float-right"><i class="fa fa-angle-down"></i></span></h5>
                                </div>
                                <div id="collapse-one" class="collapse" aria-labelledby="heading-one" data-parent="#accordion">
                                    <div class="card-body bg-grey text-white" id="address-results"></div>
                                </div>
                            </div>
                            <div class="card border-0 d-none" id="map-search-results-spots">
                                <div class="card-header bg-green" id="heading-two" data-toggle="collapse" data-target="#collapse-two" aria-expanded="false" aria-controls="collapse-two">
                                    <h5 class="mb-0 collapsed">Spots (<span id="map-search-results-spots-count"></span>)<span class="float-right"><i class="fa fa-angle-down"></i></span></h5>
                                </div>
                                <div id="collapse-two" class="collapse" aria-labelledby="heading-two" data-parent="#accordion">
                                    <div class="card-body bg-grey text-white" id="spot-results"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(!empty(Auth()->user()->hometown_bounding))
            <a class="position-fixed btn btn-green hidden" id="toggle-hometown-button"><i class="fa fa-home"></i></a>
        @endif
        <div id="map"></div>
        <div class="popup ol-popup" id="create-spot-popup">
            <div class="card">
                <div class="card-header bg-green">
                    <span class="sedgwick">Create Spot</span>
                </div>
                <div class="card-body bg-grey text-white">
                    <form method="POST" action="{{ route('spot_create') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="coordinates" id="coordinates">
                        <input type="hidden" name="lat_lon" id="lat-lon">
                        <div class="form-group row">
                            <label for="name" class="col-12 col-form-label">Name</label>
                            <div class="col-12">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name" maxlength="25">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-12 col-form-label">Description</label>
                            <div class="col-12">
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" required></textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="image" class="col-12 col-form-label">Thumbnail</label>
                            <div class="col-12">
                                <input type="file" id="image" class="form-control-file @error('image') is-invalid @enderror" name="image" required>
                                @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="visibility" class="col-12 col-form-label">Visibility</label>
                            <div class="col-12">
                                <select name="visibility" class="form-control">
                                    @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                        <option value="{{ $key }}" @if(setting('privacy_content', 'private') === $key)selected @endif>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col">
                                <button type="submit" class="btn btn-green">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="popup ol-popup" id="view-spot-popup"></div>

        <div class="popup ol-popup" id="login-register-popup">
            <div class="card">
                <div class="card-header bg-green">
                    <div class="row">
                        <span class="col sedgwick">Unauthorised</span>
                        <span class="col-auto">
                            <a class="btn close-popup-button" title="Close"><i class="fa fa-times"></i></a>
                        </span>
                    </div>
                </div>
                <div class="card-body bg-grey text-white">
                    @guest
                        <p class="mb-0">You must <a class="btn-link" href="/login">Login</a> or <a class="btn-link" href="/register">Register</a> to create new spots.</p>
                    @endguest
                    @auth
                        <p class="mb-0">You must verify your email to create new spots.</p>
                        <p class="mb-0">If you did not receive the email,</p>
                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 m-0 align-baseline">click here to request another</button>.
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
