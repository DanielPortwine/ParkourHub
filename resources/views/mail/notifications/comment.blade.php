@component('mail::message')
# Hey, {{ $commentableOwnerName }}!

<a href="{{ route('user_view', $comment->user_id) }}">{{ $commenter }}</a> just commented on your {{ $commentableType }} '{{ $commentableName }}'.

@if(!empty($comment->image))
<div class="content-wrapper">
<a href="{{ $route }}"><img src="{{ asset($comment->image) }}"></a>
</div>
@endif
@if(!empty($comment->comment))
{{ $comment->comment }}
@endif

@component('mail::button', ['url' => $route, 'color' => 'green'])
View Spot
@endcomponent
@endcomponent
