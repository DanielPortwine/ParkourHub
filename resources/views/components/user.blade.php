<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row border-subtle mb-2">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $user->id) }}">{{ $user->name }}</a>
            </div>
            @if(!empty($user->hometown_name))
                <div class="col-md-auto">
                    {{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}
                </div>
            @endif
        </div>
        <div class="row text-center">
            <div class="col" title="Number Of Spots Created">
                <i class="fa fa-map-marker text-white"></i>
                {{ count($user->spots) }}
            </div>
            <div class="col" title="Number Of Challenges Created">
                <i class="fa fa-bullseye text-white"></i>
                {{ count($user->challenges) }}
            </div>
            <div class="col" title="Number Of Spots Reviewed">
                <i class="fa fa-star text-white"></i>
                {{ count($user->reviews) }}
            </div>
            <div class="col" title="Number Of Comments On Spots">
                <i class="fa fa-comment text-white"></i>
                {{ count($user->spotComments) }}
            </div>
            <div class="col" title="Number Of Days Since Registration">
                <i class="fa fa-clock-o text-white"></i>
                {{ Carbon\Carbon::parse($user->email_verified_at)->diffInDays() }}
            </div>
        </div>
    </div>
</div>
