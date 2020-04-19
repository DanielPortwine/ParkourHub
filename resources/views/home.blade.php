@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-green sedgwick">Account Information</div>

                <div class="card-body bg-grey text-white">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user_update') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label for="name">Username</label>
                            <input type="text" id="name" class="form-control" name="name" value="{{ $user->name }}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control" name="email" value="{{ $user->email }}">
                        </div>
                        <button type="submit" class="btn btn-green">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
