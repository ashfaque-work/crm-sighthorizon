@extends('layouts.auth')

@section('title')
    {{ __('Login') }}
@endsection

@section('language-bar')
@php
    $languages = App\Models\Utility::languages();
    $settings =   App\Models\Utility::settings();
    config(
        [
            'captcha.secret' => $settings['google_recaptcha_secret'],
            'captcha.sitekey' => $settings['google_recaptcha_key'],
            'options' => [
                'timeout' => 30,
            ],
        ]
    );
@endphp
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ ucFirst($languages[$lang]) }}
                </span>

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
            <div class="">
                <h2 class="mb-3 f-w-600">{{ __('Login') }}
                </h2>

            </div>
            <form method="POST" action="{{ route('login') }}" id="form_data" class="needs-validation" novalidate="">
            @csrf
                <div class="">
                    <div class="form-group mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-label">{{__('Email')}}</label>
                            </div>
                        </div>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <label class="form-label">{{__('Password')}}</label>
                                </div>

                        </div>

                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                         @error('password')
                        <span class="invalid-feedback" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <div class="text-left">

                            <span><a href="{{ route('password.request', $lang) }}"
                                    tabindex="0">{{ __('Forgot your password?') }}</a></span>
                        </div>
                    </div>

                    @if(Utility::getValByName('recaptcha_module')=='yes')
                        <div class="form-group mb-3">
                            {!! NoCaptcha::display() !!}
                            @error('g-recaptcha-response')
                            <span class="small text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    @endif


                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block mt-2" id="login_button">{{__('Login')}}</button>
                    </div>

                </div>
            </form>
        </div>



@endsection
@push('custom-scripts')
<!-- <script src="{{asset('custom/libs/jquery/dist/jquery.min.js')}}"></script> -->
<script>
$(document).ready(function () {
  $("#form_data").submit(function (e) {
      $("#login_button").attr("disabled", true);
      return true;
  });
});
</script>
@if(Utility::getValByName('recaptcha_module')=='yes')
        {!! NoCaptcha::renderJs() !!}
@endif
@endpush


