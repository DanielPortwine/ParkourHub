<div class="card bg-grey">
    <div class="py-3 px-4">
        <div class="row">
            <div class="col vertical-center">
                @if(!empty($user->profile_image))
                    <div class="profile-image-wrapper--component pr-3">
                        <a href="{{ $user->profile_image }}"><img src="{{ $user->profile_image }}" alt="Profile image of the user named {{ $user->name }}."></a>
                    </div>
                @endif
                <a class="btn-link large-text sedgwick" href="{{ route('user_view', $user->id) }}">{{ $user->name }}</a>
            </div>
            <div class="col-auto vertical-center py-2 py-md-0">
                @if(!empty(Auth()->user()->email_verified_at))
                    <div>
                        <a class="btn text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-grey">
                            @if ($user->id === Auth()->id())
                                <a class="dropdown-item text-white" href="{{ route('user_manage') }}" title="Manage"><i class="fa fa-pencil nav-icon"></i>Manage</a>
                            @elseif(!empty($event) && $event->user_id === Auth()->id() && $event->accept_method === 'accept' && !empty($tab))
                                @if($tab === 'attendees')
                                    <a class="accept-follower-button dropdown-item text-white" href="{{ route('event_attendee_delete', ['event' => $event->id, 'user' => $user->id]) }}" title="Remove Attendee"><i class="fa fa-times nav-icon"></i>Remove Attendee</a>
                                @elseif($tab === 'applicants')
                                    <form id="accept-form" method="POST" action="{{ route('event_attendee_update', $event->id) }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="user" value="{{ $user->id }}">
                                        <input type="hidden" name="accepted" value="true">
                                    </form>
                                    <button class="accept-follower-button dropdown-item text-white" type="submit" form="accept-form" title="Accept Attendee"><i class="fa fa-check nav-icon"></i>Accept Attendee</button>
                                    <a class="accept-follower-button dropdown-item text-white" href="{{ route('event_attendee_delete', ['event' => $event->id, 'user' => $user->id]) }}" title="Reject Attendee"><i class="fa fa-times nav-icon"></i>Reject Attendee</a>
                                @endif
                            @else
                                @can('manage bans')
                                    @if($user->banned_at === null)
                                        <a class="dropdown-item text-white" href="{{ route('user_ban', $user->id) }}" title="Ban"><i class="fa fa-gavel nav-icon"></i>Ban</a>
                                    @else
                                        <a class="dropdown-item text-white" href="{{ route('user_unban', $user->id) }}" title="Unban"><i class="fa fa-balance-scale nav-icon"></i>Unban</a>
                                    @endif
                                @endcan
                                @php
                                    $followSetting = Auth()->check() ? setting('privacy_follow', 'nobody', Auth()->id()) : 'nobody';
                                    $followRequests = Auth()->check() ? Auth()->user()->followers()->where('accepted', false)->pluck('follower_id')->toArray() : [];
                                    $followers = Auth()->check() ? Auth()->user()->followers()->where('accepted', true)->pluck('follower_id')->toArray() : [];
                                @endphp
                                @if(in_array($user->id, $followRequests))
                                    <a class="accept-follower-button dropdown-item text-white" href="{{ route('user_accept_follower', $user->id) }}" title="Accept Follower"><i class="fa fa-check nav-icon"></i>Accept Follower</a>
                                    <a class="reject-follower-button dropdown-item text-white" href="{{ route('user_reject_follower', $user->id) }}" title="Reject Follower"><i class="fa fa-times nav-icon"></i>Reject Follower</a>
                                @elseif(in_array($user->id, $followers))
                                    <a class="dropdown-item text-white" href="{{ route('user_remove_follower', $user->id) }}" title="Remove Follower"><i class="fa fa-ban nav-icon"></i>Remove Follower</a>
                                @endif
                                @php
                                    $followSetting = setting('privacy_follow', 'nobody', $user->id);
                                    $followers = $user->followers()->pluck('follower_id')->toArray();
                                @endphp
                                @if(in_array(Auth()->id(), $followers))
                                    <a class="dropdown-item text-white" href="{{ route('user_unfollow', $user->id) }}" title="Unfollow"><i class="fa fa-user-times nav-icon"></i>Unfollow</a>
                                @else
                                    @if($followSetting === 'anybody')
                                        <a class="dropdown-item text-white" href="{{ route('user_follow', $user->id) }}" title="Follow"><i class="fa fa-user-plus nav-icon"></i>Follow</a>
                                    @elseif($followSetting === 'request')
                                        <a class="dropdown-item text-white" href="{{ route('user_follow', $user->id) }}" title="Request to follow"><i class="fa fa-user-plus nav-icon"></i>Request To Follow</a>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
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
        @if(!empty($event) && !empty($tab) && $event->accept_method === 'accept' && $tab === 'applicants' && !empty($user->pivot->comment))
            <div class="row mt-2">
                <div class="col">
                    {{ $user->pivot->comment }}
                </div>
            </div>
        @endif
    </div>
</div>
