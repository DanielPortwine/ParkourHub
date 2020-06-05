@component('mail::message')
# Howdy, {{ $entry->user->name }}!

You just won challenge {{ $entry->challenge->name }}.

@component('mail::button', ['url' => route('challenge_view', $entry->challenge_id), 'color' => 'green'])
View Challenge
@endcomponent
@endcomponent
