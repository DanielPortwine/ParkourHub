@extends('layouts.app')

@push('title'){{ $movement->name }} | @endpush

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show position-absolute w-100 z-10" role="alert">
            {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container p-0">
        @if(!empty($movement->video))
            <div class="content-wrapper">
                <video controls>
                    <source src="{{ $movement->video }}" type="video/{{ $movement->video_type }}">
                </video>
            </div>
        @elseif(!empty($movement->youtube))
            <div class="content-wrapper">
                <div class="youtube" data-id="{{ $movement->youtube }}" data-start="{{ $movement->youtube_start }}">
                    <span class="h-100 flex-center"><i class="fa fa-youtube-play text-shadow z-10"></i></span>
                </div>
            </div>
        @endif
    </div>
    <div class="section grey-section">
        <div class="container">
            <div class="row pt-4">
                <div class="col vertical-center">
                    <h1 class="sedgwick mb-0"><span class="text-{{ $movement->category->class_name }}">[{{ $movement->category->name }}]</span> {{ $movement->name }}</h1>
                </div>
                <div class="col-auto vertical-center">
                    <div>
                        @if(Auth()->id() !== 1)
                            <a class="btn text-white" href="{{ route('movement_report', $movement->id) }}" title="Report"><i class="fa fa-flag"></i></a>
                        @else
                            @if(count($movement->reports) > 0)
                                <a class="btn text-white" href="{{ route('report_discard', ['id' => $movement->id, 'type' => 'App\Movement']) }}" title="Discard Reports"><i class="fa fa-trash"></i></a>
                            @endif
                            <a class="btn text-white" href="{{ route('movement_report_delete', $movement->id) }}" title="Delete Content"><i class="fa fa-ban"></i></a>
                        @endif
                        <a class="btn text-white" href="{{ route('spot_listing', ['movement' => $movement->id]) }}" title="Spots With Movement"><i class="fa fa-map-marker"></i></a>
                        @if ($movement->user->id === Auth()->id())
                            <a class="btn text-white" href="{{ route('movement_edit', $movement->id) }}" title="Edit"><i class="fa fa-pencil"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row pb-2 border-subtle">
                <div class="col">
                    <span>{{ count($movement->spots) . (count($movement->spots) === 1 ? ' spot' : ' spots') }} | {{ $movement->created_at->format('jS M, Y') }}</span>
                </div>
            </div>
            <div class="py-3">
                <div id="description-box">
                    <p class="mb-0" id="description-content">{!! nl2br(e($movement->description)) !!}</p>
                </div>
                <a class="btn btn-link" id="description-more">More</a>
            </div>
        </div>
    </div>
    <div class="fragment-link" id="content"></div>
    <div class="section">
        <div class="container">
            <div class="card bg-black border-0">
                <div class="card-header card-header-black">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link btn-link @if($tab == null || $tab === 'movement')active @endif" href="{{ route('movement_view', ['id' => $movement->id, 'tab' => null]) }}#content">Spots</a>
                        </li>
                    </ul>
                </div>
                @if($tab == null || $tab === 'spots')
                    <div class="card-body bg-black">
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @foreach($spots->chunk(2) as $chunk)
                            <div class="row">
                                @foreach($chunk as $spot)
                                    <div class="col-md-6 mb-4">
                                        @include('components.spot')
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($request['spots']))
                            {{ $spots->links() }}
                        @endif
                        @if (count($movement->spots) === 0)
                            <p class="mb-0">This movement has not been linked to any spots yet.</p>
                        @elseif(count($movement->spots) > 4)
                            <div class="col text-center mb-4">
                                @if(empty($request['spots']))
                                    <a class="btn btn-green w-75" href="?spots=1#content">More</a>
                                @else
                                    <a class="btn btn-green w-75" href="{{ route('movement_view', $movement->id) }}#content">Less</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('components.footer')
@endsection
