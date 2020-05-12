<div class="card">
    <div class="card-header bg-green">
        <div class="row">
            <span class="col sedgwick">{{ $card->name }}</span>
            <span class="col-auto">
                @if($card->user_id === Auth()->id())
                    <a class="btn text-white" href="{{ route($type . '_edit', $card->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                @endif
                @if(isset($completed) && $completed === false)
                    <a class="btn text-white" href="{{ route('tick_off_hitlist', $spot) }}" title="Tick Off"><i class="fa fa-check"></i></a>
                @endif
                <a class="btn text-white" href="{{ route($type . '_view', $card->id) }}" title="View"><i class="fa fa-eye"></i></a>
                @if($type === 'spot' || $type === 'hit')
                    <a class="btn text-white" href="{{ route('spots', ['spot' => $spot]) }}" title="Locate"><i class="fa fa-map-marker"></i></a>
                @endif
            </span>
        </div>
    </div>

    <div class="content-description-container">
        @if(!empty($card->image))
            <div class="bg-grey spot-description spot-description-bottom text-white" id="spot-description">{{ strlen($card->description) > 100 ? substr($card->description, 0, 100) . '...' : $card->description }}</div>
            <img src="{{ $card->image }}" class="card-image-top w-100">
        @elseif(!empty($card->video))
            <div class="video-wrapper">
                <video controls>
                    <source src="{{ $card->video }}" type="video/mp4">
                </video>
            </div>
        @elseif(!empty($card->youtube))
            <div class="video-wrapper">
                <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/{{ $card->youtube }}" frameborder="0" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        @endif
    </div>
</div>
