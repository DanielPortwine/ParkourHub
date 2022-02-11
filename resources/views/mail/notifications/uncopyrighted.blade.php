@component('mail::message')
# Hi {{ $content->user->name }},

Your {{ $type . ($type === 'entry' ? ' to' : '') }} <strong>{{ $type === 'entry' ? $content->challenge->name : $content->name }}
</strong> that received a copyright claim has been successfully appealed or corrected and has now returned to the site.

@if($type === 'entry')
@component('mail::button', ['url' => route('challenge_view', $content->challenge_id), 'color' => 'green'])
View Challenge
@endcomponent
@else
@component('mail::button', ['url' => route($type . '_view', $content->id), 'color' => 'green'])
View {{ ucfirst($type) }}
@endcomponent
@endif

@endcomponent
