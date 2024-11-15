@extends('layouts.auth')
@section('language-bar')
@php
    $languages = App\Models\Utility::languages();

@endphp
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ ucFirst($languages[$lang]) }}
                </span>
                <img src="{{ asset('assets/images/down-arrow.svg') }}">
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach($languages as $code => $language)
                    <a href="{{ route('login',$code) }}" tabindex="0" class="dropdown-item {{ $code == $lang ? 'active':'' }}">
                        <span>{{ ucFirst($language)}}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@section('content')
<div class="card-body">

    @if (session('status') == 'verification-link-sent')
    <div class="alert alert-success" role="alert">
      {{ __('A fresh verification link has been sent to your email address.') }}
    </div>
    @endif
  <h4 class="text-primary font-weight-normal mb-1"><strong>{{__('Verify Your Email Address')}}</strong></h4>
  <span>{{ __('Before proceeding, please check your email for a verification link.') }}</span>
  <span>{{ __('If you did not receive the email') }},</span>
  <form action="{{ route('verification.resend') }}" method="POST" class="pt-3 text-left needs-validation" novalidate="">
    @csrf
    <button type="submit" class="btn-primary px-4 py-2 text-xs"><span class="d-block py-1">{{ __('click here to request another') }}</span></button>
  </form>
  <form method="POST" action="{{ route('logout') }}">
      @csrf

      <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
          {{ __('Log Out') }}
      </button>
  </form>

</div>
@endsection
