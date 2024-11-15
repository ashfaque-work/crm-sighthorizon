@extends('layouts.auth')

@section('title')
    {{ __('Forgot Password') }}
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
                <h2 class="mb-3 f-w-600">{{__('Forgot Password')}}</h2>
            </div>
             @if(session('status'))
                <div class="alert alert-primary">
                    {{ session('status') }}
                </div>
            @endif
            <p class="mb-4 text-muted">{{__('We will send a link to reset your password')}}</p>
            <form method="POST" action="{{ route('password.email') }}">
            @csrf
                <div class="">

                    <div class="form-group mb-3">
                        <label class="form-label d-flex">{{__('Email address')}}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                        @enderror
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
                        <button type="submit" class="btn btn-primary btn-block mt-2" id="login_button">Send Password Reset Link</button>
                    </div>


                    <div class="my-4 text-xs text-muted text-center">
                        <p>{{__("Already have an account?")}} <a href="{{route('login',$lang)}}">{{__('Login')}}</a></p>
                    </div>

                </div>
            </form>
        </div>

@endsection
@push('custom-scripts')
@if(Utility::getValByName('recaptcha_module')=='yes')
        {!! NoCaptcha::renderJs() !!}
@endif
@endpush
