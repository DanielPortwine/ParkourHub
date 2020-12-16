@extends('layouts.app')

@push('title')Workout Plan | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container pb-3">
        <div class="sedgwick text-center pt-3">
            <h1>Workout Plan</h1>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="card @error('workout') border-danger @enderror @error('date') border-danger @enderror @error('repeat_frequency') border-danger @enderror @error('repeat_until') border-danger @enderror">
                    <div class="card-header bg-green sedgwick card-hidden-body py-1 py-md-2">
                        <div class="row">
                            <div class="col">
                                Add Workout
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-grey text-white py-0 py-md-2">
                        <form method="POST">
                            @csrf
                            <div class="form-group row vertical-center">
                                <label for="workout" class="col-md-2 col-form-label text-md-right">Workout</label>
                                <div class="col-md-8">
                                    <select class="select2-workouts @error('workout') is-invalid border-danger @enderror" name="workout"></select>
                                    @error('workout')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row vertical-center">
                                <label for="date" class="col-md-2 col-form-label text-md-right">Date</label>
                                <div class="col-md-2">
                                    <input type="date" class="w-100 @error('date') is-invalid border-danger @enderror" name="date">
                                    @error('date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <label for="repeat-frequency" class="col-md-1 col-form-label text-md-right">Repeat</label>
                                <div class="col-md-2 vertical-center">
                                    <select class="select2-repeat-frequency @error('repeat_frequency') is-invalid border-danger @enderror" name="repeat_frequency">
                                        <option value="">Never</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="other">Every Other Day</option>
                                        <option value="daily">Daily</option>
                                    </select>
                                    @error('repeat_frequency')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <label for="repeat-until" class="col-md-1 col-form-label text-md-right">Until</label>
                                <div class="col-md-2">
                                    <input type="date" class="w-100 @error('repeat_until') is-invalid border-danger @enderror" name="repeat_until">
                                    @error('repeat_until')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-2 offset-md-2">
                                    <button type="submit" class="btn btn-green">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-1">
            <div class="col-auto">
                <a class="btn btn-green" href="?month={{ date('Y-m', strtotime($date . '-01 -1 month')) }}"><i class="fa fa-caret-left"></i></a>
                <a class="btn btn-green" href="?month={{ date('Y-m', strtotime($date . '-01 +1 month')) }}"><i class="fa fa-caret-right"></i></a>
            </div>
            <div class="col-auto">
                <a class="btn btn-green" href="?">Today</a>
            </div>
            <div class="col">
                <h3 class="sedgwick">{{ date('F Y', strtotime($date . '-01')) }}</h3>
            </div>
        </div>
        <div class="row no-gutters">
            <div class="col text-center">Mon</div>
            <div class="col text-center">Tue</div>
            <div class="col text-center">Wed</div>
            <div class="col text-center">Thu</div>
            <div class="col text-center">Fri</div>
            <div class="col text-center">Sat</div>
            <div class="col text-center">Sun</div>
        </div>
        @foreach($weeks as $week)
            <div class="row no-gutters h-{{ count($weeks) === 5 ? '20' : '25' }}">
                @foreach($week as $day)
                    <div class="col">
                        @if(!empty($day))
                            <div class="card h-100">
                                <div class="card-header calendar-card-header bg-green sedgwick">
                                    <span @if(date('Y-m-d') == $date . '-' . str_pad($day['day'], 2, '0', STR_PAD_LEFT))class="current-day" @endif>{{ $day['day'] }}</span>
                                </div>
                                <div class="card-body calendar-card-body bg-grey text-white p-1">
                                    @foreach($day['workouts'] as $workout)
                                        <div class="badge text-left btn-green w-100 p-1 overflow-hidden">
                                            <a class="text-white" href="{{ route('workout_plan_remove_workout', $workout->pivot->id) }}"><i class="fa fa-times"></i></a>
                                            <a class="text-white" href="{{ route('workout_view', $workout->id) }}">{{ $workout->name }}</a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="card h-100">
                                <div class="card-header calendar-card-header bg-black sedgwick"></div>
                                <div class="card-body calendar-card-body bg-black text-white"></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection

@push('scripts')
    <script defer>
        $(document).ready(function() {
            $('.select2-repeat-frequency').select2({
                width: '100%',
            });
        });
        $.ajax({
            url: '/workouts/plan/getUserWorkouts',
            success: function (response) {
                $('.select2-workouts').select2({
                    data: response,
                    width: '100%',
                });
            },
        });
    </script>
@endpush
