<div class="card bg-grey">
    <div class="content-wrapper">
        @if(!empty($equipment->image))
            <a href="{{ route('equipment_view', $equipment->id) }}">
                <img class="lazyload" data-src="{{ $equipment->image }}" alt="Image of the {{ $equipment->name }} equipment">
            </a>
        @endif
    </div>
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('equipment_view', $equipment->id) }}">{{ $equipment->name }}</a>
            </div>
            <div class="col-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('equipment_recover', $equipment->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('equipment_remove', $equipment->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col vertical-center">
                <span>Deleted {{ $equipment->deleted_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
