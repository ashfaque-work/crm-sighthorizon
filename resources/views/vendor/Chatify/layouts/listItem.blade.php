
@php
    $setting = App\Models\Utility::colorset();
    $color = isset($setting['color']) ? $setting['color'] : 'theme-3';

    $settings = App\Models\Utility::settings();
    $company_favicon=Utility::getValByName('company_favicon');
    $SITE_RTL = App\Models\Utility::getValByName('SITE_RTL');
    if($SITE_RTL == ''){
        $SITE_RTL == 'off';
        }
    $profile=\App\Models\Utility::get_file('/'.config('chatify.user_avatar.folder'));
    $profiles=\App\Models\Utility::get_file('uploads/avatar/');
@endphp

{{-- -------------------- Saved Messages -------------------- --}}
@if($get == 'saved')
    <table class="messenger-list-item m-li-divider @if('user_'.Auth::user()->id == $id && $id != "0") m-list-active @endif">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td>
                @if($color == "theme-1")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #0CAF60; margin-top: 12px !important;"></span>
                </div>
                @endif
                 @if($color == "theme-2")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #584ED2; margin-top: 12px !important;"></span>
                </div>
                @endif
                 @if($color == "theme-3")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #6FD943; margin-top: 12px !important;"></span>
                </div>
                @endif
                 @if($color == "theme-4")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #145388; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-5")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #B9406B; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-6")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #008ECC; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-7")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #922C88; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-8")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #C0A145; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-9")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #48494B; margin-top: 12px !important;"></span>
                </div>
                @endif
                @if($color == "theme-")
                <div class="avatar av-m" style="background-color: #D9EFFF; text-align: center;">
                    <span class="far fa-bookmark" style="font-size: 22px; color: #0C7785; margin-top: 12px !important;"></span>
                </div>
                @endif
            </td>
            {{-- center side --}}
            <td>
                <p data-id="{{ 'user_'.Auth::user()->id }}">Saved Messages <span>You</span></p>
                <span>Save messages secretly</span>
            </td>
        </tr>
    </table>
@endif

{{-- -------------------- All users/group list -------------------- --}}
@if (($get == 'users') && ($user) && ($lastMessage))
    <table class="messenger-list-item @if($user->id == $id && $id != "0") m-list-active @endif" data-contact="{{ $user->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td style="position: relative">
                @if($user->active_status)
                    <span class="activeStatus"></span>
                @endif
                @if(!empty($user->avatar))
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profile.'/'.$user->avatar }}');">
                    </div>
                @else
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profiles.'/avatar.png' }}');">
                    </div>
                @endif
            </td>
            {{-- center side --}}
            @if(!empty($lastMessage))
            <td style="width: 100%;">
                <p data-id="{{ $type.'_'.$user->id }}" style="text-align: start;">
                    {{ strlen($user->name) > 12 ? trim(substr($user->name,0,12)).'..' : $user->name }}
                    <span>{{ !empty($lastMessage)?$lastMessage->created_at->diffForHumans():'' }}</span></p>
                    <span style="justify-content: left;display: flex;">
            {{-- Last Message user indicator --}}
                    {!!
                        $lastMessage->from_id == Auth::user()->id
                        ? '<span class="lastMessageIndicator">'.__('You :').'</span>'
                        : ''
                    !!}
                    {{-- Last message body --}}
                    @if($lastMessage->attachment == null)
                        {{
                            strlen($lastMessage->body) > 30
                            ? trim(substr($lastMessage->body, 0, 30)).'..'
                            : $lastMessage->body
                        }}
                    @else
                        <span class="fas fa-file"></span> {{__('Attachment')}}
                    @endif
        </span>
                {{-- New messages counter --}}
                {!! $unseenCounter > 0 ? "<b>".$unseenCounter."</b>" : '' !!}
            </td>
            @else
            <td></td>
            @endif
        </tr>
    </table>
@endif

{{-- -------------------- Search Item -------------------- --}}
@if($get == 'search_item')
    <table class="messenger-list-item" data-contact="{{ $user->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td style="position: relative">
                @if($user->active_status)
                    <span class="activeStatus"></span>
                @endif
                @if(!empty($user->avatar))
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profile.$user->avatar }}');">
                    </div>
                @else
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profiles.'/avatar.png'}}');">
                    </div>
                @endif
            </td>
            {{-- center side --}}
            <td style="width: 100%;">
                <p data-id="{{ $type.'_'.$user->id }}" style="text-align: start;">
                {{ strlen($user->name) > 12 ? trim(substr($user->name,0,12)).'..' : $user->name }}
            </td>

        </tr>
    </table>
@endif

{{-- -------------------- Get All Members -------------------- --}}

@if($get == 'all_members')
    <table class="messenger-list-item" data-contact="{{ $user->id }}">
        <tr data-action="0">
            {{-- Avatar side --}}
            <td style="position: relative">
                @if($user->active_status)
                    <span class="activeStatus"></span>
                @endif
                @if(!empty($user->avatar))
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profile.'/'.$user->avatar }}');">
                    </div>
                @else
                    <div class="avatar av-m"
                         style="background-image: url('{{ $profiles.'/avatar.png' }}');">
                    </div>
                @endif
            </td>
            {{-- center side --}}
            <td>
                <p data-id="{{ $type.'_'.$user->id }}">
                {{ strlen($user->name) > 12 ? trim(substr($user->name,0,12)).'..' : $user->name }}
            </td>

        </tr>
    </table>
@endif

{{-- -------------------- Shared photos Item -------------------- --}}
@if($get == 'sharedPhoto')
    <div class="shared-photo chat-image" style="background-image: url('{{ $image }}')"></div>
@endif
