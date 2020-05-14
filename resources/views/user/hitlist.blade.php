@extends('layouts.app')

@push('title'){{ Auth()->user()->name }}'s Hitlist | @endpush

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-green sedgwick">Hitlist</div>
                    <div class="card-body bg-grey text-white">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <h3 class="separator sedgwick pb-2 mb-3">Not Ticked Off</h3>
                        @if(count($hitsToTickOff))
                            @foreach($hitsToTickOff->chunk(2) as $chunk)
                                <div class="row">
                                    @foreach($chunk as $hit)
                                        <div class="col-md-6 mb-4">
                                            @include('components.card', ['card' => $hit->spot, 'type' => 'spot', 'spot' => $hit->spot_id, 'completed' => $hit->completed_at ?: false])
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="row">
                                <div class="col">
                                    <p>You have no spots in your hitlist to tick off</p>
                                </div>
                            </div>
                        @endif
                        <h3 class="separator sedgwick pb-2 mb-4">Ticked Off</h3>
                        @if(count($hitsTickedOff))
                            @foreach($hitsTickedOff->chunk(2) as $chunk)
                                <div class="row">
                                    @foreach($chunk as $hit)
                                        <div class="col-md-6 mb-4">
                                            @include('components.card', ['card' => $hit->spot, 'type' => 'spot', 'spot' => $hit->spot_id, 'completed' => $hit->completed_at])
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="row">
                                <div class="col">
                                    <p>You have no spots in your hitlist that you have ticked off</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
