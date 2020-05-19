<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($challenge->thumbnail))
            <a href="{{ route('challenge_view', $challenge->id) }}">
                <img src="{{ $challenge->thumbnail }}">
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
                <a class="btn text-white" href="{{ route('spots', ['spot' => $challenge->spot_id]) }}" title="Locate Spot"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                <span class="large-text sedgwick">{{ $challenge->user->name }}</span>
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
