@component('mail::message')
# Howdy, {{ $comment->spot->user->name }}!

<a href="{{ route('user_view', $comment->user_id) }}">{{ $comment->user->name }}</a> just commented on your spot {{ $comment->spot->name }}.

@if(!empty($comment->image))
<div class="content-wrapper">
<a href="{{ route('spot_view', $comment->spot_id) }}"><img src="{{ asset($comment->image) }}"></a>
</div>
@endif
@if(!empty($comment->comment))
{{ $comment->comment }}
@endif

@component('mail::button', ['url' => route('spot_view', $comment->spot_id), 'color' => 'green'])
View Spot
@endcomponent
@endcomponent
