<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($movement->video))
            <div class="content-wrapper">
                <video controls>
                    <source src="{{ $movement->video }}" type="video/{{ $movement->video_type }}">
                </video>
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
                @endif
                @if(Auth()->id() !== 1)
                    <a class="btn text-white" href="{{ route('movement_report', $movement->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @else
                    @if(count($movement->reports) > 0)
                        <a class="btn text-white" href="{{ route('report_discard', ['id' => $movement->id, 'type' => 'App\Movement']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                    @endif
                    <a class="btn text-white" href="{{ route('movement_report_delete', $movement->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('spot_listing', ['movement' => $movement->name]) }}" title="View Spots With Movement"><i class="fa fa-map-marker"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md vertical-center">
                <span>{{ count($movement->spots) . (count($movement->spots) === 1 ? ' spot' : ' spots') }} | {{ $movement->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
