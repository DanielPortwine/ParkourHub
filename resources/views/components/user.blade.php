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
            <div class="col vertical-center">
                <span>{{ count($user->spots) . (count($user->spots) === 1 ? ' spot' : ' spots') }} | {{ count($user->challenges) . (count($user->challenges) === 1 ? ' challenge' : ' challenges') }} | {{ 'Joined ' . Carbon\Carbon::parse($user->email_verified_at)->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
