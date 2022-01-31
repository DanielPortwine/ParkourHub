@extends('layouts.app')

@push('title')Edit Movement | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Edit Movement</div>
                    <div class="card-body bg-grey text-white">
                        <form method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row">
                                <label for="name" class="col-md-2 col-form-label text-md-right">Name</label>
                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="name" value="{{ $movement->name }}">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="description" class="col-md-2 col-form-label text-md-right">Description</label>
                                <div class="col-md-8">
                                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description">{{ $movement->description }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Youtube or Video</label>
                                <div class="col-md-8">
                                    @if(!empty($movement->youtube))
                                        <div class="row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <div class="youtube" data-id="{{ $movement->youtube }}" data-start="{{ $movement->youtube_start }}">
                                                        <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(!empty($movement->video))
                                        <div class="row">
                                            <div class="col">
                                                <div class="content-wrapper">
                                                    <video controls>
                                                        <source src="{{ $movement->video }}" type="video/{{ $movement->video_type }}">
                                                    </video>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-lg-4 col-md-8 offset-md-2 mb-md-2">
                                    <input type="text" id="youtube" class="form-control @error('youtube') is-invalid @enderror" name="youtube" autocomplete="youtube" placeholder="e.g. https://youtu.be/QDIVrf2ZW0s">
                                    @error('youtube')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="col-lg-4 col-md-8 offset-md-2 offset-lg-0">
                                    <input type="file" id="video" class="form-control-file @error('video') is-invalid @enderror" name="video">
                                    @error('video')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-md-2 col-form-label text-md-right">Thumbnail</label>
                                <div class="col-md-8">
                                    @if(!empty($movement->thumbnail))
                                        <img class="w-100 mb-2" src="{{ $movement->thumbnail }}" alt="Image of the {{ $movement->name }} movement.">
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-lg-4 col-md-8 offset-md-2">
                                    <input type="file" id="thumbnail" class="form-control-file @error('thumbnail') is-invalid border-danger @enderror" name="thumbnail">
                                    @error('thumbnail')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Fields</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-no-search" name="fields[]" multiple="multiple">
                                        @foreach($movementFields as $field)
                                            <option value="{{ $field->id }}" @if(in_array($field->id, $movement->fields()->pluck('movement_field_id')->toArray()))selected @endif>{{ $field->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-2 col-form-label text-md-right">Equipment</label>
                                <div class="col-md-8 vertical-center">
                                    <select class="select2-no-search @error('equipment') is-invalid border-danger @enderror" name="equipment[]" multiple="multiple">
                                        @foreach($equipments as $equipment)
                                            <option value="{{ $equipment->id }}" @if(in_array($equipment->id, $movement->equipment()->pluck('equipment.id')->toArray()))selected @endif>{{ $equipment->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('equipment')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="visibility" class="col-md-2 col-form-label text-md-right">Visibility</label>
                                <div class="col-md-8">
                                    <select name="visibility" class="form-control select2-no-search">
                                        @foreach(config('settings.privacy.privacy_content.options') as $key => $name)
                                            <option value="{{ $key }}" @if($movement->visibility === $key)selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-10 offset-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input @error('link_access') is-invalid @enderror" type="checkbox" name="link_access" id="link_access" value="1" @if($movement->link_access) checked @endif>
                                        <label class="form-check-label" for="link_access">Anyone with link can view</label>
                                        @error('link_access')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-2">
                                    <button type="submit" class="btn btn-green">Save</button>
                                    <a class="btn btn-danger require-confirmation float-right">Delete</a>
                                    <input type="hidden" name="redirect" value="{{ session('redirect') ?? url()->previous() }}">
                                    <input type="submit" class="btn btn-danger d-none confirmation-button float-right" name="delete" value="Confirm">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
