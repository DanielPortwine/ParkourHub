<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($challenge->thumbnail))
            <a href="{{ route('challenge_view', $challenge->id) }}">
                <img class="lazyload" data-src="{{ $challenge->thumbnail }}">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $challenge->id) }}">{{ $challenge->name }}</a>
            </div>
            <div class="col-md-auto">
                @if($challenge->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                @auth
                    <a class="btn text-white" href="{{ route('challenge_report', $challenge->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @endauth
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('challenge_delete', ['id' => $challenge->id, 'from' => 'component']) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($challenge->reports) > 0)
                        <a class="btn text-white" href="{{ route('challenge_report_discard', $challenge->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
                <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                @if(!empty($challenge->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $challenge->user->profile_image }}"><img src="{{ $challenge->user->profile_image }}" alt="Profile image of the user named {{ $challenge->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $challenge->user->id) }}">{{ $challenge->user->name }}</a>
            </div>
            <div class="col-md-auto vertical-center">
                <div>
                    @for($circle = 1; $circle <= 5; $circle++)
                        <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                <span>{{ count($challenge->views) . (count($challenge->views) === 1 ? ' view' : ' views') }} | {{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | {{ $challenge->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
