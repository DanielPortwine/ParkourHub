@component('mail::message')
# Howdy, {{ $user }}!

<a href="{{ route('user_view', $spot->user_id) }}">{{ $spot->user->name }}</a> just created a spot <strong>{{ $spot->name }}</strong>.

@if(!empty($spot->image))
<div class="content-wrapper">
<a href="{{ route('spot_view', $spot->id) }}"><img src="{{ asset($spot->image) }}"></a>
</div>
@endif
@if(!empty($spot->description))
{{ $spot->description }}
@endif

@component('mail::button', ['url' => route('spot_view', $spot->id), 'color' => 'green'])
View Spot
@endcomponent
@endcomponent
