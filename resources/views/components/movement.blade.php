<div class="card bg-grey">
    <div class="content-wrapper">
        @if($movement->official)
            <span class="h3 official-tick" title="Official"><i class="fa fa-gavel"></i></span>
        @endif
        @if(!empty($movement->video))
            <video controls>
                <source src="{{ $movement->video }}" type="video/{{ $movement->video_type }}">
            </video>
        @elseif(!empty($movement->youtube))
            <div class="youtube" data-id="{{ $movement->youtube }}" data-start="{{ $movement->youtube_start }}">
                <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
            </div>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('movement_view', $movement->id) }}">{{ $movement->name }}</a>
            </div>
            <div class="col-md-auto">
                @if($movement->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('movement_edit', $movement->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @if(!empty($progressionID) || !empty($advancementID))
                        <form method="POST" action="{{ route('movements_unlink') }}" class="d-inline-block">
                            @csrf
                            <input type="hidden" name="progression" value="{{ $progressionID ?? $movement->id }}">
                            <input type="hidden" name="advancement" value="{{ $advancementID ?? $movement->id }}">
                            <button type="submit" class="btn text-white" title="Unlink"><i class="fa fa-unlink"></i></button>
                        </form>
                    @endif
                    @if(!empty($tab) && $tab === 'exercises' && !empty($originalMovement))
                        <form method="POST" action="{{ route('movement_exercise_unlink') }}" class="d-inline-block">
                            @csrf
                            <input type="hidden" name="move" value="{{ $originalMovement->id }}">
                            <input type="hidden" name="exercise" value="{{ $movement->id }}">
                            <button type="submit" class="btn text-white" title="Unlink"><i class="fa fa-unlink"></i></button>
                        </form>
                    @endif
                @endif
                <a class="btn text-white" href="{{ route('movement_report', $movement->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('movement_delete', $movement->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($movement->reports) > 0)
                        <a class="btn text-white" href="{{ route('movement_report_discard', $movement->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
                @if(!$movement->official)
                    <a class="btn text-white" href="{{ route('movement_officialise', $movement->id) }}" title="Officialise"><i class="fa fa-gavel"></i></a>
                @else
                    <a class="btn text-white" href="{{ route('movement_unofficialise', $movement->id) }}" title="Unofficialise"><i class="fa fa-gavel"></i></a>
                @endif
                @if($movement->type_id === 1)
                    <a class="btn text-white" href="{{ route('spot_listing', ['movement' => $movement->id]) }}" title="View Spots With Move"><i class="fa fa-map-marker"></i></a>
                @elseif($movement->type_id === 2)
                        <a class="btn text-white" href="{{ route('movement_listing', ['exercise' => $movement->id]) }}" title="View Moves For Exercise"><i class="fa fa-child"></i></a>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                @if($movement->type_id === 1)
                    <span>{{ count($movement->spots) . (count($movement->spots) === 1 ? ' spot' : ' spots') }} | {{ $movement->created_at->diffForHumans() }}</span>
                @elseif($movement->type_id === 2)
                    <span>{{ count($movement->moves) . (count($movement->moves) === 1 ? ' move' : ' moves') }} | {{ $movement->created_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
