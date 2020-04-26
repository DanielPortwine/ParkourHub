@extends('layouts.app')

@section('content')
    <div class="vh-100-nav">
        <div class="position-absolute p-md-3 p-1 map-search">
            <form id="map-search-form">
                <div class="input-group w-100">
                    <input type="text" class="form-control" id="map-search-input" placeholder="Search an address or spot" aria-label="from" aria-describedby="from" value="{{ !empty($_GET['search']) ? $_GET['search'] : '' }}">
                    <div class="input-group-append">
                        <a class="btn btn-green input-group-text" id="map-search-button"><i class="fa fa-search"></i></a>
                    </div>
                </div>
            </form>
            <p class="text-danger">@error('search'){{ $message }}@enderror</p>
            <div class="popup card h-50 d-none" id="map-search-results">
                <div class="card-header bg-green">
                    <span class="sedgwick">Results</span>
                    <a class="btn close-popup-button float-right"><i class="fa fa-times"></i></a>
                </div>
                <div class="card-body bg-grey text-white">
                    <div id="accordion">
                        <div class="card border-0 d-none" id="map-search-results-addresses">
                            <div class="card-header bg-green" id="heading-one" data-toggle="collapse" data-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                <h5 class="mb-0 collapsed">Addresses (<span id="map-search-results-addresses-count"></span>)<span class="float-right"><i class="fa fa-angle-down"></i></span></h5>
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
        <div id="map"></div>
        <div class="popup ol-popup" id="create-spot-popup">
            <div class="card">
                <div class="card-header bg-green">
                    <span class="sedgwick">Create Spot</span>
                    <a class="btn close-popup-button float-right"><i class="fa fa-times"></i></a>
                </div>
                <div class="card-body bg-grey text-white">
                    <form method="POST" action="{{ route('spot_create') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="coordinates" id="coordinates">
                        <div class="form-group row">
                            <label for="name" class="col-12 col-form-label">Name</label>
                            <div class="col-12">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name">
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
                                <textarea id="description" class="form-control" name="description"></textarea>
                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="image" class="col-12 col-form-label">Main Image</label>
                            <div class="col-12">
                                <input type="file" id="image" class="form-control-file" name="image">
                                @error('image')
                                <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="private" id="private" value="1">
                                    <label class="form-check-label" for="private">Private</label>
                                </div>
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

        <div class="popup ol-popup" id="view-spot-popup">
            <div class="card">
                <div class="card-header bg-green">
                    <span class="sedgwick" id="spot-name"></span>
                    <span class="float-right">
                        <a class="btn text-white" id="view-spot-button"><i class="fa fa-eye"></i></a>
                        <a class="btn close-popup-button"><i class="fa fa-times"></i></a>
                    </span>
                </div>
                <div class="card-body bg-grey text-white">
                    <div class="row d-none" id="spot-private">
                        <div class="col">
                            <small>Private</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <img class="w-100" id="spot-image">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col" id="spot-description"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
