@extends('layouts.app')

@push('title'){{ Auth()->user()->name }}'s Reviews | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Reviews</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @foreach($reviews->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $review)
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-grey card-hidden-body">
                                                <div class="row">
                                                    <span class="col sedgwick">{{ $review->spot->name }}</span>
                                                    <div class="col-auto d-md-block d-none">
                                                        <div class="rating-stars">
                                                            @for($star = 1; $star <= 5; $star++)
                                                                <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fa fa-caret-down"></i>
                                                    </div>
                                                </div>
                                                <div class="d-md-none d-block row">
                                                    <div class="col">
                                                        <div class="rating-stars">
                                                            @for($star = 1; $star <= 5; $star++)
                                                                <i class="rating-star fa {{ $star <= $review->rating ? 'fa-star' : 'fa-star-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body bg-grey">
                                                <div class="row">
                                                    <span class="col h4 sedgwick">{{ $review->title }}</span>
                                                    <div class="col-auto">
                                                        @if($review->user_id === Auth()->id())
                                                            <a class="btn text-white" href="{{ route('review_edit', $review->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        {!! nl2br(e($review->review)) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
