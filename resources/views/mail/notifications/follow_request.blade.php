@component('mail::message')
# Howdy, {{ $follower->user->name }}!

<a href="{{ route('user_view', $follower->follower_id) }}">{{ $follower->follower->name }}</a> just requested to follow you.

@component('mail::button', ['url' => route('user_follow_requests'), 'color' => 'green'])
View Requests
@endcomponent
@endcomponent
