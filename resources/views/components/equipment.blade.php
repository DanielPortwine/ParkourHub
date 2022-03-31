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
                <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    <i class="fa fa-ellipsis-v"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right bg-grey">
                    @if($equipment->user_id === Auth()->id())
                        @premium
                            <a class="dropdown-item text-white" href="{{ route('equipment_edit', $equipment->id) }}" title="Edit"><i class="fa fa-pencil nav-icon"></i>Edit</a>
                        @endpremium
                        @if(isset($tab) && (($tab == null && $originalMovement->type_id === 2) || $tab === 'equipment' && !empty($originalMovement)))
                            <form method="POST" action="{{ route('movement_equipment_unlink') }}" class="d-inline-block">
                                @csrf
                                <input type="hidden" name="movement" value="{{ $originalMovement->id }}">
                                <input type="hidden" name="equipment" value="{{ $equipment->id }}">
                                <button type="submit" class="dropdown-item text-white" title="Unlink"><i class="fa fa-unlink nav-icon"></i>Unlink</button>
                            </form>
                        @endif
                        <a class="dropdown-item text-white" href="{{ route('equipment_delete', $equipment->id) }}" title="Delete Content"><i class="fa fa-trash nav-icon"></i>Delete</a>
                    @endif
                    <a class="dropdown-item text-white" href="{{ route('equipment_report', $equipment->id) }}" title="Report"><i class="fa fa-flag nav-icon"></i>Report</a>
                    @if(count($equipment->reports) > 0 && Route::currentRouteName() == 'report_listing')
                        @can('manage reports')
                            <a class="dropdown-item text-white" href="{{ route('equipment_report_discard', $equipment->id) }}" title="Discard Reports"><i class="fa fa-balance-scale nav-icon"></i>Discard Reports</a>
                        @endcan
                        @can('remove content')
                            <a class="dropdown-item text-white" href="{{ route('equipment_remove', $equipment->id) }}" title="Remove Content"><i class="fa fa-trash nav-icon"></i>Remove</a>
                        @endcan
                    @endif
                    @can('manage copyright')
                        @if($equipment->copyright_infringed_at === null)
                            <a class="dropdown-item text-white" href="{{ route('equipment_copyright_set', $equipment->id) }}" title="Mark Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Claim Copyright</a>
                        @else
                            <a class="dropdown-item text-white" href="{{ route('equipment_copyright_remove', $equipment->id) }}" title="Clear Copyright Infringement"><i class="fa fa-copyright nav-icon"></i>Clear Copyright</a>
                        @endif
                    @endcan
                    <a class="dropdown-item text-white" href="{{ route('movement_listing', ['equipment' => $equipment->id]) }}" title="View Exercises With Equipment"><i class="fa fa-child nav-icon"></i>View Exercises</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col vertical-center">
                <span>{{ count($equipment->movements) . (count($equipment->movements) === 1 ? ' exercise' : ' exercises') }} | {{ $equipment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
