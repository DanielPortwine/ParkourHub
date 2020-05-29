<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $user->id) }}">{{ $user->name }}</a>
            </div>
            <div class="col-md-auto vertical-center">
                <div>
                    @if ($user->id === Auth()->id())
                        <a class="btn text-white" href="{{ route('user_manage') }}" title="Manage"><i class="fa fa-pencil"></i></a>
                    @else
                        @php $followers = $user->followers()->pluck('follower_id')->toArray(); @endphp
                        <a class="follow-user-button btn text-white @if(in_array(Auth()->id(), $followers))d-none @endif" id="follow-user-{{ $user->id }}" title="Follow"><i class="fa fa-user-plus"></i></a>
                        <a class="unfollow-user-button btn text-white @if(!in_array(Auth()->id(), $followers))d-none @endif" id="unfollow-user-{{ $user->id }}" title="Unfollow"><i class="fa fa-user-times"></i></a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row border-subtle pb-1 mb-2">
            @if(!empty($user->hometown_name))
                <div class="col-md">
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
            <div class="col" title="Followers">
                <i class="fa fa-group text-white"></i>
                {{ $user->followers }}
            </div>
            <div class="col" title="Number Of Days Since Registration">
                <i class="fa fa-clock-o text-white"></i>
                {{ Carbon\Carbon::parse($user->email_verified_at)->diffInDays() }}
            </div>
        </div>
    </div>
</div>
