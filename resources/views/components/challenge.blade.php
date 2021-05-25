<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($challenge->thumbnail))
            <a href="{{ route('challenge_view', $challenge->id) }}">
                <img class="lazyload" data-src="{{ $challenge->thumbnail }}" alt="Image of the {{ $challenge->name }} challenge.">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="d-block d-lg-flex col-lg vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('challenge_view', $challenge->id) }}">{{ $challenge->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                @if($challenge->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('challenge_edit', $challenge->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    <a class="btn text-white" href="{{ route('challenge_delete', $challenge->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                @endif
                @auth
                    <a class="btn text-white" href="{{ route('challenge_report', $challenge->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @endauth
                @if(count($challenge->reports) > 0 && Route::currentRouteName() === 'report_listing')
                    @can('manage reports')
                        <a class="btn text-white" href="{{ route('challenge_report_discard', $challenge->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endcan
                    @can('remove content')
                        <a class="btn text-white" href="{{ route('challenge_remove', $challenge->id) }}" title="Remove Content"><i class="fa fa-trash"></i></a>
                    @endcan
                @endif
                @if(!empty($challenge->spot))
                    <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if(!empty($challenge->user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $challenge->user->profile_image }}"><img src="{{ $challenge->user->profile_image }}" alt="Profile image of the user named {{ $challenge->user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $challenge->user->id) }}">{{ $challenge->user->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pt-2 pt-lg-0">
                <div>
                    @for($circle = 1; $circle <= 5; $circle++)
                        <i class="rating-circle pr-1 fa {{ $circle <= $challenge->difficulty ? 'fa-circle' : 'fa-circle-o' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="row pt-lg-2">
            <div class="col-lg vertical-center">
                <span>{{ count($challenge->entries) . (count($challenge->entries) === 1 ? ' entry' : ' entries') }} | {{ $challenge->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
