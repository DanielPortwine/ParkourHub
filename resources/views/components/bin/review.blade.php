<div class="card bg-grey">
    @if(isset($user) && $user === true)
        <div class="content-wrapper">
            @if(!empty($review->spot->image))
                <a href="{{ route('spot_view', $review->spot->id) }}">
                    <img class="lazyload" data-src="{{ $review->spot->image }}" alt="Image of spot {{ $review->spot->name }} for review {{ $review->title }}.">
                </a>
            @endif
        </div>
    @endif
    <div class="py-3 px-4">
        <div class="row border-subtle">
            <div class="col sedgwick">
                <a class="btn-link h3 mb-0 sedgwick" href="{{ route('spot_view', $review->spot_id) }}">{{ $review->spot->name }}</a>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                <h4 class="sedgwick text-large">{{ $review->title }}</h4>
            </div>
            <div class="col-md-auto vertical-center">
                <div class="rating-stars pr-2 d-none d-lg-block">
                    @for($star = 1; $star <= 5; $star++)
                        <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                    @endfor
                </div>
                <a class="btn text-white" href="{{ route('review_recover', $review->id) }}" title="Recover"><i class="fa fa-history"></i></a>
                <a class="btn text-white" href="{{ route('review_remove', $review->id) }}" title="Remove Forever"><i class="fa fa-trash"></i></a>
            </div>
        </div>
        <div class="row d-lg-none mb-2">
            <div class="col">
                <div class="rating-stars pr-2">
                    @for($star = 1; $star <= 5; $star++)
                        <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                {!! nl2br(e($review->review)) !!}
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md vertical-center">
                <span>Deleted {{ $review->deleted_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
