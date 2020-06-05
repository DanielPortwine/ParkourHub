@component('mail::message')
# Howdy, {{ $user }}!

<a href="{{ route('user_view', $challenge->user_id) }}">{{ $challenge->user->name }}</a> just created a challenge <strong>{{ $challenge->name }}</strong>.

@if(!empty($challenge->thumbnail))
<div class="content-wrapper">
<a href="{{ route('challenge_view', $challenge->id) }}"><img src="{{ asset($challenge->thumbnail) }}"></a>
</div>
@endif
@if(!empty($challenge->description))
{{ $challenge->description }}
@endif

@component('mail::button', ['url' => route('challenge_view', $challenge->id), 'color' => 'green'])
View Challenge
@endcomponent
@endcomponent
