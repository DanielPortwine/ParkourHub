<div class="card bg-grey">
    <div class="content-wrapper">
        @if($movement->official)
            <span class="h3 official-tick" title="Official"><i class="fa fa-check-circle"></i></span>
        @endif
        @if(!empty($movement->thumbnail))
            <a href="{{ route('movement_view', $movement->id) }}">
                @if(isset($lazyload) ? $lazyload : true)
                    <img class="lazyload" data-src="{{ $movement->thumbnail }}" alt="Image of the {{ $movement->name }} movement.">
                @else
                    <img src="{{ $movement->thumbnail }}" alt="Image of the {{ $movement->name }} movement.">
                @endif
            </a>
        @elseif(!empty($movement->video))
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
            <div class="col-lg vertical-center">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('movement_view', $movement->id) }}">{{ $movement->name }}</a>
            </div>
            <div class="col-lg-auto vertical-center pl-0">
                <a class="btn text-white" href="{{ route('movement_recover', $movement->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('movement_remove', $movement->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                <span>Deleted {{ $movement->deleted_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
