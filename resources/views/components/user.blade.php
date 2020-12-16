<div class="card bg-grey">
    <div class="pt-3 pb-md-3 px-4">
        <div class="row">
            <div class="col-md vertical-center">
                @if(!empty($user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $user->profile_image }}"><img src="{{ $user->profile_image }}" alt="Profile image of the user named {{ $user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $user->id) }}">{{ $user->name }}</a>
            </div>
            <div class="col-md-auto vertical-center py-2 py-md-0">
                <div>
                    @if ($user->id === Auth()->id())
                        <a class="btn text-white" href="{{ route('user_manage') }}" title="Manage"><i class="fa fa-pencil"></i></a>
                    @else
                        @if(!empty($title) && $title === 'Follow Requests')
                            <a class="accept-follower-button btn text-white" href="{{ route('user_accept_follower', $user->id) }}" title="Accept Follower"><i class="fa fa-check"></i></a>
                            <a class="reject-follower-button btn text-white" href="{{ route('user_reject_follower', $user->id) }}" title="Reject Follower"><i class="fa fa-times"></i></a>
                        @elseif(!empty($title) && $title === 'Followers')
                            <a class="btn text-white" href="{{ route('user_remove_follower', $user->id) }}" title="Remove Follower"><i class="fa fa-ban"></i></a>
                        @endif
                        @php
                            $followSetting = setting('privacy_follow', 'nobody', $user->id);
                            $followers = $user->followers()->pluck('follower_id')->toArray();
                        @endphp
                        @if(in_array(Auth()->id(), $followers))
                            <a class="btn text-white" href="{{ route('user_unfollow', $user->id) }}" title="Unfollow"><i class="fa fa-user-times"></i></a>
                        @else
                            @if($followSetting === 'anybody')
                                <a class="btn text-white" href="{{ route('user_follow', $user->id) }}" title="Follow"><i class="fa fa-user-plus"></i></a>
                            @elseif($followSetting === 'request')
                                <a class="btn text-white" href="{{ route('user_follow', $user->id) }}" title="Request to follow"><i class="fa fa-user-plus"></i></a>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @if(!empty($user->hometown_name) && (
                (
                    setting('privacy_hometown', null, $user->id) === 'anybody' || (
                        setting('privacy_hometown', null, $user->id) === 'follower' &&
                        !empty($user->followers->firstWhere('id', Auth()->id()))
                    )
                ) ||
                $user->id === Auth()->id()
            ))
            <div class="row">
                <div class="col">
                    {{ explode(',', $user->hometown_name)[0] . ', ' . explode(',', $user->hometown_name)[1] }}
                </div>
            </div>
        @endif
    </div>
</div>
