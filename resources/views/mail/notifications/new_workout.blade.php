@component('mail::message')
# Howdy, {{ $user }}!

<a href="{{ route('user_view', $workout->user_id) }}">{{ $workout->user->name }}</a> just created a workout <strong>{{ $workout->name }}</strong>.

@if(!empty($workout->description))
{{ $workout->description }}
@endif

@component('mail::button', ['url' => route('workout_view', $workout->id), 'color' => 'green'])
View Workout
@endcomponent
@endcomponent
