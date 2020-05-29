@extends('layouts.app')

@push('title'){{ $title }} | @endpush

@section('content')
    <div class="container-fluid pt-4">
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row">
            <div class="col">
                <h1 class="sedgwick text-center pb-3">{{ $title }}</h1>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-green sedgwick @if(empty($_GET))card-hidden-body @endif">
                        <div class="row">
                            <div class="col">
                                Filters
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-grey text-white">
                        <form method="GET">
                            <div class="row">
                                @if($component !== 'user')
                                    <div class="col-auto pb-3">
                                        <label><strong>Created Between: </strong></label>
                                        <div>
                                            <input type="date" name="date_from" value="{{ $_GET['date_from'] ?? '' }}">
                                            <input type="date" name="date_to" value="{{ $_GET['date_to'] ?? '' }}">
                                        </div>
                                    </div>
                                @endif
                                @if($component === 'spot' || $component === 'challenge')
                                    <div class="col-auto pb-3">
                                        <label><strong>Following</strong></label>
                                        <div class="form-check text-center">
                                            <input class="form-check-input" type="checkbox" name="following" id="following" {{ !empty($_GET['following']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="following"></label>
                                        </div>
                                    </div>
                                @endif
                                @if($component === 'spot')
                                    <div class="col-auto pb-3">
                                        <label><strong>Rating</strong></label>
                                        <input type="hidden" id="rating" name="rating" value="{{ $_GET['rating'] ?? '0' }}">
                                        <div>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                        </div>
                                    </div>
                                    @if(empty($hitlist))
                                        <div class="col-auto pb-3">
                                            <label><strong>On Hitlist</strong></label>
                                            <div class="form-check text-center">
                                                <input class="form-check-input" type="checkbox" name="on_hitlist" id="on-hitlist" {{ !empty($_GET['on_hitlist']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="on-hitlist"></label>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-auto pb-3">
                                        <label><strong>Ticked Off</strong></label>
                                        <div class="form-check text-center">
                                            <input class="form-check-input" type="checkbox" name="ticked_hitlist" id="ticked-hitlist" {{ !empty($_GET['ticked_hitlist']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ticked-hitlist"></label>
                                        </div>
                                    </div>
                                @elseif($component === 'challenge')
                                    <div class="col-auto pb-3">
                                        <label><strong>Entered</strong></label>
                                        <div class="form-check text-center">
                                            <input class="form-check-input" type="checkbox" name="entered" id="entered"  {{ !empty($_GET['entered']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="entered"></label>
                                        </div>
                                    </div>
                                    <div class="col-auto pb-3">
                                        <label><strong>Difficulty</strong></label>
                                        <input type="hidden" id="difficulty" name="difficulty" value="{{ $_GET['difficulty'] ?? '0' }}">
                                        <div>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-1"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-2"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-3"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-4"></i>
                                            <i class="rating-circle editable fa fa-circle-o" id="rating-circle-5"></i>
                                        </div>
                                    </div>
                                @elseif($component === 'entry')
                                    <div class="col-auto pb-3">
                                        <label><strong>Winner</strong></label>
                                        <div class="form-check text-center">
                                            <input class="form-check-input" type="checkbox" name="winner" id="winner" {{ ($_GET['winner'] ?? '') === 'on' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="winner"></label>
                                        </div>
                                    </div>
                                @elseif($component === 'review')
                                    <div class="col-auto pb-3">
                                        <label><strong>Rating</strong></label>
                                        <input type="hidden" id="rating" name="rating" value="{{ $_GET['rating'] ?? '0' }}">
                                        <div>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-1"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-2"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-3"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-4"></i>
                                            <i class="rating-star editable fa fa-star-o" id="rating-star-5"></i>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-auto pb-3">
                                    <label><strong>Sort</strong></label>
                                    <div>
                                        <select name="sort">
                                            <option value="date_desc" @if(($_GET['sort'] ?? '') === 'date_desc')selected @endif>Newest</option>
                                            <option value="date_asc" @if(($_GET['sort'] ?? '') === 'date_asc')selected @endif>Oldest</option>
                                            @if($component === 'spot')
                                                <option value="rating_desc" @if(($_GET['sort'] ?? '') === 'rating_desc')selected @endif>Highest Rated</option>
                                                <option value="rating_asc" @if(($_GET['sort'] ?? '') === 'rating_asc')selected @endif>Lowest Rated</option>
                                                <option value="views_desc" @if(($_GET['sort'] ?? '') === 'views_desc')selected @endif>Most Viewed</option>
                                                <option value="views_asc" @if(($_GET['sort'] ?? '') === 'views_asc')selected @endif>Least Viewed</option>
                                            @elseif($component === 'challenge')
                                                <option value="difficulty_desc" @if(($_GET['sort'] ?? '') === 'difficulty_desc')selected @endif>Most Difficult</option>
                                                <option value="difficulty_asc" @if(($_GET['sort'] ?? '') === 'difficulty_asc')selected @endif>Least Difficult</option>
                                                <option value="entries_desc" @if(($_GET['sort'] ?? '') === 'entries_desc')selected @endif>Most Entries</option>
                                                <option value="entries_asc" @if(($_GET['sort'] ?? '') === 'entries_asc')selected @endif>Least Entries</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <button class="btn btn-green" type="submit">Filter</button>
                                    <a class="btn btn-link" href="?">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{ $content->links() }}
        @foreach($content->chunk(4) as $chunk)
            <div class="row">
                @foreach($chunk as $card)
                    <div class="col-xl-3 col-md-6 mb-4">
                        @include('components.' . $component, array_merge([$component => $card], $options ?? []))
                    </div>
                @endforeach
            </div>
        @endforeach
        {{ $content->links() }}
    </div>
@endsection
