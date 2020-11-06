<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($equipment->image))
            <a href="{{ route('equipment_view', $equipment->id) }}">
                <img class="lazyload" data-src="{{ $equipment->image }}">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('equipment_view', $equipment->id) }}">{{ $equipment->name }}</a>
            </div>
            <div class="col-md-auto">
                @if($equipment->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('equipment_edit', $equipment->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                    @if(($tab == null && $originalMovement->type_id === 2) || $tab === 'equipment' && !empty($originalMovement))
                        <form method="POST" action="{{ route('movement_equipment_unlink') }}" class="d-inline-block">
                            @csrf
                            <input type="hidden" name="movement" value="{{ $originalMovement->id }}">
                            <input type="hidden" name="equipment" value="{{ $equipment->id }}">
                            <button type="submit" class="btn text-white" title="Unlink"><i class="fa fa-unlink"></i></button>
                        </form>
                    @endif
                @endif
                <a class="btn text-white" href="{{ route('equipment_report', $equipment->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                @if(Auth()->id() === 1)
                    <a class="btn text-white" href="{{ route('equipment_delete', $equipment->id) }}" title="Delete Content"><i class="fa fa-trash"></i></a>
                    @if(count($equipment->reports) > 0)
                        <a class="btn text-white" href="{{ route('equipment_report_discard', $equipment->id) }}" title="Discard Reports"><i class="fa fa-balance-scale"></i></a>
                    @endif
                @endif
                <a class="btn text-white" href="{{ route('movement_listing', ['equipment' => $equipment->id]) }}" title="View Exercises With Equipment"><i class="fa fa-child"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col vertical-center">
                <span>{{ count($equipment->movements) . (count($equipment->movements) === 1 ? ' exercise' : ' exercises') }} | {{ $equipment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
