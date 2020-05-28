<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <span class="large-text sedgwick">{{ $user->name }}</span>
            </div>
            <div class="col-md-auto">
                {{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}
            </div>
        </div>
        <div class="row">
            <div class="col-auto" title="Number Of Spots Created">
                <i class="fa fa-map-marker text-white"></i>
                {{ count($user->spots) }}
            </div>
            <div class="col-auto" title="Number Of Challenges Created">
                <i class="fa fa-bullseye text-white"></i>
                {{ count($user->challenges) }}
            </div>
            <div class="col-auto" title="Number Of Days Since Registration">
                <i class="fa fa-clock-o text-white"></i>
                {{ Carbon\Carbon::parse($user->email_verified_at)->diffInDays() }}
            </div>
        </div>
    </div>
</div>
