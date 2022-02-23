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
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($movement->user_id === Auth()->id())
                        @premium
                            <a class="dropdown-item text-white" href="{{ route('movement_edit', $movement->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        @endpremium
                        @if(!empty($progressionID) || !empty($advancementID))
                            <form method="POST" action="{{ route('movements_unlink') }}" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="progression" value="{{ $progressionID ?? $movement->id }}">
                                <input type="hidden" name="advancement" value="{{ $advancementID ?? $movement->id }}">
                                <button type="submit" class="dropdown-item text-white" title="Unlink"><i class="fa fa-unlink nav-icon"></i>Unlink</button>
                            </form>
                        @endif
                        @if(!empty($tab) && $tab === 'exercises' && !empty($originalMovement))
                            <form method="POST" action="{{ route('movement_exercise_unlink') }}" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="move" value="{{ $originalMovement->id }}">
                                <input type="hidden" name="exercise" value="{{ $movement->id }}">
                                <button type="submit" class="dropdown-item text-white" title="Unlink"><i class="fa fa-unlink nav-icon"></i>Unlink</button>
                            </form>
                        @endif
                        <a class="dropdown-item text-white" href="{{ route('movement_delete', $movement->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    <a class="dropdown-item text-white" href="{{ route('movement_report', $movement->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @if(count($movement->reports) > 0 && Route::currentRouteName() === 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('movement_report_discard', $movement->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('movement_remove', $movement->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                    @can('manage copyright')
                        @if($movement->copyright_infringed_at === null)
                            <a class="dropdown-item text-white" href="{{ route('movement_copyright_set', $movement->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                        @else
                            <a class="dropdown-item text-white" href="{{ route('movement_copyright_remove', $movement->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                        @endif
                    @endcan
                    @if(!$movement->official)
                        <a class="dropdown-item text-white" href="{{ route('movement_officialise', $movement->id) }}" title="Officialise"><i class="fa fa-check-circle nav-icon"></i>Officialise</a>
                    @else
                        <a class="dropdown-item text-white" href="{{ route('movement_unofficialise', $movement->id) }}" title="Unofficialise"><i class="fa fa-check-circle nav-icon"></i>Unofficialise</a>
                    @endif
                    @if($movement->type_id === 1)
                        <a class="dropdown-item text-white" href="{{ route('spot_listing', ['movement' => $movement->id]) }}" title="View Spots With Move"><i class="fa fa-map-marker nav-icon"></i>View Spots</a>
                    @elseif($movement->type_id === 2)
                            <a class="dropdown-item text-white" href="{{ route('movement_listing', ['exercise' => $movement->id]) }}" title="View Moves For Exercise"><i class="fa fa-child nav-icon"></i>View Moves</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg vertical-center">
                @if($movement->type_id === 1)
                    <span>{{ count($movement->spots) . (count($movement->spots) === 1 ? ' spot' : ' spots') }} | {{ $movement->created_at->diffForHumans() }}</span>
                @elseif($movement->type_id === 2)
                    <span>{{ count($movement->moves) . (count($movement->moves) === 1 ? ' move' : ' moves') }} | {{ $movement->created_at->diffForHumans() }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
