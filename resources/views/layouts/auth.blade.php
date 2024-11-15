@php
    $SITE_RTL = Utility::getValByName('SITE_RTL');
    $setting = App\Models\Utility::colorset();
    $color = 'theme-3';
    if (!empty($setting['color'])) {
        $color = $setting['color'];
    }
    // $logo=asset(Storage::url('logo/'));

    // $company_logo=Utility::get_superadmin_logo();
    $logo = \App\Models\Utility::get_file('logo/');
    $settings = App\Models\Utility::settings();
    if ($settings['cust_darklayout'] == 'on') {
        $company_logo = 'logo-light.png';
    } else {
        $company_logo = 'logo-dark.png';
    }
    $lang = \App::getLocale('lang');
    if ($lang == 'ar' || $lang == 'he') {
        $SITE_RTL = 'on';
    }
    $LangName = \App\Models\Languages::where('code', $lang)->first();
    if (empty($LangName)) {
        $LangName = new App\Models\Utility();
        $LangName->fullName = 'English';
    }
    $footer_text = isset($setting['footer_text']) ? $setting['footer_text'] : '';
    $mode_setting = \App\Models\Utility::mode_layout();
    $dark_logo=Utility::getValByName('dark_logo');
    $light_logo=Utility::getValByName('light_logo');
@endphp
<!DOCTYPE html>
<html lang="en" dir="{{ $SITE_RTL == 'on' ? 'rtl' : '' }}">

<head>

    <title>
        {{ Utility::getValByName('header_text') ? Utility::getValByName('header_text') : config('app.name', 'Sight Horizon') }}
        &dash; @yield('title')</title>
    <!-- HTML5 Shim and Respond.js IE11 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 11]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="keywords" content="Dashboard Template" />
    <meta name="author" content="Rajodiya Infotech" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="title"
        content="{{ isset($settings['meta_keywords']) && $settings['meta_keywords'] ? $settings['meta_keywords'] : '' }}">
    <meta name="description"
        content="{{ isset($settings['meta_description']) && $settings['meta_description'] ? $settings['meta_description'] : '' }}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title"
        content="{{ isset($settings['meta_keywords']) && $settings['meta_keywords'] ? $settings['meta_keywords'] : '' }}">
    <meta property="og:description"
        content="{{ isset($settings['meta_description']) && $settings['meta_description'] ? $settings['meta_description'] : '' }}">
    <meta property="og:image" content="{{ $logo . 'meta_image.png' }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title"
        content="{{ isset($settings['meta_keywords']) && $settings['meta_keywords'] ? $settings['meta_keywords'] : '' }}">
    <meta property="twitter:description"
        content= "{{ isset($settings['meta_description']) && $settings['meta_description'] ? $settings['meta_description'] : '' }}">
    <meta property="twitter:image" content="{{ $logo . 'meta_image.png' }}">

    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset(Storage::url('logo/favicon.png')) }}" type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/stylesheet.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">


    <!-- vendor css -->
    {{-- <link rel="stylesheet" href="{{asset('assets/css/style.css')}}" id="main-style-link"> --}}

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    @if ($settings['cust_darklayout'] == 'on')
    <style>
        .g-recaptcha {
            filter: invert(1) hue-rotate(180deg) !important;
        }
    </style>
    @endif
    @if (isset($cust_darklayout) && $cust_darklayout == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">
    @else
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @endif
    @if ($settings['cust_darklayout'] == 'on')
    <style>
        .g-recaptcha {
            filter: invert(1) hue-rotate(180deg) !important;
        }
    </style>
    @endif

    @if($settings['cust_darklayout']=='on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}">

    @if(isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
    <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="main-style-link">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css')}}">
    @else
    @if(isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="main-style-link">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style.css')}}" id="main-style-link">
    @endif
    @endif

    @if(isset($SITE_RTL) && $SITE_RTL == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth-rtl.css')}}" id="main-style-link">
        @else
        <link rel="stylesheet" href="{{ asset('assets/css/custom-auth.css')}}" id="main-style-link">
    @endif
    @if($settings['cust_darklayout']=='on')
        <link rel="stylesheet" href="{{ asset('assets/css/custom-dark.css')}}" id="main-style-link">

    @endif
</head>

<body class="{{ $color }}">
    <!-- [ auth-signup ] start -->
    <div class="custom-login">
        <div class="login-bg-img">
            <img src="{{ asset('assets/images/auth/'.$color.'.svg') }}" class="login-bg-1">
            <img src="{{ asset('assets/images/user2.svg') }}" class="login-bg-2">
        </div>
        <div class="bg-login bg-primary"></div>
        <div class="custom-login-inner">
            <header class="dash-header">
                <nav class="navbar navbar-expand-md default">
                    <div class="container">
                        <div class="navbar-brand">
                            <a href="#">
                                @if (!empty($settings['cust_darklayout']) && $settings['cust_darklayout'] == 'on')
                                <img src="{{ $logo.'/'.  $light_logo . '?' . time() }}" style="max-height: 50px; max-width: 152px;"
                                    alt="{{ config('app.name', 'Sight Horizon') }}" class="logo logo-lg">
                            @else

                                <img src="{{ $logo.'/'.  $dark_logo . '?' . time() }}" style="max-height: 50px; max-width: 152px;"
                                    alt="{{ config('app.name', 'Sight Horizon') }}" class="logo logo-lg">
                            @endif
                            </a>
                        </div>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarlogin">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarlogin">
                            <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                                @include('landingpage::layouts.buttons')
                                @yield('language-bar')
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <main class="custom-wrapper">
                <div class="custom-row">
                    <div class="card">
                        @yield('content')
                    </div>
                </div>
            </main>
            <footer>
                <div class="auth-footer">
                    <div class="container">
                        <div class="row">
                            <div class="col-12">
                                <span>&copy; {{ date('Y') }}
                                    {{ App\Models\Utility::getValByName('footer_text') ? App\Models\Utility::getValByName('footer_text') : config('app.name', 'Storego Saas') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- [ auth-signup ] end -->

    <!-- Required Js -->
    <script src="{{ asset('custom/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script>
        feather.replace();
    </script>

    <input type="checkbox" class="d-none" id="cust-theme-bg"
        {{ Utility::getValByName('cust_theme_bg') == 'on' ? 'checked' : '' }} />
    <input type="checkbox" class="d-none" id="cust-darklayout"
        {{ Utility::getValByName('cust_darklayout') == 'on' ? 'checked' : '' }} />
    <script>
        // $(document).ready(function() {
        //       cust_darklayout();
        //   });
        feather.replace();
        var pctoggle = document.querySelector("#pct-toggler");
        if (pctoggle) {
            pctoggle.addEventListener("click", function() {
                if (
                    !document.querySelector(".pct-customizer").classList.contains("active")
                ) {
                    document.querySelector(".pct-customizer").classList.add("active");
                } else {
                    document.querySelector(".pct-customizer").classList.remove("active");
                }
            });
        }

        var themescolors = document.querySelectorAll(".themes-color > a");
        for (var h = 0; h < themescolors.length; h++) {
            var c = themescolors[h];

            c.addEventListener("click", function(event) {
                var targetElement = event.target;
                if (targetElement.tagName == "SPAN") {
                    targetElement = targetElement.parentNode;
                }
                var temp = targetElement.getAttribute("data-value");
                removeClassByPrefix(document.querySelector("body"), "theme-");
                document.querySelector("body").classList.add(temp);
            });
        }

        var custthemebg = document.querySelector("#cust-theme-bg");
        custthemebg.addEventListener("click", function() {
            if (custthemebg.checked) {
                document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.add("transprent-bg");
            } else {
                document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.remove("transprent-bg");
            }
        });

        function cust_darklayout() {
            var custdarklayout = document.querySelector("#cust-darklayout");
            // custdarklayout.addEventListener("click", function() {

            if (custdarklayout.checked) {
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ $logo . '/' . 'logo-light.png' }}");
                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
            } else {
                document
                    .querySelector(".m-header > .b-brand > .logo-lg")
                    .setAttribute("src", "{{ $logo . '/' . 'logo-dark.png' }}");
                document
                    .querySelector("#main-style-link")
                    .setAttribute("href", "{{ asset('assets/css/style.css') }}");
            }


        }

        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>
    <script src="{{ asset('custom/js/jquery.min.js') }}"></script>
    <script>
        var toster_pos = "{{ $SITE_RTL == 'on' ? 'left' : 'right' }}";
    </script>
    <script src="{{ asset('custom/js/custom.js') }}"></script>
    @stack('script')
    @if ($settings['enable_cookie'] == 'on')
        @include('layouts.cookie_consent')
    @endif
    @stack('custom-scripts')


</body>

</html>
