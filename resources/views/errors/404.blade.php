@extends('layouts.app')

@push('title')404 - Not Found | @endpush

@section('description')404 - Unfortunately this page could not be found.@endsection

@section('content')
    <div class="flex-center vh-100-nav">
        <div class="text-center">
            <div class="page-title sedgwick">
                404 | Page Not Found
            </div>
            <span class="h2">{{ config('errors.404')[rand(0, count(config('errors.404')) - 1)] }}</span>
        </div>
    </div>
@endsection
