@component('mail::message')
# Howdy, {{ $follower->user->name }}!

<a href="{{ route('user_view', $follower->follower_id) }}">{{ $follower->follower->name }}</a> just started following you.

@component('mail::button', ['url' => route('user_view', $follower->follower_id), 'color' => 'green'])
View User
@endcomponent
@endcomponent
