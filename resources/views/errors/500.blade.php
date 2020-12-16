@extends('layouts.error')

@push('title')500 - Server Error | @endpush

@section('description')500 - Unfortunately something went wrong.@endsection

@section('content')
    <div class="flex-center vh-100-nav">
        <div class="text-center">
            <div class="page-title sedgwick">
                500 | Server
            </div>
            <p class="h2">{{ config('errors.500')[rand(0, count(config('errors.500')) - 1)] }} Go back and try again.</p>
            <p class="h5">If the issue persists, send a <strong>detailed</strong> explanation of what you did to support@parkourhub.com.</p>
        </div>
    </div>
@endsection
