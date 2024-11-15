{{-- user info and avatar --}}
@php
$profile=\App\Models\Utility::get_file('/'.config('chatify.user_avatar.folder'));
$profiles=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@if(!empty(\Auth::user()->avatar))
    <div class="avatar av-l" style="background-image: url('{{ $profile.'/'.Auth::user()->avatar }}');">
    </div>
@else
    <div class="avatar av-l"
         style="background-image: url('{{ $profiles.'/avatar.png' }}');">
    </div>
@endif
<p class="info-name">{{ config('chatify.name') }}</p>
<div class="messenger-infoView-btns">
    {{-- <a href="#" class="default"><i class="fas fa-camera"></i> default</a> --}}
    <a href="#" class="danger delete-conversation"><i class="fas fa-trash-alt"></i> Delete Conversation</a>
</div>
{{-- shared photos --}}
<div class="messenger-infoView-shared">
    <p class="messenger-title">shared photos</p>
    <div class="shared-photos-list"></div>
</div>
