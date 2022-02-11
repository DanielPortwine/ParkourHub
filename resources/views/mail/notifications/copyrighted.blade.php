@component('mail::message')
# Hi {{ $content->user->name }},

Your {{ $type . ($type === 'entry' ? ' to' : '') }} <strong>{{ $type === 'entry' ? $content->challenge->name : $content->name }}
</strong> has been claimed as a copyright infringement and is now hidden from the site.

Please update this {{ $type }} and let us know once you've done so and we will let it back on to the site.

If you would like to discuss or appeal the claim, please get in touch.

Note: Since you can't edit a challenge entry, you will need to appeal the claim or create a new entry and delete the old one.

@if($type === 'entry')
@component('mail::button', ['url' => route('challenge_view', $content->challenge_id), 'color' => 'green'])
View Challenge
@endcomponent
@else
@component('mail::button', ['url' => route($type . '_edit', $content->id), 'color' => 'green'])
Edit {{ ucfirst($type) }}
@endcomponent
@endif

@endcomponent
