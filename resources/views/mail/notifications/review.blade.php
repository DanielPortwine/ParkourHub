@component('mail::message')
# Howdy, {{ $review->spot->user->name }}!

<a href="{{ route('user_view', $review->user_id) }}">{{ $review->user->name }}</a> just gave your spot {{ $review->spot->name }} a {{ $review->rating }}* review.

@if(!empty($review->review))
{{ $review->review }}
@endif

@component('mail::button', ['url' => route('spot_view', $review->spot_id), 'color' => 'green'])
View Spot
@endcomponent
@endcomponent
