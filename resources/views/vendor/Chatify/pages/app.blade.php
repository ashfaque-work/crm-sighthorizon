@extends('layouts.admin')

@push('head')
@include('Chatify::layouts.headLinks')
@endpush

@php
$setting = App\Models\Utility::colorset();
$color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
$profile=\App\Models\Utility::get_file('/'.config('chatify.user_avatar.folder'));
$profiles=\App\Models\Utility::get_file('uploads/avatar/');
@endphp


@section('title')
    {{__('Messenger')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{__('Messenger')}}</li>
@endsection

@section('content')
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css'/>
<div class="row">
    <div class="col-xl-12">
        <div class="card mt-4">
            <div class="card-body">
                <div class="messenger min-h-750 overflow-hidden " style="border: 1px solid #eee; border-right: 0;">
                    {{-- ----------------------Users/Groups lists side---------------------- --}}
                    <div class="messenger-listView">
                        {{-- Header and search bar --}}
                        <div class="m-header">
                            <nav>
                                <nav class="m-header-right">
                                    <a href="#" class="listView-x"><i class="fas fa-times"></i></a>
                                </nav>
                            </nav>
                            {{-- Search input --}}
                            <input type="text" class="messenger-search" placeholder="{{__('Search')}}" />
                            {{-- Tabs --}}

                            <div class="messenger-listView-tabs"> <a href="#" @if($route
                            == 'user') class="active-tab" @endif data-view="users"> <span
                            class="far fa-clock"></span></a> <a href="#"
                            @if($route == 'group') class="active-tab" @endif
                            data-view="groups"> <span class="fas fa-users"></span>
                            </a> </div>
                        </div>
                        {{-- tabs and lists --}}
                        <div class="m-body">
                        {{-- Lists [Users/Group] --}}
                        {{-- ---------------- [ User Tab ] ---------------- --}}

                        <div class="@if($route == 'user') show @endif messenger-tab app-scroll" data-view="users">

                            {{-- Favorites --}}
                                <p class="messenger-title">Favorites</p>
                                <div class="messenger-favorites app-scroll-thin"></div>

                            {{-- Saved Messages --}}
                            {!! view('Chatify::layouts.listItem', ['get' => 'saved','id' => $id])->render() !!}

                            {{-- Contact --}}
                            <div class="listOfContacts" style="width: 100%;height: calc(100% - 200px);position: relative;"></div>

                        </div>

                        {{-- ---------------- [ Group Tab ] ---------------- --}}
                        <div class="all_members @if($route == 'group') show @endif messenger-tab app-scroll" data-view="groups">
                                    <p style="text-align: center;color:grey;">{{__('Soon will be available')}}</p>
                                </div>

                            {{-- ---------------- [ Search Tab ] ---------------- --}}
                        <div class="messenger-tab app-scroll" data-view="search">
                                {{-- items --}}
                                <p class="messenger-title">{{__('Search')}}</p>
                                <div class="search-records">
                                    <p class="message-hint center-el" style="background-color: gray;"><span >{{__('Type to search..')}}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ----------------------Messaging side---------------------- --}}
                    <div class="messenger-messagingView">
                        {{-- header title [conversation name] amd buttons --}}
                        <div class="m-header m-header-messaging">
                            <nav>
                                {{-- header back button, avatar and user name --}}
                                <div style="display: inline-flex;">
                                        <a href="#" class="show-listView"><i class="fas fa-arrow-left"></i> </a>
                                        @if(!empty(Auth::user()->avatar))
                                            <div class="avatar av-s header-avatar" style="margin: 0px 10px; margin-top: -5px; margin-bottom: -5px;background-image: url('{{ $profile.Auth::user()->avatar }}');"></div>
                                        @else
                                            <div class="avatar av-s header-avatar" style="margin: 0px 10px; margin-top: -5px; margin-bottom: -5px;background-image: url('{{ $profiles.'avatar.png' }}');"></div>
                                        @endif
                                        <a href="#" class="user-name">{{ config('chatify.name') }}</a>
                                    </div>
                                {{-- header buttons --}}
                                <nav class="m-header-right">
                                    <a href="#" class="add-to-favorite my-lg-1 my-xl-1 mx-lg-3 mx-xl-3"><i class="fas fa-star"></i></a>
                                    <a href="#" class="show-infoSide my-lg-1 my-xl-1 mx-lg-3 mx-xl-3"><i class="fas fa-info-circle"></i></a>
                                </nav>
                            </nav>
                        </div>
                        {{-- Internet connection --}}
                        <div class="internet-connection">
                            <span class="ic-connected">{{__('Connected')}}</span>
                            <span class="ic-connecting">{{__('Connecting...')}}</span>
                            {{-- <span class="ic-noInternet">{{__('Please add pusher settings for using messenger')}}</span> --}}
                        </div>
                        {{-- Messaging area --}}
                        <div class="m-body app-scroll">
                            <div class="messages">
                                <p class="message-hint" style="margin-top: calc(30% - 126.2px);"><span style="background-color: gray;">{{__('Please select a chat to start messaging')}}</span></p>
                            </div>
                            {{-- Typing indicator --}}
                            <div class="typing-indicator">
                                <div class="message-card typing">
                                    <p>
                                        <span class="typing-dots">
                                            <span class="dot dot-1"></span>
                                            <span class="dot dot-2"></span>
                                            <span class="dot dot-3"></span>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            {{-- Send Message Form --}}
                            @include('Chatify::layouts.sendForm')
                        </div>
                    </div>
                    {{-- ---------------------- Info side ---------------------- --}}
                    <div class="messenger-infoView app-scroll text-center">
                        {{-- nav actions --}}
                        <nav class="text-left">
                            <a href="#"><i class="fas fa-times"></i></a>
                        </nav>
                        {!! view('Chatify::layouts.info')->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    @include('Chatify::layouts.modals')
@endpush
@if($color == "theme-1")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #0CAF60 3.46%, #0CAF60 99.86%), #0CAF60 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #0CAF60 3.46%, #0CAF60 99.86%), #0CAF60 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #0CAF60 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #0CAF60 3.46%, #0CAF60 99.86%), #0CAF60 !important;
}
.m-header svg {
    color: #0CAF60 !important;
}
.active-tab {
    border-bottom: 2px solid #0CAF60 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #0CAF60 3.46%, #0CAF60 99.86%), #0CAF60 !important;
}
.lastMessageIndicator {
    color: #0CAF60 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #0CAF60 3.46%, #0CAF60 99.86%), #0CAF60 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #0CAF60 !important;
}
</style>
@endif
@if($color == "theme-2")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #584ED2 3.46%, #584ED2 99.86%), #584ED2 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #584ED2 3.46%, #584ED2 99.86%), #584ED2 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #584ED2 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #584ED2 3.46%, #584ED2 99.86%), #584ED2 !important;
}
.m-header svg {
    color: #584ED2 !important;
}
.active-tab {
    border-bottom: 2px solid #584ED2 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #584ED2 3.46%, #584ED2 99.86%), #584ED2 !important;
}
.lastMessageIndicator {
    color: #584ED2 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #584ED2 3.46%, #584ED2 99.86%), #584ED2 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #584ED2 !important;
}
</style>
@endif
@if($color == "theme-3")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #6FD943 3.46%, #6FD943 99.86%), #6FD943 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #6FD943 3.46%, #6FD943 99.86%), #6FD943 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #6FD943 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #6FD943 3.46%, #6FD943 99.86%), #6FD943 !important;
}
.m-header svg {
    color: #6FD943 !important;
}
.active-tab {
    border-bottom: 2px solid #6FD943 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #6FD943 3.46%, #6FD943 99.86%), #6FD943 !important;
}
.lastMessageIndicator {
    color: #6FD943 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #6FD943 3.46%, #6FD943 99.86%), #6FD943 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #6FD943 !important;
}
</style>
@endif
@if($color == "theme-4")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #145388 3.46%, #145388 99.86%), #145388 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #145388 3.46%, #145388 99.86%), #145388 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #145388 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #145388 3.46%, #145388 99.86%), #145388 !important;
}
.m-header svg {
    color: #145388 !important;
}
.active-tab {
    border-bottom: 2px solid #145388 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #145388 3.46%, #145388 99.86%), #145388 !important;
}
.lastMessageIndicator {
    color: #145388 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #145388 3.46%, #145388 99.86%), #145388 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #145388 !important;
}
</style>
@endif
@if($color == "theme-5")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #B9406B 3.46%, #B9406B 99.86%), #B9406B !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #B9406B 3.46%, #B9406B 99.86%), #B9406B !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #B9406B !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #B9406B 3.46%, #B9406B 99.86%), #B9406B !important;
}
.m-header svg {
    color: #B9406B !important;
}
.active-tab {
    border-bottom: 2px solid #B9406B !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #B9406B 3.46%, #B9406B 99.86%), #B9406B !important;
}
.lastMessageIndicator {
    color: #B9406B !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #B9406B 3.46%, #B9406B 99.86%), #B9406B !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #B9406B !important;
}
</style>
@endif
@if($color == "theme-6")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #008ECC 3.46%, #008ECC 99.86%), #008ECC !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #008ECC 3.46%, #008ECC 99.86%), #008ECC !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #008ECC !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #008ECC 3.46%, #008ECC 99.86%), #008ECC !important;
}
.m-header svg {
    color: #008ECC !important;
}
.active-tab {
    border-bottom: 2px solid #008ECC !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #008ECC 3.46%, #008ECC 99.86%), #008ECC !important;
}
.lastMessageIndicator {
    color: #008ECC !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #008ECC 3.46%, #008ECC 99.86%), #008ECC !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #008ECC !important;
}
</style>
@endif
@if($color == "theme-7")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #922C88 3.46%, #922C88 99.86%), #922C88 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #922C88 3.46%, #922C88 99.86%), #922C88 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #922C88 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #922C88 3.46%, #922C88 99.86%), #922C88 !important;
}
.m-header svg {
    color: #922C88 !important;
}
.active-tab {
    border-bottom: 2px solid #922C88 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #922C88 3.46%, #922C88 99.86%), #922C88 !important;
}
.lastMessageIndicator {
    color: #922C88 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #922C88 3.46%, #922C88 99.86%), #922C88 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #922C88 !important;
}
</style>
@endif
@if($color == "theme-8")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #C0A145 3.46%, #C0A145 99.86%), #C0A145 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #C0A145 3.46%, #C0A145 99.86%), #C0A145 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #C0A145 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #C0A145 3.46%, #C0A145 99.86%), #C0A145 !important;
}
.m-header svg {
    color: #C0A145 !important;
}
.active-tab {
    border-bottom: 2px solid #C0A145 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #C0A145 3.46%, #C0A145 99.86%), #C0A145 !important;
}
.lastMessageIndicator {
    color: #C0A145 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #C0A145 3.46%, #C0A145 99.86%), #C0A145 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #C0A145 !important;
}
</style>
@endif
@if($color == "theme-9")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #48494B !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
}
.m-header svg {
    color: #48494B !important;
}
.active-tab {
    border-bottom: 2px solid #48494B !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
}
.lastMessageIndicator {
    color: #48494B !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #48494B 3.46%, #48494B 99.86%), #48494B !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #48494B !important;
}
</style>
@endif
@if($color == "theme-10")
<style type="text/css">
    .m-list-active, .m-list-active:hover, .m-list-active:focus {
    background: linear-gradient(141.55deg, #0C7785 3.46%, #0C7785 99.86%), #0C7785 !important;
}
.mc-sender p {
    background: linear-gradient(141.55deg, #0C7785 3.46%, #0C7785 99.86%), #0C7785 !important;
}
.messenger-favorites div.avatar {
    box-shadow: 0px 0px 0px 2px #0C7785 !important;
}
.messenger-listView-tabs a, .messenger-listView-tabs a:hover, .messenger-listView-tabs a:focus {
    color: linear-gradient(141.55deg, #0C7785 3.46%, #0C7785 99.86%), #0C7785 !important;
}
.m-header svg {
    color: #0C7785 !important;
}
.active-tab {
    border-bottom: 2px solid #0C7785 !important;
}
.messenger-infoView nav a {
    color: linear-gradient(141.55deg, #0C7785 3.46%, #0C7785 99.86%), #0C7785 !important;
}
.lastMessageIndicator {
    color: #0C7785 !important;
}
.messenger-list-item td span .lastMessageIndicator {
    color: linear-gradient(141.55deg, #0C7785 3.46%, #0C7785 99.86%), #0C7785 !important;
    font-weight: bold;
}
.messenger-sendCard button svg {
     color: #0C7785 !important;
}
</style>
@endif
