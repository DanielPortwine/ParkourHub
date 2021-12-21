@component('mail::message')
# Hi, {{ $user }}!

<a href="{{ route('user_view', $event->user_id) }}">{{ $event->user->name }}</a> has invited you to attend an event: <strong>{{ $event->name }}</strong>.
The event will be held on {{ Carbon\Carbon::parse($event->date_time)->format('D, d M H:i') }}

@if(!empty($event->thumbnail))
<div class="content-wrapper">
<a href="{{ route('event_view', $event->id) }}"><img src="{{ asset($event->thumbnail) }}"></a>
</div>
@endif
@if(!empty($event->description))
@component('mail::panel')
{{ $event->description }}
@endcomponent
@endif

This event will take place at the following spots:<br>
@foreach($event->spots as $spot)
<a href="{{ route('spot_view', $spot->id) }}">{{ $spot->name }}</a><br>
@endforeach

@component('mail::button', ['url' => route('event_view', $event->id), 'color' => 'green'])
View Event
@endcomponent
@endcomponent
