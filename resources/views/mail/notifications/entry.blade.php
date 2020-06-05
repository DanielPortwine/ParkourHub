@component('mail::message')
# Howdy, {{ $entry->challenge->user->name }}!

<a href="{{ route('user_view', $entry->user_id) }}">{{ $entry->user->name }}</a> just entered your challenge {{ $entry->challenge->name }}.

@component('mail::button', ['url' => route('challenge_view', $entry->challenge_id), 'color' => 'green'])
View Challenge
@endcomponent
@endcomponent
