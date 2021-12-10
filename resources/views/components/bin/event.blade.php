<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($event->thumbnail))
            <a href="{{ route('event_view', $event->id) }}">
                <img class="lazyload" data-src="{{ $event->thumbnail }}" alt="Image of the {{ $event->name }} event.">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="d-block d-lg-flex col-lg vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('event_view', $event->id) }}">{{ $event->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('challenge_recover', $challenge->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('challenge_remove', $challenge->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($event->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $event->user->profile_image }}"><img src="{{ $event->user->profile_image }}" alt="Profile image of the user named {{ $event->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $event->user->id) }}">{{ $event->user->name }}</a>
            </div>
        </div>
        <div class="row pt-lg-2">
            <div class="col-lg vertical-center">
                {{ Carbon\Carbon::parse($event->date_time)->format('d/m/Y H:i') }} | {{ Carbon\Carbon::parse($event->date_time)->diffForHumans(['options' => Carbon\Carbon::ONE_DAY_WORDS]) }}
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                <span>{{ count($event->spots) . (count($event->spots) === 1 ? ' spot' : ' spots') }} | {{ count($event->attendees) . (count($event->attendees) === 1 ? ' attendee' : ' attendees') }}</span>
            </div>
        </div>
    </div>
</div>
