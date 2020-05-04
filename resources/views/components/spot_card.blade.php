<div class="card">
    <div class="card-header bg-green">
        <div class="row">
            <span class="col sedgwick">{{ $spot->name }}</span>
            <span class="col-auto">
                @if($spot->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route('spot_edit', $spot->id) }}"><i class="fa fa-pencil"></i></a>
                @endif
                <a class="btn text-white" href="{{ route('spot_view', $spot->id) }}"><i class="fa fa-eye"></i></a>
                <a class="btn text-white" href="{{ route('spots', ['spot' => $spot->id]) }}"><i class="fa fa-map-marker"></i></a>
            </span>
        </div>
    </div>
    <img src="{{ $spot->image }}" class="card-image-top w-100">
    <div class="card-body bg-grey text-white">
        <p class="card-text mt-auto">{{ $spot->description }}</p>
    </div>
</div>
