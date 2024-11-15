@extends('layouts.admin')
@php
    $setting = App\Models\Utility::colorset();
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';

    $logo = asset(Storage::url('logo/'));
    $dark_logo = Utility::getValByName('dark_logo');
    $light_logo = Utility::getValByName('light_logo');
    $company_favicon = Utility::getValByName('company_favicon');
    // $layout_setting = App\Models\Utility::settings();
    $file_type = config('files_types');
    $setting = App\Models\Utility::settings();

    $local_storage_validation = $setting['local_storage_validation'];
    $local_storage_validations = explode(',', $local_storage_validation);

    $s3_storage_validation = $setting['s3_storage_validation'];
    $s3_storage_validations = explode(',', $s3_storage_validation);

    $wasabi_storage_validation = $setting['wasabi_storage_validation'];
    $wasabi_storage_validations = explode(',', $wasabi_storage_validation);

    $SITE_RTL = App\Models\Utility::getValByName('SITE_RTL');
    if ($SITE_RTL == '') {
        $SITE_RTL == 'off';
    }
    $cust_darklayout = 'off';
    if (!empty($setting['cust_darklayout'])) {
        $cust_darklayout = $setting['cust_darklayout'];
    }

@endphp
@section('title')
    {{ __('Settings') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Settings') }}</li>
@endsection

@push('script')
    <script src="{{ asset('assets/js/jscolor.js') }} "></script>
    <script>
        $(document).on("change", "select[name='invoice_template'], input[name='invoice_color']", function() {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('#invoice_frame').attr('src', '{{ url('/invoices/preview') }}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='estimation_template'], input[name='estimation_color']", function() {
            var template = $("select[name='estimation_template']").val();
            var color = $("input[name='estimation_color']:checked").val();
            $('#estimation_frame').attr('src', '{{ url('/estimations/preview') }}/' + template + '/' + color);
        });

        $(document).on("change", "select[name='mdf_template'], input[name='mdf_color']", function() {
            var template = $("select[name='mdf_template']").val();
            var color = $("input[name='mdf_color']:checked").val();
            $('#mdf_frame').attr('src', '{{ url('/mdf/preview') }}/' + template + '/' + color);
        });
    </script>
    <script>
        function check_theme(color_val) {
            $('input[value="' + color_val + '"]').prop('checked', true);
            $('input[value="' + color_val + '"]').attr('checked', true);
            $('a[data-value]').removeClass('active_color');
            $('a[data-value="' + color_val + '"]').addClass('active_color');
        }
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300,

        })
        $(".list-group-item").click(function() {
            $('.list-group-item').filter(function() {
                return this.href == id;
            }).parent().removeClass('text-primary');
        });
    </script>

    <script>

        $(document).on('change', '[name=storage_setting]', function() {
            if ($(this).val() == 's3') {
                $('.s3-setting').removeClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').addClass('d-none');
            } else if ($(this).val() == 'wasabi') {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').removeClass('d-none');
                $('.local-setting').addClass('d-none');
            } else {
                $('.s3-setting').addClass('d-none');
                $('.wasabi-setting').addClass('d-none');
                $('.local-setting').removeClass('d-none');
            }
        });
    </script>

    <script type="text/javascript">
        @can('On-Off Email Template')
            $(document).on("click", ".email-template-checkbox", function() {
                var chbox = $(this);
                $.ajax({
                    url: chbox.attr('data-url'),
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: chbox.val()
                    },
                    type: 'PUT',
                    success: function(response) {
                        if (response.is_success) {
                            show_toastr('Success', response.success, 'success');
                            if (chbox.val() == 1) {
                                $('#' + chbox.attr('id')).val(0);
                            } else {
                                $('#' + chbox.attr('id')).val(1);
                            }
                        } else {
                            show_toastr('Error', response.error, 'error');
                        }
                    },
                    error: function(response) {
                        response = response.responseJSON;
                        if (response.is_success) {
                            show_toastr('Error', response.error, 'error');
                        } else {
                            show_toastr('Error', response, 'error');
                        }
                    }
                })
            });
        @endcan
    </script>


    <script>
        $(document).on("click", '.send_email', function(e) {
            e.preventDefault();
            var title = $(this).attr('data-title');

            var size = 'md';
            var url = $(this).attr('data-url');
            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');

                $.post(url, {
                    _token: '{{ csrf_token() }}',
                    mail_driver: $("#mail_driver").val(),
                    mail_host: $("#mail_host").val(),
                    mail_port: $("#mail_port").val(),
                    mail_username: $("#mail_username").val(),
                    mail_password: $("#mail_password").val(),
                    mail_encryption: $("#mail_encryption").val(),
                    mail_from_address: $("#mail_from_address").val(),
                    mail_from_name: $("#mail_from_name").val(),

                }, function(data) {
                    $('#commonModal .body').html(data);
                });
            }
        });


        $(document).on('submit', '#test_email', function(e) {
            e.preventDefault();
            $("#email_sending").show();
            var post = $(this).serialize();
            var url = $(this).attr('action');
            $.ajax({
                type: "post",
                url: url,
                data: post,
                cache: false,
                beforeSend: function() {
                    $('#test_email .btn-create').attr('disabled', 'disabled');
                },
                success: function(data) {
                    if (data.is_success) {
                        show_toastr('Success', data.message, 'success');
                    } else {
                        show_toastr('Error', data.message, 'error');
                    }
                    $("#email_sending").hide();
                    $('#commonModal').modal('hide');
                },
                complete: function() {
                    $('#test_email .btn-create').removeAttr('disabled');
                },
            });
        });
    </script>
    <script type="text/javascript">
        function enablecookie() {
            const element = $('#enable_cookie').is(':checked');
            $('.cookieDiv').addClass('disabledCookie');
            if (element == true) {
                $('.cookieDiv').removeClass('disabledCookie');
                $("#cookie_logging").attr('checked', true);
            } else {
                $('.cookieDiv').addClass('disabledCookie');
                $("#cookie_logging").attr('checked', false);
            }
        }
    </script>
    <script>
        if ($('#cust-darklayout').length > 0) {
            var custthemedark = document.querySelector("#cust-darklayout");
            custthemedark.addEventListener("click", function() {
                if (custthemedark.checked) {
                    $('#style').attr('href', '{{ env('APP_URL') }}' + '/public/assets/css/style-dark.css');

                    $('.dash-sidebar .main-logo a img').attr('src', '{{ $logo . $light_logo }}');

                } else {
                    $('#style').attr('href', '{{ env('APP_URL') }}' + '/public/assets/css/style.css');
                    $('.dash-sidebar .main-logo a img').attr('src', '{{ $logo . $dark_logo }}');

                }
            });
        }
        if ($('#cust-theme-bg').length > 0) {
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
        }
    </script>
@endpush
@php
    $lang = isset($users->lang) ? $users->lang : 'en';
    if ($lang == null) {
        $lang = 'en';
    }
@endphp
@section('content')
    <div class="row ">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#brand-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Brand Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#email-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#pusher-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Pusher Settings') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#system-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('System Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            @can('Manage Company Settings')
                                <a href="#company-setting"
                                    class="list-group-item list-group-item-action border-0">{{ __('Company Settings') }}<div
                                        class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            @endcan

                            <a href="#company-payment-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Payment Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#invoice-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Invoice Print Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>

                            <a href="#estimation-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Estimation Print Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>

                            <a href="#mdf-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('MDF Print Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#zoom-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Zoom Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#slack-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Slack Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#telegram-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Telegram Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#email-notification-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Email Notification Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>

                            <a href="#recaptcha-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('ReCaptcha Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#storage-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Storage Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#google-calender"
                                class="list-group-item list-group-item-action border-0">{{ __('Google Calendar Settings') }}
                                <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                            </a>
                            <a href="#seo-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('SEO Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#cache-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Cache Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#webhook-setting"
                                class="list-group-item list-group-item-action border-0">{{ __('Webhook Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#cookie-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('Cookie Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                            <a href="#pills-chatgpt-settings"
                                class="list-group-item list-group-item-action border-0">{{ __('ChatGPT Settings') }}<div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="" id="brand-setting">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('Brand Settings') }}</h5>
                                <small class="text-dark font-weight-bold">{{ __('Edit your Brand Settings') }}</small>
                            </div>
                            <div class="card-body">
                                {{ Form::model($settings, ['route' => 'business.setting', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card site-card" style="height:321px">
                                            <div class="card-header">
                                                <h5>{{ __('Dark Logo') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content mt-4 setting-logo">
                                                        <a href="{{ asset(Storage::url('logo/logo-dark.png' . '?' . time())) }}"
                                                            target="_blank">
                                                            <img id="blah" alt="your image"
                                                                src="{{ $logo . '/' . (isset($dark_logo) && !empty($dark_logo) ? $dark_logo : 'logo-dark.png' . '?' . time()) }}"
                                                                class="small-logo" style="width: 126px !important;" />
                                                        </a>
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="dark_logo">
                                                            <div class=" bg-primary"> <i
                                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" class="form-control file"
                                                                name="dark_logo" id="dark_logo"
                                                                data-filename="dark_logo_update"
                                                                onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                                        </label>
                                                    </div>
                                                    @error('company_logo')
                                                        <span class="invalid-dark_logo text-xs text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                    {{-- <p class="lh-160 mb-0 text-sm pt-0">
                                                        {{ __('These Logo will appear on Estimations and Invoice as well.') }}
                                                    </p> --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card site-card" style="height:321px">
                                            <div class="card-header">
                                                <h5>{{ __('Light Logo') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content mt-4">
                                                        <a href="{{ asset(Storage::url('logo/logo-light.png' . '?' . time())) }}"
                                                            target="_blank">
                                                            <img id="blah1"
                                                                src="{{ $logo . '/' . (isset($light_logo) && !empty($light_logo) ? $light_logo : 'logo-light.png' . '?' . time()) }}"
                                                                class="small-logo img_setting"
                                                                style="width: 126px !important;" alt="your image" />
                                                        </a>
                                                    </div>
                                                    <div class="choose-files mt-5">
                                                        <label for="light_logo">
                                                            <div class=" bg-primary"> <i
                                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" class="form-control file"
                                                                name="light_logo" id="light_logo"
                                                                data-filename="light_logo_update"
                                                                onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])">
                                                        </label>
                                                    </div>
                                                    @error('company_logo')
                                                        <span class="invalid-light_logo text-xs text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 col-md-6">
                                        <div class="card site-card" style="height:321px">
                                            <div class="card-header">
                                                <h5>{{ __('Favicon') }}</h5>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="setting-card">
                                                    <div class="logo-content mt-4">
                                                        <a href="{{ asset(Storage::url('logo/favicon.png' . '?' . time())) }}"
                                                            target="_blank">
                                                            <img id="blah2"
                                                                src="{{ $logo . '/' . (isset($company_favicon) && !empty($company_favicon) ? $company_favicon : 'favicon.png' . '?' . time()) }}"
                                                                class="small-logo img_setting"
                                                                style="width: 65px !important;" alt="" />
                                                        </a>
                                                    </div>
                                                    <div class="choose-files mt-4">
                                                        <label for="favicon">
                                                            <div class=" bg-primary"> <i
                                                                    class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                            </div>
                                                            <input type="file" class="form-control file"
                                                                name="favicon" id="favicon"
                                                                data-filename="favicon_update"
                                                                onchange="document.getElementById('blah2').src = window.URL.createObjectURL(this.files[0])">
                                                        </label>
                                                    </div>
                                                    @error('favicon')
                                                        <span class="invalid-favicon text-xs text-danger"
                                                            role="alert">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::label('header_text', __('Title Text'), ['class' => 'col-form-label text-dark']) }}
                                            {{ Form::text('header_text', Utility::getValByName('header_text'), ['class' => 'form-control', 'placeholder' => __('Enter Header Title Text')]) }}
                                            @error('header_text')
                                                <span class="invalid-header_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{ Form::label('footer_text', __('Footer Text'), ['class' => 'col-form-label text-dark']) }}
                                            {{ Form::text('footer_text', Utility::getValByName('footer_text'), ['class' => 'form-control', 'placeholder' => __('Enter Footer Text')]) }}
                                            @error('footer_text')
                                                <span class="invalid-footer_text" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            {{ Form::label('default_language', __('Default Language'), ['class' => 'col-form-label text-dark']) }}
                                            <select name="default_language" id="default_language"
                                                class="form-control select2">
                                                @foreach (App\Models\Utility::languages() as $code => $lang)
                                                <option @if (App\Models\Utility::getValByName('default_language') == $code) selected @endif
                                                    value="{{ $code }}">{{ Str::upper($lang) }}</option>
                                            @endforeach
                                            </select>
                                            @error('default_language')
                                                <span class="invalid-default_language" role="alert">
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-3 my-auto">
                                        <div class="form-group">
                                            <label class="text-dark mb-1 mt-3"
                                                for="SITE_RTL">{{ __('Enable RTL') }}</label>
                                            <div class="">
                                                <input type="checkbox" name="SITE_RTL" id="SITE_RTL"
                                                    data-toggle="switchbutton" value="on"
                                                    {{ $SITE_RTL == 'on' ? 'checked="checked"' : '' }}
                                                    data-onstyle="primary">
                                                <label class="form-check-label" for="SITE_RTL"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 my-auto">

                                        <div class="form-group">
                                            <label class=" text-dark mb-1 mt-3"
                                                for="enable_landing">{{ __('Enable Landing Page') }}</label>
                                            <div class="form-check form-switch d-inline-block">
                                                <input type="checkbox" value="on" name="enable_landing"
                                                    class="form-check-input" id="enable_landing"
                                                    data-toggle="switchbutton"
                                                    {{ Utility::getValByName('enable_landing') == 'yes' ? 'checked' : '' }}
                                                    data-onstyle="primary">
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <h4 class="small-title">{{ __('Theme Customizer') }}</h4>
                                <div class="setting-card setting-logo-box p-3">
                                    <div class="row">
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-3">
                                                <i data-feather="credit-card"
                                                    class="me-2"></i>{{ __('Primary color settings') }}
                                            </h6>
                                            <hr class="my-2" />
                                            <div class="theme-color themes-color">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-1' ? 'active_color' : '' }}"
                                                    data-value="theme-1" onclick="check_theme('theme-1')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-1"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-2' ? 'active_color' : '' }} "
                                                    data-value="theme-2" onclick="check_theme('theme-2')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-2"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-3' ? 'active_color' : '' }}"
                                                    data-value="theme-3" onclick="check_theme('theme-3')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-3"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-4' ? 'active_color' : '' }}"
                                                    data-value="theme-4" onclick="check_theme('theme-4')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-4"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-5' ? 'active_color' : '' }}"
                                                    data-value="theme-5" onclick="check_theme('theme-5')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-5"
                                                    style="display: none;">
                                                <br>
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-6' ? 'active_color' : '' }}"
                                                    data-value="theme-6" onclick="check_theme('theme-6')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-6"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-7' ? 'active_color' : '' }}"
                                                    data-value="theme-7" onclick="check_theme('theme-7')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-7"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-8' ? 'active_color' : '' }}"
                                                    data-value="theme-8" onclick="check_theme('theme-8')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-8"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-9' ? 'active_color' : '' }}"
                                                    data-value="theme-9" onclick="check_theme('theme-9')"></a>
                                                <input type="radio" class="theme_color" name="color" value="theme-9"
                                                    style="display: none;">
                                                <a href="#!"
                                                    class="{{ $settings['color'] == 'theme-10' ? 'active_color' : '' }}"
                                                    data-value="theme-10" onclick="check_theme('theme-10')"></a>
                                                <input type="radio" class="theme_color" name="color"
                                                    value="theme-10" style="display: none;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-3">
                                                <i data-feather="layout" class="me-2"></i>{{ __('Sidebar settings') }}
                                            </h6>
                                            <hr class="my-2" />
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" id="cust-theme-bg"
                                                    name="cust_theme_bg"
                                                    {{ Utility::getValByName('cust_theme_bg') == 'on' ? 'checked' : '' }} />
                                                <label class="form-check-label f-w-600 pl-1"
                                                    for="cust-theme-bg">{{ __('Transparent layout') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-xl-4 col-md-4">
                                            <h6 class="mt-3">
                                                <i data-feather="sun" class="me-2"></i>{{ __('Layout settings') }}
                                            </h6>
                                            <hr class="my-2" />
                                            <div class="form-check form-switch mt-2">
                                                <input type="checkbox" class="form-check-input" id="cust-darklayout"
                                                    name="cust_darklayout"{{ Utility::getValByName('cust_darklayout') == 'on' ? 'checked' : '' }} />
                                                <label class="form-check-label f-w-600 pl-1"
                                                    for="cust-darklayout">{{ __('Dark Layout') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12  text-end">
                                    <input type="submit" value="{{ __('Save Changes') }}"
                                        class="btn btn-print-invoice  btn-primary m-r-10">
                                </div>

                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                    <div class="card" id="email-setting">
                        {{ Form::open(['route' => 'email.settings.store', 'method' => 'post']) }}
                        @csrf

                        <div class="card-header">
                            <h5>{{ __('Email Settings') }}</h5>
                            <small class="text-dark font-weight-bold">{{ __('Edit your Email Settings') }}</small>
                        </div>
                        <div class="card-body">

                            <div class="row mt-4">


                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_driver"
                                            class="col-form-label text-dark">{{ __('Mail Driver') }}</label>
                                        <input type="text" name="mail_driver" id="mail_driver"
                                            class="form-control {{ $errors->has('mail_driver') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_driver']) || is_null($settings['mail_driver']) ? '' : $settings['mail_driver'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_driver_placeholder') }}" />
                                        @if ($errors->has('mail_driver'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_driver') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_host"
                                            class="col-form-label text-dark">{{ __('Mail Host') }}</label>
                                        <input type="text" name="mail_host" id="mail_host"
                                            class="form-control {{ $errors->has('mail_host') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_host']) || is_null($settings['mail_host']) ? '' : $settings['mail_host'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_host_placeholder') }}" />
                                        @if ($errors->has('mail_host'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_host') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_port"
                                            class="col-form-label text-dark">{{ __('Mail Port') }}</label>
                                        <input type="number" name="mail_port" id="mail_port"
                                            class="form-control {{ $errors->has('mail_port') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_port']) || is_null($settings['mail_port']) ? '' : $settings['mail_port'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_port_placeholder') }}" />
                                        @if ($errors->has('mail_port'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_port') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_username"
                                            class="col-form-label text-dark">{{ __('Mail Username') }}</label>
                                        <input type="text" name="mail_username" id="mail_username"
                                            class="form-control {{ $errors->has('mail_username') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_username']) || is_null($settings['mail_username']) ? '' : $settings['mail_username'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_username_placeholder') }}" />
                                        @if ($errors->has('mail_username'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_username') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_password"
                                            class="col-form-label text-dark">{{ __('Mail Password') }}</label>
                                        <input type="text" name="mail_password" id="mail_password"
                                            class="form-control {{ $errors->has('mail_password') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_password']) || is_null($settings['mail_password']) ? '' : $settings['mail_password'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_password_placeholder') }}" />
                                        @if ($errors->has('mail_password'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_password') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_encryption"
                                            class="col-form-label text-dark">{{ __('Mail Encryption') }}</label>
                                        <input type="text" name="mail_encryption" id="mail_encryption"
                                            class="form-control {{ $errors->has('mail_encryption') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_encryption']) || is_null($settings['mail_encryption']) ? '' : $settings['mail_encryption'] }}"
                                            placeholder="{{ trans('installer_messages.environment.wizard.form.app_tabs.mail_encryption_placeholder') }}" />
                                        @if ($errors->has('mail_encryption'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_encryption') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_from_address"
                                            class="col-form-label text-dark">{{ __('Mail From Address') }}</label>
                                        <input type="text" name="mail_from_address" id="mail_from_address"
                                            class="form-control {{ $errors->has('mail_from_address') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_from_address']) || is_null($settings['mail_from_address']) ? '' : $settings['mail_from_address'] }}"
                                            placeholder="{{ __('Enter Mail From Address') }}" />
                                        @if ($errors->has('mail_from_address'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_from_address') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_from_name"
                                            class="col-form-label text-dark">{{ __('Mail From Name') }}</label>
                                        <input type="text" name="mail_from_name" id="mail_from_name"
                                            class="form-control {{ $errors->has('mail_from_name') ? 'is-invalid' : '' }}"
                                            value="{{ !isset($settings['mail_from_name']) || is_null($settings['mail_from_name']) ? '' : $settings['mail_from_name'] }}"
                                            placeholder="{{ __('Enter Mail From Name') }}" />
                                        @if ($errors->has('mail_from_name'))
                                            <span class="invalid-feedback text-danger text-xs">
                                                {{ $errors->first('mail_from_name') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>


                            </div>
                            <div class="row">
                                <div class="footer-row d-flex justify-content-end flex-wrap">
                                    <a href="#" class="btn btn-print-invoice  btn-primary m-r-10 send_email mb-2"
                                        data-ajax-popup="true" data-title="{{ __('Send Test Mail') }}"
                                        data-url="{{ route('test.email') }}">
                                        {{ __('Send Test Mail') }}
                                    </a>

                                    <input type="submit" value="{{ __('Save Changes') }}"
                                        class="btn btn-print-invoice  btn-primary m-r-10 mb-2">
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>

                    <div class="card" id="pusher-setting">
                        <form method="POST" action="{{ route('pusher.settings.store') }}" accept-charset="UTF-8">
                            @csrf
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-6">
                                        <h5>{{ __('Pusher Settings') }}</h5>
                                        <small>{{ __('Pusher Settings') }}</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-end">
                                            <input type="checkbox" name="enable_chat" id="enable_chat"
                                                data-toggle="switchbutton"
                                                @if ($settings['enable_chat'] == 'yes') checked @endif value="yes"
                                                data-onstyle="primary">


                                            <label class="form-check-labe" for="enable_chat"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pusher_app_id"
                                                class="col-form-label text-dark">{{ __('Pusher App Id') }}</label>
                                            <input type="text" name="pusher_app_id" id="pusher_app_id"
                                                class="form-control {{ $errors->has('pusher_app_id') ? 'is-invalid' : '' }}"
                                                value="{{ !isset($settings['pusher_app_id']) || is_null($settings['pusher_app_id']) ? '' : $settings['pusher_app_id'] }}"
                                                placeholder="{{ __('Pusher App Id') }}" />
                                            @if ($errors->has('pusher_app_id'))
                                                <span class="invalid-feedback text-danger text-xs">
                                                    {{ $errors->first('pusher_app_id') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pusher_app_key"
                                                class="col-form-label text-dark">{{ __('Pusher App Key') }}</label>
                                            <input type="text" name="pusher_app_key" id="pusher_app_key"
                                                class="form-control {{ $errors->has('pusher_app_key') ? 'is-invalid' : '' }}"
                                                value="{{ !isset($settings['pusher_app_key']) || is_null($settings['pusher_app_key']) ? '' : $settings['pusher_app_key'] }}"
                                                placeholder="{{ __('Pusher App Key') }}" />
                                            @if ($errors->has('pusher_app_key'))
                                                <span class="invalid-feedback text-danger text-xs">
                                                    {{ $errors->first('pusher_app_key') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pusher_app_secret"
                                                class="col-form-label text-dark">{{ __('Pusher App Secret') }}</label>
                                            <input type="text" name="pusher_app_secret" id="pusher_app_secret"
                                                class="form-control {{ $errors->has('pusher_app_secret') ? 'is-invalid' : '' }}"
                                                value="{{ !isset($settings['pusher_app_secret']) || is_null($settings['pusher_app_secret']) ? '' : $settings['pusher_app_secret'] }}"
                                                placeholder="{{ __('Pusher App Secret') }}" />
                                            @if ($errors->has('pusher_app_secret'))
                                                <span class="invalid-feedback text-danger text-xs">
                                                    {{ $errors->first('pusher_app_secret') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pusher_app_cluster"
                                                class="col-form-label text-dark">{{ __('Pusher App Cluster') }}</label>
                                            <input type="text" name="pusher_app_cluster" id="pusher_app_cluster"
                                                class="form-control {{ $errors->has('pusher_app_cluster') ? 'is-invalid' : '' }}"
                                                value="{{ !isset($settings['pusher_app_cluster']) || is_null($settings['pusher_app_cluster']) ? '' : $settings['pusher_app_cluster'] }}"
                                                placeholder="{{ __('Pusher App Cluster') }}" />
                                            @if ($errors->has('pusher_app_cluster'))
                                                <span class="invalid-feedback text-danger text-xs">
                                                    {{ $errors->first('pusher_app_cluster') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-xs">
                                        <a href="https://pusher.com/channels"
                                            target="_blank">{{ __('You can Make Pusher channel Account from here and Get your App Id and Secret key') }}</a>
                                    </div>
                                </div>
                                <div class="col-lg-12  text-end">
                                    <input type="submit" value="{{ __('Save Changes') }}"
                                        class="btn btn-print-invoice  btn-primary m-r-10">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="system-setting" class="card">
                        <div class="card-header">
                            <h5>{{ __('System Settings') }}</h5>
                            <small class="text-dark font-weight-bold">{{ __('Edit your System Settings') }}</small>
                        </div>
                        <div class="card-body">
                            <form id="setting-form" method="post" action="{{ route('settings.store') }}">
                                @csrf

                                <div class="row company-setting">
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Title Text') }}</label>
                                        <input type="text" name="header_text" class="form-control" id="header_text"
                                            value="{{ Utility::getValByName('header_text') }}"
                                            placeholder="{{ __('Enter Header Title Text') }}">
                                        @error('header_text')
                                            <span class="invalid-header_text text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Footer Text') }}</label>
                                        <input type="text" name="footer_text" class="form-control" id="footer_text"
                                            value="{{ Utility::getValByName('footer_text') }}"
                                            placeholder="{{ __('Enter Footer Text') }}">
                                        @error('footer_text')
                                            <span class="invalid-header_text text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Default Language') }}</label>
                                        <select name="default_language" id="default_language"
                                            class="form-control select2">
                                            @foreach (Utility::languages() as $language)
                                                <option @if (Utility::getValByName('default_language') == $language) selected @endif
                                                    value="{{ $language }}">{{ Str::upper($language) }}</option>
                                            @endforeach
                                        </select>
                                        @error('default_language')
                                            <span class="invalid-header_text text-xs"
                                                role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Currency') }} *</label>
                                        <input type="text" name="site_currency" class="form-control"
                                            id="site_currency" value="{{ $settings['site_currency'] }}" required>
                                        <small class="text-xs">
                                            {{ __('Note: Add currency code as per three-letter ISO code') }}.
                                            <a href="https://stripe.com/docs/currencies"
                                                target="_blank">{{ __('you can find out here..') }}</a>
                                        </small>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Currency Symbol') }} *</label>
                                        <input type="text" name="site_currency_symbol" class="form-control"
                                            id="site_currency_symbol" value="{{ $settings['site_currency_symbol'] }}"
                                            required>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Currency Symbol Position') }} *</label>
                                        <div class="d-flex radio-check">
                                            <div class="custom-control custom-radio custom-control-inline m-1">
                                                <input type="radio" id="pre" value="pre"
                                                    name="site_currency_symbol_position" class="form-check-input"
                                                    @if ($settings['site_currency_symbol_position'] == 'pre') checked @endif>
                                                <label class="form-check-labe"
                                                    for="pre">{{ __('Pre') }}</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline m-1">
                                                <input type="radio" id="post" value="post"
                                                    name="site_currency_symbol_position" class="form-check-input"
                                                    @if ($settings['site_currency_symbol_position'] == 'post') checked @endif>
                                                <label class="form-check-labe"
                                                    for="post">{{ __('Post') }}</label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="site_date_format"
                                            class="col-form-label">{{ __('Date Format') }}</label>
                                        <select type="text" name="site_date_format" class="form-control select2"
                                            id="site_date_format">
                                            <option value="M j, Y"
                                                @if ($settings['site_date_format'] == 'M j, Y') selected="selected" @endif>
                                                {{ date('M d Y') }}</option>
                                            <option value="d-m-Y"
                                                @if ($settings['site_date_format'] == 'd-m-Y') selected="selected" @endif>
                                                {{ date('d-m-y') }}</option>
                                            <option value="m-d-Y"
                                                @if ($settings['site_date_format'] == 'm-d-Y') selected="selected" @endif>
                                                {{ date('m-d-y') }}</option>
                                            <option value="Y-m-d"
                                                @if ($settings['site_date_format'] == 'Y-m-d') selected="selected" @endif>
                                                {{ date('y-m-d') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="site_time_format"
                                            class="col-form-label">{{ __('Time Format') }}</label>
                                        <select type="text" name="site_time_format" class="form-control select2"
                                            id="site_time_format">
                                            <option value="g:i A"
                                                @if ($settings['site_time_format'] == 'g:i A') selected="selected" @endif>
                                                {{ date('H:s A') }} </option>
                                            <option value="g:i a"
                                                @if ($settings['site_time_format'] == 'g:i a') selected="selected" @endif>
                                                {{ date('H:s a') }}</option>
                                            <option value="H:i"
                                                @if ($settings['site_time_format'] == 'H:i') selected="selected" @endif>
                                                {{ date('G:s') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="invoice_prefix" class="col-form-label">{{ __('Invoice Prefix') }}
                                            *</label>
                                        <input type="text" name="invoice_prefix" class="form-control"
                                            id="invoice_prefix" value="{{ $settings['invoice_prefix'] }}" required>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="contract_prefix"
                                            class="col-form-label">{{ __('Contract Prefix') }}</label>
                                        <input type="text" name="contract_prefix" class="form-control"
                                            id="contract_prefix" value="{{ $settings['contract_prefix'] }}" required>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="estimation_prefix"
                                            class="col-form-label">{{ __('Estimation Prefix') }} *</label>
                                        <input type="text" name="estimation_prefix" class="form-control"
                                            id="estimation_prefix" value="{{ $settings['estimation_prefix'] }}"
                                            required>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label class="col-form-label">{{ __('Invoice/Estimation/MDF Title') }} *</label>
                                        <input type="text" name="footer_title" class="form-control" id="footer_title"
                                            value="{{ $settings['footer_title'] }}">
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="footer_note"
                                            class="col-form-label">{{ __('Invoice/Estimation/MDF Note') }} *</label>
                                        <textarea name="footer_note" id="footer_note" class="form-control">{{ $settings['footer_note'] }}</textarea>
                                    </div>
                                    <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                        <label for="mdf_prefix" class="col-form-label">{{ __('MDF Prefix') }}
                                        </label>
                                        <input type="text" name="mdf_prefix" class="form-control" id="mdf_prefix"
                                            value="{{ $settings['mdf_prefix'] }}" required>
                                    </div>

                                    <div class="form-group col-md-12 text-end">
                                        <input type="submit" id="addSig" value="{{ __('Save Changes') }}"
                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    @can('Manage Company Settings')
                        <div class="card" id="company-setting">
                            <div class="card-header">
                                <h5>{{ __('Company Settings') }}</h5>
                                <small class="text-dark font-weight-bold">{{ __('Edit your Company Settings') }}</small>
                            </div>
                            <div class="card-body">
                                <form id="setting-form" method="post" action="{{ route('settings.store') }}">
                                    @csrf
                                    <div class="row company-setting">
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_name" class="col-form-label">{{ __('Company Name') }}
                                                *</label>
                                            <input type="text" name="company_name" class="form-control" id="company_name"
                                                value="{{ $settings['company_name'] }}" placeholder="Enter Company Name" required>
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_address" class="col-form-label">{{ __('Address') }}</label>
                                            <input type="text" name="company_address" class="form-control"
                                                id="company_address" value="{{ $settings['company_address'] }}" placeholder="Enter Company Address">
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_city" class="col-form-label">{{ __('City') }}</label>
                                            <input type="text" name="company_city" class="form-control" id="company_city"
                                                value="{{ $settings['company_city'] }}" placeholder="Enter Company City">
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_state" class="col-form-label">{{ __('State') }}</label>
                                            <input type="text" name="company_state" class="form-control"
                                                id="company_state" value="{{ $settings['company_state'] }}" placeholder="Enter Company State">
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_zipcode"
                                                class="col-form-label">{{ __('Zip/Post Code') }}</label>
                                            <input type="text" name="company_zipcode" class="form-control"
                                                id="company_zipcode" value="{{ $settings['company_zipcode'] }}" placeholder="Enter Company Zipcode">
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_country" class="col-form-label">{{ __('Country') }}</label>
                                            <input type="text" name="company_country" class="form-control"
                                                id="company_country" value="{{ $settings['company_country'] }}" placeholder="Enter Company Country">
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <label for="company_telephone"
                                                class="col-form-label">{{ __('Telephone') }}</label>
                                            <input type="text" name="company_telephone" class="form-control"
                                                id="company_telephone" value="{{ $settings['company_telephone'] }}" placeholder="Enter Company Telephone">
                                        </div>

                                        <div class="form-group col-md-12 text-end">
                                            <input type="submit" id="save-btn" value="{{ __('Save Changes') }}"
                                                class="btn btn-print-invoice  btn-primary m-r-10">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endcan


                    <div class="card" id="company-payment-setting">
                        <div class="card-header">
                            <h5>{{ __('Payment Settings') }}</h5>
                            <small
                                class="text-dark font-weight-bold">{{ __('These details will be used to collect invoice payments. Each invoice will have a payment button based on the below configuration.') }}</small>
                        </div>
                        <div class="card-body">
                            <form id="setting-form" method="post" action="{{ route('payment.settings') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <div class="">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                                        <label class="col-form-label">{{ __('Currency') }} *</label>
                                                        <input type="text" name="currency" class="form-control"
                                                            id="currency"
                                                            value="{{ !isset($payment['currency']) || is_null($payment['currency']) ? '' : $payment['currency'] }}"
                                                            required>
                                                        <small class="text-xs">
                                                            {{ __('Note: Add currency code as per three-letter ISO code') }}.
                                                            <a href="https://stripe.com/docs/currencies"
                                                                target="_blank">{{ __('You can find out how to do that here.') }}</a>
                                                        </small>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                                        <label for="currency_symbol"
                                                            class="col-form-label">{{ __('Currency Symbol') }}</label>
                                                        <input type="text" name="currency_symbol" class="form-control"
                                                            id="currency_symbol"
                                                            value="{{ !isset($payment['currency_symbol']) || is_null($payment['currency_symbol']) ? '' : $payment['currency_symbol'] }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="faq justify-content-center">
                                    <div class="col-sm-12 col-md-10 col-xxl-12">
                                        <div class="accordion accordion-flush setting-accordion" id="accordionExample">

                                            <!-- Bank Transfer -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-1">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse15"
                                                        aria-expanded="true" aria-controls="collapse15">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Bank Transfer') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_banktransfer_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_banktransfer_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_banktransfer_enabled"
                                                                    id="is_banktransfer_enabled"
                                                                    {{ isset($payment['is_banktransfer_enabled']) && $payment['is_banktransfer_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse15"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-1"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body" style="padding: 20px;">
                                                        <div class="row">
                                                            <div class="form-group col-12">
                                                                {{ Form::label('bank_details', __('Bank Details'), ['class' => 'col-form-label']) }}
                                                                {{ Form::textarea('bank_details', !isset($payment['bank_details']) || is_null($payment['bank_details']) ? '' : $payment['bank_details'], ['class' => 'form-control', 'rows' => '6']) }}
                                                            </div>
                                                            <small class="text-xs">
                                                                {{ __('Example: Bank: bank name </br> Account Number : 0000 0000 </br>') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Strip -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-2">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse1"
                                                        aria-expanded="true" aria-controls="collapse1">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Stripe') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_stripe_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_stripe_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_stripe_enabled" id="is_stripe_enabled"
                                                                    {{ isset($payment['is_stripe_enabled']) && $payment['is_stripe_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse1"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-2"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="stripe_key"
                                                                        class="col-form-label">{{ __('Stripe Key') }}</label>
                                                                    <input class="form-control"
                                                                        placeholder="{{ __('Stripe Key') }}"
                                                                        name="stripe_key" type="text"
                                                                        value="{{ !isset($payment['stripe_key']) || is_null($payment['stripe_key']) ? '' : $payment['stripe_key'] }}"
                                                                        id="stripe_key">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="stripe_secret"
                                                                        class="col-form-label">{{ __('Stripe Secret') }}</label>
                                                                    <input class="form-control "
                                                                        placeholder="{{ __('Stripe Secret') }}"
                                                                        name="stripe_secret" type="text"
                                                                        value="{{ !isset($payment['stripe_secret']) || is_null($payment['stripe_secret']) ? '' : $payment['stripe_secret'] }}"
                                                                        id="stripe_secret">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Paypal -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-3">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse2"
                                                        aria-expanded="true" aria-controls="collapse2">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Paypal') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_paypal_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_paypal_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paypal_enabled" id="is_paypal_enabled"
                                                                    {{ isset($payment['is_paypal_enabled']) && $payment['is_paypal_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse2"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-3"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="paypal-label col-form-label"
                                                                    for="paypal_mode">{{ __('Paypal Mode') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ !isset($payment['paypal_mode']) || $payment['paypal_mode'] == '' || $payment['paypal_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="paypal_mode" value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['paypal_mode']) && $payment['paypal_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paypal_client_id"
                                                                        class="col-form-label">{{ __('Client ID') }}</label>
                                                                    <input type="text" name="paypal_client_id"
                                                                        id="paypal_client_id" class="form-control"
                                                                        value="{{ !isset($payment['paypal_client_id']) || is_null($payment['paypal_client_id']) ? '' : $payment['paypal_client_id'] }}"
                                                                        placeholder="{{ __('Client ID') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paypal_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="paypal_secret_key"
                                                                        id="paypal_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['paypal_secret_key']) || is_null($payment['paypal_secret_key']) ? '' : $payment['paypal_secret_key'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Paystack -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-4">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse3"
                                                        aria-expanded="true" aria-controls="collapse3">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Paystack') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_paystack_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_paystack_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paystack_enabled" id="is_paystack_enabled"
                                                                    {{ isset($payment['is_paystack_enabled']) && $payment['is_paystack_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse3"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-4"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paypal_client_id"
                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                    <input type="text" name="paystack_public_key"
                                                                        id="paystack_public_key" class="form-control"
                                                                        value="{{ !isset($payment['paystack_public_key']) || is_null($payment['paystack_public_key']) ? '' : $payment['paystack_public_key'] }}"
                                                                        placeholder="{{ __('Public Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paystack_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="paystack_secret_key"
                                                                        id="paystack_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['paystack_secret_key']) || is_null($payment['paystack_secret_key']) ? '' : $payment['paystack_secret_key'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- FLUTTERWAVE -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-5">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse4"
                                                        aria-expanded="true" aria-controls="collapse4">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Flutterwave') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_flutterwave_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_flutterwave_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_flutterwave_enabled"
                                                                    id="is_flutterwave_enabled"
                                                                    {{ isset($payment['is_flutterwave_enabled']) && $payment['is_flutterwave_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse4"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-5"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paypal_client_id"
                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                    <input type="text" name="flutterwave_public_key"
                                                                        id="flutterwave_public_key" class="form-control"
                                                                        value="{{ !isset($payment['flutterwave_public_key']) || is_null($payment['flutterwave_public_key']) ? '' : $payment['flutterwave_public_key'] }}"
                                                                        placeholder="Public Key">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paystack_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="flutterwave_secret_key"
                                                                        id="flutterwave_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['flutterwave_secret_key']) || is_null($payment['flutterwave_secret_key']) ? '' : $payment['flutterwave_secret_key'] }}"
                                                                        placeholder="Secret Key">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Razorpay -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-6">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse5"
                                                        aria-expanded="true" aria-controls="collapse5">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Razorpay') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_razorpay_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_razorpay_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_razorpay_enabled" id="is_razorpay_enabled"
                                                                    {{ isset($payment['is_razorpay_enabled']) && $payment['is_razorpay_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse5"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-6"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paypal_client_id"
                                                                        class="col-form-label">{{ __('Public Key') }}</label>

                                                                    <input type="text" name="razorpay_public_key"
                                                                        id="razorpay_public_key" class="form-control"
                                                                        value="{{ !isset($payment['razorpay_public_key']) || is_null($payment['razorpay_public_key']) ? '' : $payment['razorpay_public_key'] }}"
                                                                        placeholder="Public Key">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paystack_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="razorpay_secret_key"
                                                                        id="razorpay_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['razorpay_secret_key']) || is_null($payment['razorpay_secret_key']) ? '' : $payment['razorpay_secret_key'] }}"
                                                                        placeholder="Secret Key">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Paytm -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-7">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse6"
                                                        aria-expanded="true" aria-controls="collapse6">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Paytm') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_paytm_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_paytm_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paytm_enabled" id="is_paytm_enabled"
                                                                    {{ isset($payment['is_paytm_enabled']) && $payment['is_paytm_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse6"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-7"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="paypal-label col-form-label"
                                                                    for="paypal_mode">{{ __('Paytm Environment') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">

                                                                                    <input type="radio"
                                                                                        name="paytm_mode" value="local"
                                                                                        class="form-check-input"
                                                                                        {{ !isset($payment['paytm_mode']) || $payment['paytm_mode'] == '' || $payment['paytm_mode'] == 'local' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Local') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="paytm_mode"
                                                                                        value="production"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['paytm_mode']) && $payment['paytm_mode'] == 'production' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Production') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="paytm_public_key"
                                                                        class="col-form-label">{{ __('Merchant ID') }}</label>
                                                                    <input type="text" name="paytm_merchant_id"
                                                                        id="paytm_merchant_id" class="form-control"
                                                                        value="{{ !isset($payment['paytm_merchant_id']) || is_null($payment['paytm_merchant_id']) ? '' : $payment['paytm_merchant_id'] }}"
                                                                        placeholder="Merchant ID">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="paytm_secret_key"
                                                                        class="col-form-label">{{ __('Merchant Key') }}</label>
                                                                    <input type="text" name="paytm_merchant_key"
                                                                        id="paytm_merchant_key" class="form-control"
                                                                        value="{{ !isset($payment['paytm_merchant_key']) || is_null($payment['paytm_merchant_key']) ? '' : $payment['paytm_merchant_key'] }}"
                                                                        placeholder="Merchant Key">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="paytm_industry_type"
                                                                        class="col-form-label">{{ __('Industry Type') }}</label>
                                                                    <input type="text" name="paytm_industry_type"
                                                                        id="paytm_industry_type" class="form-control"
                                                                        value="{{ !isset($payment['paytm_industry_type']) || is_null($payment['paytm_industry_type']) ? '' : $payment['paytm_industry_type'] }}"
                                                                        placeholder="Industry Type">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Mercado Pago-->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-8">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse7"
                                                        aria-expanded="true" aria-controls="collapse7">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Mercado Pago') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_mercado_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_mercado_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_mercado_enabled" id="is_mercado_enabled"
                                                                    {{ isset($payment['is_mercado_enabled']) && $payment['is_mercado_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse7"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-8"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="coingate-label col-form-label"
                                                                    for="mercado_mode">{{ __('Mercado Mode') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="mercado_mode"
                                                                                        value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ (isset($payment['mercado_mode']) && $payment['mercado_mode'] == '') || (isset($payment['mercado_mode']) && $payment['mercado_mode'] == 'sandbox') ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="mercado_mode"
                                                                                        value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['mercado_mode']) && $payment['mercado_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="mercado_access_token"
                                                                        class="col-form-label">{{ __('Access Token') }}</label>
                                                                    <input type="text" name="mercado_access_token"
                                                                        id="mercado_access_token" class="form-control"
                                                                        value="{{ isset($payment['mercado_access_token']) ? $payment['mercado_access_token'] : '' }}"
                                                                        placeholder="{{ __('Access Token') }}" />
                                                                    @if ($errors->has('mercado_secret_key'))
                                                                        <span class="invalid-feedback d-block">
                                                                            {{ $errors->first('mercado_access_token') }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Mollie -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-9">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse8"
                                                        aria-expanded="true" aria-controls="collapse8">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Mollie') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_mollie_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_mollie_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_mollie_enabled" id="is_mollie_enabled"
                                                                    {{ isset($payment['is_mollie_enabled']) && $payment['is_mollie_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse8"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-9"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="mollie_api_key"
                                                                        class="col-form-label">{{ __('Mollie Api Key') }}</label>
                                                                    <input type="text" name="mollie_api_key"
                                                                        id="mollie_api_key" class="form-control"
                                                                        value="{{ !isset($payment['mollie_api_key']) || is_null($payment['mollie_api_key']) ? '' : $payment['mollie_api_key'] }}"
                                                                        placeholder="Mollie Api Key">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="mollie_profile_id"
                                                                        class="col-form-label">{{ __('Mollie Profile Id') }}</label>
                                                                    <input type="text" name="mollie_profile_id"
                                                                        id="mollie_profile_id" class="form-control"
                                                                        value="{{ !isset($payment['mollie_profile_id']) || is_null($payment['mollie_profile_id']) ? '' : $payment['mollie_profile_id'] }}"
                                                                        placeholder="Mollie Profile Id">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="mollie_partner_id"
                                                                        class="col-form-label">{{ __('Mollie Partner Id') }}</label>
                                                                    <input type="text" name="mollie_partner_id"
                                                                        id="mollie_partner_id" class="form-control"
                                                                        value="{{ !isset($payment['mollie_partner_id']) || is_null($payment['mollie_partner_id']) ? '' : $payment['mollie_partner_id'] }}"
                                                                        placeholder="Mollie Partner Id">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Skrill -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-10">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse9"
                                                        aria-expanded="true" aria-controls="collapse9">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Skrill') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_skrill_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_skrill_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_skrill_enabled" id="is_skrill_enabled"
                                                                    {{ isset($payment['is_skrill_enabled']) && $payment['is_skrill_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse9"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-10"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="mollie_api_key"
                                                                        class="col-form-label">Skrill Email</label>
                                                                    <input type="text" name="skrill_email"
                                                                        id="skrill_email" class="form-control"
                                                                        value="{{ !isset($payment['skrill_email']) || is_null($payment['skrill_email']) ? '' : $payment['skrill_email'] }}"
                                                                        placeholder="Enter Skrill Email">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- CoinGate -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-11">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse10"
                                                        aria-expanded="true" aria-controls="collapse10">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('CoinGate') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_coingate_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_coingate_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_coingate_enabled" id="is_coingate_enabled"
                                                                    {{ isset($payment['is_coingate_enabled']) && $payment['is_coingate_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse10"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-11"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="col-form-label"
                                                                    for="coingate_mode">{{ __('CoinGate Mode') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">

                                                                                    <input type="radio"
                                                                                        name="coingate_mode"
                                                                                        value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ !isset($payment['coingate_mode']) || $payment['coingate_mode'] == '' || $payment['coingate_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="coingate_mode"
                                                                                        value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['coingate_mode']) && $payment['coingate_mode'] == 'live' ? 'checked="checked"' : '' }}>
                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="coingate_auth_token"
                                                                        class="col-form-label">{{ __('CoinGate Auth Token') }}</label>
                                                                    <input type="text" name="coingate_auth_token"
                                                                        id="coingate_auth_token" class="form-control"
                                                                        value="{{ !isset($payment['coingate_auth_token']) || is_null($payment['coingate_auth_token']) ? '' : $payment['coingate_auth_token'] }}"
                                                                        placeholder="CoinGate Auth Token">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- PaymentWall -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-12">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse11"
                                                        aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('PaymentWall') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_paymentwall_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="orm-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_paymentwall_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paymentwall_enabled"
                                                                    id="is_paymentwall_enabled"
                                                                    {{ isset($payment['is_paymentwall_enabled']) && $payment['is_paymentwall_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse11"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-12"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paymentwall_public_key"
                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                    <input type="text" name="paymentwall_public_key"
                                                                        id="paymentwall_public_key" class="form-control"
                                                                        value="{{ !isset($payment['paymentwall_public_key']) || is_null($payment['paymentwall_public_key']) ? '' : $payment['paymentwall_public_key'] }}"
                                                                        placeholder="{{ __('Public Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paymentwall_private_key"
                                                                        class="col-form-label">{{ __('Private Key') }}</label>
                                                                    <input type="text" name="paymentwall_private_key"
                                                                        id="paymentwall_private_key"
                                                                        class="form-control"
                                                                        value="{{ !isset($payment['paymentwall_private_key']) || is_null($payment['paymentwall_private_key']) ? '' : $payment['paymentwall_private_key'] }}"
                                                                        placeholder="{{ __('Private Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toyyibpay -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-13">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse12"
                                                        aria-expanded="true" aria-controls="collapse12">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Toyyibpay') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_toyyibpay_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_toyyibpay_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_toyyibpay_enabled"
                                                                    id="is_toyyibpay_enabled"
                                                                    {{ isset($payment['is_toyyibpay_enabled']) && $payment['is_toyyibpay_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse12"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-13"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="toyyibpay_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="toyyibpay_secret_key"
                                                                        id="toyyibpay_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['toyyibpay_secret_key']) || is_null($payment['toyyibpay_secret_key']) ? '' : $payment['toyyibpay_secret_key'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="category_code"
                                                                        class="col-form-label">{{ __('Category Code') }}</label>
                                                                    <input type="text" name="category_code"
                                                                        id="category_code" class="form-control"
                                                                        value="{{ !isset($payment['category_code']) || is_null($payment['category_code']) ? '' : $payment['category_code'] }}"
                                                                        placeholder="{{ __('Category Code') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- PayFast -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-14">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse13"
                                                        aria-expanded="true" aria-controls="collapse13">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Payfast') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_payfast_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_payfast_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_payfast_enabled" id="is_payfast_enabled"
                                                                    {{ isset($payment['is_payfast_enabled']) && $payment['is_payfast_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse13"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-14"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="paypal-label col-form-label"
                                                                    for="payfast_mode">{{ __('Payfast Mode') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="payfast_mode"
                                                                                        value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ !isset($payment['payfast_mode']) || $payment['payfast_mode'] == '' || $payment['payfast_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="payfast_mode"
                                                                                        value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['payfast_mode']) && $payment['payfast_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="payfast_merchant_id"
                                                                        class="col-form-label">{{ __('Merchant Id') }}</label>
                                                                    <input type="text" name="payfast_merchant_id"
                                                                        id="payfast_merchant_id" class="form-control"
                                                                        value="{{ !isset($payment['payfast_merchant_id']) || is_null($payment['payfast_merchant_id']) ? '' : $payment['payfast_merchant_id'] }}"
                                                                        placeholder="{{ __('Merchant Id') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="payfast_merchant_key"
                                                                        class="col-form-label">{{ __('Merchant Key') }}</label>
                                                                    <input type="text" name="payfast_merchant_key"
                                                                        id="payfast_merchant_key" class="form-control"
                                                                        value="{{ !isset($payment['payfast_merchant_key']) || is_null($payment['payfast_merchant_key']) ? '' : $payment['payfast_merchant_key'] }}"
                                                                        placeholder="{{ __('Merchant Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="payfast_signature"
                                                                        class="col-form-label">{{ __('Signature') }}</label>
                                                                    <input type="text" name="payfast_signature"
                                                                        id="payfast_signature" class="form-control"
                                                                        value="{{ !isset($payment['payfast_signature']) || is_null($payment['payfast_signature']) ? '' : $payment['payfast_signature'] }}"
                                                                        placeholder="{{ __('Signature') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- iyzipay -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-15">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse14"
                                                        aria-expanded="true" aria-controls="collapse14">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Iyzipay') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_iyzipay_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_iyzipay_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_iyzipay_enabled" id="is_iyzipay_enabled"
                                                                    {{ isset($payment['is_iyzipay_enabled']) && $payment['is_iyzipay_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse14"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-15"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-12 pb-4">
                                                                <label class="paypal-label col-form-label"
                                                                    for="iyzipay_mode">{{ __('Iyzipay Mode') }}</label>
                                                                <br>
                                                                <div class="d-flex">
                                                                    <div class="mr-2" style="margin-right: 15px;">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="iyzipay_mode"
                                                                                        value="sandbox"
                                                                                        class="form-check-input"
                                                                                        {{ !isset($payment['iyzipay_mode']) || $payment['iyzipay_mode'] == '' || $payment['iyzipay_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Sandbox') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mr-2">
                                                                        <div class="border card p-3">
                                                                            <div class="form-check">
                                                                                <label class="form-check-labe text-dark">
                                                                                    <input type="radio"
                                                                                        name="iyzipay_mode"
                                                                                        value="live"
                                                                                        class="form-check-input"
                                                                                        {{ isset($payment['iyzipay_mode']) && $payment['iyzipay_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                    {{ __('Live') }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="iyzipay_public_key"
                                                                        class="col-form-label">{{ __('Public Key') }}</label>
                                                                    <input type="text" name="iyzipay_public_key"
                                                                        id="iyzipay_public_key" class="form-control"
                                                                        value="{{ !isset($payment['iyzipay_public_key']) || is_null($payment['iyzipay_public_key']) ? '' : $payment['iyzipay_public_key'] }}"
                                                                        placeholder="{{ __('Public Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="iyzipay_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="iyzipay_secret_key"
                                                                        id="iyzipay_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['iyzipay_secret_key']) || is_null($payment['iyzipay_secret_key']) ? '' : $payment['iyzipay_secret_key'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Sspay -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-2-16">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse16"
                                                        aria-expanded="true" aria-controls="collapse16">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Sspay') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <label class="custom-control-label form-control-label"
                                                                for="is_sspay_enabled">
                                                                <span class="me-2">{{ __('Enable') }}</span></label>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_sspay_enabled"
                                                                    value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_sspay_enabled" id="is_sspay_enabled"
                                                                    {{ isset($payment['is_sspay_enabled']) && $payment['is_sspay_enabled'] == 'on' ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse16"
                                                    class="accordion-collapse collapse"aria-labelledby="heading-2-13"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="sspay_secret_key"
                                                                        class="col-form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="sspay_secret_key"
                                                                        id="sspay_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['sspay_secret_key']) || is_null($payment['sspay_secret_key']) ? '' : $payment['sspay_secret_key'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="sspay_category_code"
                                                                        class="col-form-label">{{ __('Category Code') }}</label>
                                                                    <input type="text" name="sspay_category_code"
                                                                        id="sspay_category_code" class="form-control"
                                                                        value="{{ !isset($payment['sspay_category_code']) || is_null($payment['sspay_category_code']) ? '' : $payment['sspay_category_code'] }}"
                                                                        placeholder="{{ __('Category Code') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            {{-- Paytab --}}

                                            <div class="accordion-item card">
                                                <h2 class="accordion-header" id="heading-2-12">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse17"
                                                        aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">

                                                            {{ __('Paytab') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                            <div class=" form-check form-switch custom-switch-v1">
                                                                <input type="hidden"
                                                                    name="is_paytab_enabled"value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paytab_enabled" id="is_paytab_enabled"
                                                                    {{ isset($payment['is_paytab_enabled']) && $payment['is_paytab_enabled'] == 'on' ? 'checked' : '' }}>
                                                                <label class="custom-control-label form-control-label"
                                                                    for="is_paytab_enabled"></label>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse17" class="accordion-collapse collapse"
                                                    aria-labelledby="heading-2-3" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytab_profile_id"
                                                                        class="form-label">{{ __('Profile Id') }}</label>
                                                                    <input type="text" name="paytab_profile_id"
                                                                        id="paytab_profile_id" class="form-control"
                                                                        value="{{ !isset($payment['paytab_profile_id']) || is_null($payment['paytab_profile_id']) ? '' : $payment['paytab_profile_id'] }}"
                                                                        placeholder="{{ __('Profile Id') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytab_server_key"
                                                                        class="form-label">{{ __('Server Key') }}</label>
                                                                    <input type="text" name="paytab_server_key"
                                                                        id="paytab_server_key" class="form-control"
                                                                        value="{{ !isset($payment['paytab_server_key']) || is_null($payment['paytab_server_key']) ? '' : $payment['paytab_server_key'] }}"
                                                                        placeholder="{{ __('Server Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytab_region"
                                                                        class="form-label">{{ __('Region') }}</label>
                                                                    <input type="text" name="paytab_region"
                                                                        id="paytab_region" class="form-control"
                                                                        value="{{ !isset($payment['paytab_region']) || is_null($payment['paytab_region']) ? '' : $payment['paytab_region'] }}"
                                                                        placeholder="{{ __('Region') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            {{-- Benift --}}
                                            <div class="accordion-item card">
                                                <h2 class="accordion-header" id="heading-2-12">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse18"
                                                        aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">

                                                            {{ __('Benefit') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                            <div class=" form-check form-switch custom-switch-v1">
                                                                <input type="hidden"
                                                                    name="is_benefit_enabled"value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_benefit_enabled" id="is_benefit_enabled"
                                                                    {{ isset($payment['is_benefit_enabled']) && $payment['is_benefit_enabled'] == 'on' ? 'checked' : '' }}>
                                                                <label class="custom-control-label form-control-label"
                                                                    for="is_benefit_enabled"></label>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>

                                                <div id="collapse18" class="accordion-collapse collapse"
                                                    aria-labelledby="heading-2-3" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="benefit_api_key"
                                                                        class="form-label">{{ __('Benefit Key') }}</label>
                                                                    <input type="text" name="benefit_api_key"
                                                                        id="benefit_api_key" class="form-control"
                                                                        value="{{ !isset($payment['benefit_api_key']) || is_null($payment['benefit_api_key']) ? '' : $payment['benefit_api_key'] }}"
                                                                        placeholder="{{ __('Benefit Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="benefit_secret_key"
                                                                        class="form-label">{{ __('Benefit Secret Key') }}</label>
                                                                    <input type="text" name="benefit_secret_key"
                                                                        id="benefit_secret_key" class="form-control"
                                                                        value="{{ !isset($payment['benefit_secret_key']) || is_null($payment['benefit_secret_key']) ? '' : $payment['benefit_secret_key'] }}"
                                                                        placeholder="{{ __('Benefit Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            {{-- Cashfree --}}
                                            <div class="accordion-item card">
                                                <h2 class="accordion-header" id="heading-2-12">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse19"
                                                        aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">

                                                            {{ __('CashFree') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                            <div class=" form-check form-switch custom-switch-v1">
                                                                <input type="hidden"
                                                                    name="is_cashefree_enabled"value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_cashefree_enabled"
                                                                    id="is_cashefree_enabled"
                                                                    {{ isset($payment['is_cashefree_enabled']) && $payment['is_cashefree_enabled'] == 'on' ? 'checked' : '' }}>
                                                                <label class="custom-control-label form-control-label"
                                                                    for="is_cashefree_enabled"></label>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse19" class="accordion-collapse collapse"
                                                    aria-labelledby="heading-2-3" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="cashfree_key"
                                                                        class="form-label">{{ __('Cashfree Key') }}</label>
                                                                    <input type="text" name="cashfree_key"
                                                                        id="cashfree_key" class="form-control"
                                                                        value="{{ !isset($payment['cashfree_key']) || is_null($payment['cashfree_key']) ? '' : $payment['cashfree_key'] }}"
                                                                        placeholder="{{ __('Cashfree Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="cashfree_secret"
                                                                        class="form-label">{{ __('Cashfree Secret Key') }}</label>
                                                                    <input type="text" name="cashfree_secret"
                                                                        id="cashfree_secret" class="form-control"
                                                                        value="{{ !isset($payment['cashfree_secret']) || is_null($payment['cashfree_secret']) ? '' : $payment['cashfree_secret'] }}"
                                                                        placeholder="{{ __('Cashfree Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Aamarpay --}}
                                            <div class="accordion-item card">
                                                <h2 class="accordion-header" id="heading-2-12">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse20"
                                                        aria-expanded="true" aria-controls="collapse11">
                                                        <span class="d-flex align-items-center">

                                                            {{ __('Aamarpay') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                            <div class=" form-check form-switch custom-switch-v1">
                                                                <input type="hidden"
                                                                    name="is_aamarpay_enabled"value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_aamarpay_enabled" id="is_aamarpay_enabled"
                                                                    {{ isset($payment['is_aamarpay_enabled']) && $payment['is_aamarpay_enabled'] == 'on' ? 'checked' : '' }}>
                                                                <label class="custom-control-label form-control-label"
                                                                    for="is_aamarpay_enabled"></label>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse20" class="accordion-collapse collapse"
                                                    aria-labelledby="heading-2-3" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="aamarpay_store_id"
                                                                        class="form-label">{{ __('Store Id') }}</label>
                                                                    <input type="text" name="aamarpay_store_id"
                                                                        id="aamarpay_store_id" class="form-control"
                                                                        value="{{ !isset($payment['aamarpay_store_id']) || is_null($payment['aamarpay_store_id']) ? '' : $payment['aamarpay_store_id'] }}"
                                                                        placeholder="{{ __('Store Id') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="aamarpay_signature_key"
                                                                        class="form-label">{{ __('Signature Key') }}</label>
                                                                    <input type="text" name="aamarpay_signature_key"
                                                                        id="aamarpay_signature_key" class="form-control"
                                                                        value="{{ !isset($payment['aamarpay_signature_key']) || is_null($payment['aamarpay_signature_key']) ? '' : $payment['aamarpay_signature_key'] }}"
                                                                        placeholder="{{ __('Signature Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="aamarpay_description"
                                                                        class="form-label">{{ __('Description') }}</label>
                                                                    <input type="text" name="aamarpay_description"
                                                                        id="aamarpay_description" class="form-control"
                                                                        value="{{ !isset($payment['aamarpay_description']) || is_null($payment['aamarpay_description']) ? '' : $payment['aamarpay_description'] }}"
                                                                        placeholder="{{ __('Description') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- PayTR --}}
                                            <div class="accordion-item card">
                                                <h2 class="accordion-header" id="heading-2-13">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse21"
                                                        aria-expanded="true" aria-controls="collapse13">
                                                        <span class="d-flex align-items-center">

                                                            {{ __('Pay TR') }}
                                                        </span>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{ __('Enable') }}:</span>
                                                            <div class=" form-check form-switch custom-switch-v1">
                                                                <input type="hidden"
                                                                    name="is_paytr_enabled"value="off">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="is_paytr_enabled" id="is_paytr_enabled"
                                                                    {{ isset($payment['is_paytr_enabled']) && $payment['is_paytr_enabled'] == 'on' ? 'checked' : '' }}>
                                                                <label class="custom-control-label form-control-label"
                                                                    for="is_paytr_enabled"></label>
                                                            </div>
                                                        </div>
                                                    </button>
                                                </h2>
                                                <div id="collapse21" class="accordion-collapse collapse"
                                                    aria-labelledby="heading-2-3" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytr_merchant_id"
                                                                        class="form-label">{{ __('Merchant Id') }}</label>
                                                                    <input type="text" name="paytr_merchant_id"
                                                                        id="paytr_merchant_id" class="form-control"
                                                                        value="{{ !isset($payment['paytr_merchant_id']) || is_null($payment['paytr_merchant_id']) ? '' : $payment['paytr_merchant_id'] }}"
                                                                        placeholder="{{ __('Enter Merchant Id') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytr_merchant_key"
                                                                        class="form-label">{{ __('Merchant Key') }}</label>
                                                                    <input type="text" name="paytr_merchant_key"
                                                                        id="paytr_merchant_key" class="form-control"
                                                                        value="{{ !isset($payment['paytr_merchant_key']) || is_null($payment['paytr_merchant_key']) ? '' : $payment['paytr_merchant_key'] }}"
                                                                        placeholder="{{ __('Enter Merchant Key') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="paytr_merchant_salt"
                                                                        class="form-label">{{ __('Salt Passphrase') }}</label>
                                                                    <input type="text" name="paytr_merchant_salt"
                                                                        id="paytr_merchant_salt" class="form-control"
                                                                        value="{{ !isset($payment['paytr_merchant_salt']) || is_null($payment['paytr_merchant_salt']) ? '' : $payment['paytr_merchant_salt'] }}"
                                                                        placeholder="{{ __('Enter Salt Passphrase') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                 {{--Yookassa--}}
                                                 <div class="accordion-item card shadow-none">
                                                    <h2 class="accordion-header" id="heading-2-22">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapse22"
                                                            aria-expanded="true" aria-controls="collapse22">
                                                            <span class="d-flex align-items-center">
                                                                {{ __('Yookassa') }}
                                                            </span>

                                                            <div class="d-flex align-items-center">
                                                                <span class="me-2">{{__('Enable')}}</span>
                                                                <div class="form-check form-switch custom-switch-v1">
                                                                    <input type="hidden" name="is_yookassa_enabled"
                                                                        value="off">
                                                                    <input type="checkbox"
                                                                        class="form-check-input input-primary"
                                                                        name="is_yookassa_enabled" id="is_yookassa_enabled"
                                                                        {{ isset($payment['is_yookassa_enabled']) && $payment['is_yookassa_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="customswitchv1-2"></label>
                                                                </div>
                                                            </div>

                                                        </button>
                                                    </h2>

                                                    <div id="collapse22"
                                                        class="accordion-collapse collapse"aria-labelledby="heading-2-22"data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="yookassa_shop_id"
                                                                            class="form-label">{{ __('Shop ID Key') }}</label>
                                                                        <input type="text" name="yookassa_shop_id"
                                                                            id="yookassa_shop_id" class="form-control"
                                                                            value="{{ !isset($payment['yookassa_shop_id']) || is_null($payment['yookassa_shop_id']) ? '' : $payment['yookassa_shop_id'] }}"
                                                                            placeholder="{{ __('Shop ID Key') }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="yookassa_secret"
                                                                            class="form-label">{{ __('Secret Key') }}</label>
                                                                        <input type="text" name="yookassa_secret"
                                                                            id="yookassa_secret" class="form-control"
                                                                            value="{{ !isset($payment['yookassa_secret']) || is_null($payment['yookassa_secret']) ? '' : $payment['yookassa_secret'] }}"
                                                                            placeholder="{{ __('Secret Key') }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                               {{-- Midtrans --}}
                                               <div class="accordion-item card shadow-none">
                                                <h2 class="accordion-header" id="heading-2-23">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#collapse23"
                                                        aria-expanded="true" aria-controls="collapse23">
                                                        <span class="d-flex align-items-center">
                                                            {{ __('Midtrans') }}
                                                        </span>

                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2">{{__('Enable')}}</span>
                                                            <div class="form-check form-switch custom-switch-v1">
                                                                <input type="hidden" name="is_midtrans_enabled"
                                                                    value="off">
                                                                <input type="checkbox"
                                                                    class="form-check-input input-primary"
                                                                    name="is_midtrans_enabled" id="is_midtrans_enabled"
                                                                    {{ isset($payment['is_midtrans_enabled']) && $payment['is_midtrans_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="customswitchv1-2"></label>
                                                            </div>
                                                        </div>

                                                    </button>
                                                </h2>

                                                <div id="collapse23" class="accordion-collapse collapse"aria-labelledby="heading-2-23"data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <div class="row">
                                                            <div class="d-flex">
                                                                <div class="mr-2" style="margin-right: 15px;">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio"
                                                                                    name="midtrans_mode"
                                                                                    value="sandbox"
                                                                                    class="form-check-input"
                                                                                    {{ !isset($payment['midtrans_mode']) || $payment['midtrans_mode'] == '' || $payment['midtrans_mode'] == 'sandbox' ? 'checked="checked"' : '' }}>

                                                                                {{ __('Sandbox') }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mr-2">
                                                                    <div class="border card p-3">
                                                                        <div class="form-check">
                                                                            <label class="form-check-labe text-dark">
                                                                                <input type="radio"
                                                                                    name="midtrans_mode"
                                                                                    value="live"
                                                                                    class="form-check-input"
                                                                                    {{ isset($payment['midtrans_mode']) && $payment['midtrans_mode'] == 'live' ? 'checked="checked"' : '' }}>

                                                                                {{ __('Live') }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <br>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="midtrans_secret"
                                                                        class="form-label">{{ __('Secret Key') }}</label>
                                                                    <input type="text" name="midtrans_secret"
                                                                        id="midtrans_secret" class="form-control"
                                                                        value="{{ !isset($payment['midtrans_secret']) || is_null($payment['midtrans_secret']) ? '' : $payment['midtrans_secret'] }}"
                                                                        placeholder="{{ __('Secret Key') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                {{-- Xendit --}}
                                                <div class="accordion-item card shadow-none">
                                                    <h2 class="accordion-header" id="heading-2-24">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse" data-bs-target="#collapse24"
                                                            aria-expanded="true" aria-controls="collapse24">
                                                            <span class="d-flex align-items-center">
                                                                {{ __('Xendit') }}
                                                            </span>

                                                            <div class="d-flex align-items-center">
                                                                <span class="me-2">{{__('Enable')}}</span>
                                                                <div class="form-check form-switch custom-switch-v1">
                                                                    <input type="hidden" name="is_xendit_enabled"
                                                                        value="off">
                                                                    <input type="checkbox"
                                                                        class="form-check-input input-primary"
                                                                        name="is_xendit_enabled" id="is_xendit_enabled"
                                                                        {{ isset($payment['is_xendit_enabled']) && $payment['is_xendit_enabled'] == 'on' ? 'checked="checked"' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="customswitchv1-2"></label>
                                                                </div>
                                                            </div>

                                                        </button>
                                                    </h2>

                                                    <div id="collapse24" class="accordion-collapse collapse"aria-labelledby="heading-2-24"data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            <div class="row">

                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="xendit_api"
                                                                            class="form-label">{{ __('API Key') }}</label>
                                                                        <input type="text" name="xendit_api"
                                                                            id="xendit_api" class="form-control"
                                                                            value="{{ !isset($payment['xendit_api']) || is_null($payment['xendit_api']) ? '' : $payment['xendit_api'] }}"
                                                                            placeholder="{{ __('API Key') }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="xendit_token"
                                                                            class="form-label">{{ __('Token') }}</label>
                                                                        <input type="text" name="xendit_token"
                                                                            id="xendit_token" class="form-control"
                                                                            value="{{ !isset($payment['xendit_token']) || is_null($payment['xendit_token']) ? '' : $payment['xendit_token'] }}"
                                                                            placeholder="{{ __('Token') }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12  text-end">
                                    <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit"
                                        value="{{ __('Save Changes') }}">
                                </div>
                        </div>
                        </form>
                    </div>

                    {{-- </div> --}}

                    <div class="card" id="invoice-setting">
                        <div class="card-header">
                            <h5>{{ __('Invoice Print Settings') }}</h5>
                        </div>
                        <div class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-header card-body">
                                        <form id="setting-form" method="post"
                                            action="{{ route('template.setting') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address"
                                                    class="col-form-label">{{ __('Invoice Template') }}</label>
                                                <select class="form-control select2" name="invoice_template">
                                                    @foreach (Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{ $key }}"
                                                            {{ isset($settings['invoice_template']) && $settings['invoice_template'] == $key ? 'selected' : '' }}>
                                                            {{ $template }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('Color Input') }}</label>
                                                <div class="row gutters-xs">
                                                    @foreach (Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="invoice_color" type="radio"
                                                                    value="{{ $color }}"
                                                                    class="colorinput-input"
                                                                    {{ isset($settings['invoice_color']) && $settings['invoice_color'] == $color ? 'checked' : '' }}>
                                                                <span class="colorinput-color"
                                                                    style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('Invoice Logo') }}</label>
                                                <div class="choose-files mt-5 ">
                                                    <label for="invoice_logo">
                                                        <div class=" bg-primary"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file"
                                                            name="invoice_logo" id="invoice_logo"
                                                            data-filename="invoice_logo_update">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{ __('Save Changes') }}"
                                                    class="btn btn-print-invoice  btn-primary m-r-10">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if (isset($settings['invoice_template']) && isset($settings['invoice_color']))
                                        <iframe id="invoice_frame" class="w-100 h-1050" frameborder="0"
                                            src="{{ route('invoice.preview', [$settings['invoice_template'], $settings['invoice_color']]) }}"></iframe>
                                    @else
                                        <iframe id="invoice_frame" class="w-100 h-1050" frameborder="0"
                                            src="{{ route('invoice.preview', ['template1', 'ffffff']) }}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="estimation-setting">
                        <div class="card-header">
                            <h5>{{ __('Estimation Print Settings') }}</h5>
                        </div>
                        <div class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-header card-body">
                                        <form id="setting-form" method="post"
                                            action="{{ route('template.setting') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address"
                                                    class="col-form-label">{{ __('Estimation Template') }}</label>
                                                <select class="form-control select2" name="estimation_template">
                                                    @foreach (Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{ $key }}"
                                                            {{ isset($settings['estimation_template']) && $settings['estimation_template'] == $key ? 'selected' : '' }}>
                                                            {{ $template }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('Color Input') }}</label>
                                                <div class="row gutters-xs">
                                                    @foreach (Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="estimation_color" type="radio"
                                                                    value="{{ $color }}"
                                                                    class="colorinput-input"
                                                                    {{ isset($settings['estimation_color']) && $settings['estimation_color'] == $color ? 'checked' : '' }}>
                                                                <span class="colorinput-color"
                                                                    style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('Estimation Logo') }}</label>
                                                <div class="choose-files mt-5 ">
                                                    <label for="estimation_logo">
                                                        <div class=" bg-primary"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file"
                                                            name="estimation_logo" id="estimation_logo"
                                                            data-filename="estimation_logo_update">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{ __('Save Changes') }}"
                                                    class="btn btn-print-invoice  btn-primary m-r-10">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if (isset($settings['estimation_template']) && isset($settings['estimation_color']))
                                        <iframe id="estimation_frame" frameborder="0" class="w-100 h-1050"
                                            src="{{ route('estimations.preview', [$settings['estimation_template'], $settings['estimation_color']]) }}"></iframe>
                                    @else
                                        <iframe id="estimation_frame" frameborder="0" class="w-100 h-1050"
                                            src="{{ route('estimations.preview', ['template1', 'ffffff']) }}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="mdf-setting">
                        <div class="card-header">
                            <h5>{{ __('MDF Print Settings') }}</h5>
                        </div>
                        <div class="bg-none">
                            <div class="row company-setting">
                                <div class="col-md-3">
                                    <div class="card-header card-body">
                                        <form id="setting-form" method="post"
                                            action="{{ route('template.setting') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="address"
                                                    class="col-form-label">{{ __('MDF Template') }}</label>
                                                <select class="form-control select2" name="mdf_template">
                                                    @foreach (Utility::templateData()['templates'] as $key => $template)
                                                        <option value="{{ $key }}"
                                                            {{ isset($settings['mdf_template']) && $settings['mdf_template'] == $key ? 'selected' : '' }}>
                                                            {{ $template }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('Color Input') }}</label>
                                                <div class="row gutters-xs">
                                                    @foreach (Utility::templateData()['colors'] as $key => $color)
                                                        <div class="col-auto">
                                                            <label class="colorinput">
                                                                <input name="mdf_color" type="radio"
                                                                    value="{{ $color }}"
                                                                    class="colorinput-input"
                                                                    {{ isset($settings['mdf_color']) && $settings['mdf_color'] == $color ? 'checked' : '' }}>
                                                                <span class="colorinput-color"
                                                                    style="background: #{{ $color }}"></span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-form-label">{{ __('MDF Logo') }}</label>
                                                <div class="choose-files mt-5 ">
                                                    <label for="mdf_logo">
                                                        <div class=" bg-primary"> <i
                                                                class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                        </div>
                                                        <input type="file" class="form-control file"
                                                            name="mdf_logo" id="mdf_logo"
                                                            data-filename="mdf_logo_update">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group mt-2 text-end">
                                                <input type="submit" value="{{ __('Save Changes') }}"
                                                    class="btn btn-print-invoice  btn-primary m-r-10">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    @if (isset($settings['mdf_template']) && isset($settings['mdf_color']))
                                        <iframe id="mdf_frame" class="w-100 h-1050" frameborder="0"
                                            src="{{ route('mdf.preview', [$settings['mdf_template'], $settings['mdf_color']]) }}"></iframe>
                                    @else
                                        <iframe id="mdf_frame" class="w-100 h-1050" frameborder="0"
                                            src="{{ route('mdf.preview', ['template1', 'ffffff']) }}"></iframe>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="zoom-setting">
                        <div class="card-header">
                            <h5>{{ __('Zoom Settings') }}</h5>
                            <small class="text-dark font-weight-bold">{{ __('Edit your Zoom Settings') }}</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <form id="setting-form" method="post"
                                        action="{{ route('setting.ZoomSettings') }}">
                                        @csrf
                                        <div class="bg-none">
                                            <div class="row company-setting">
                                                <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                                    <label class="col-form-label">{{ __('Zoom Account Id') }} *</label>
                                                    <input type="text" name="zoom_account_id" class="form-control"
                                                        placeholder="Enter Zoom Account Id"
                                                        value="{{ !empty($settings['zoom_account_id']) ? $settings['zoom_account_id'] : '' }}">
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                                    <label for="site_currency_symbol"
                                                        class="col-form-label">{{ __('Zoom Client Id') }}</label>
                                                    <input type="text" name="zoom_client_id" class="form-control"
                                                        placeholder="Enter Zoom Client Id"
                                                        value="{{ !empty($settings['zoom_client_id']) ? $settings['zoom_client_id'] : '' }}">
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                                    <label for="site_currency_symbol"
                                                        class="col-form-label">{{ __('Zoom Client Secret') }}</label>
                                                    <input type="text" name="zoom_client_secret" class="form-control"
                                                        placeholder="Enter Zoom Client Secret"
                                                        value="{{ !empty($settings['zoom_client_secret']) ? $settings['zoom_client_secret'] : '' }}">
                                                </div>

                                                <div class="form-group col-md-12 text-end">
                                                    <input type="submit" id="save-btn"
                                                        value="{{ __('Save Changes') }}"
                                                        class="btn btn-print-invoice  btn-primary m-r-10">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card" id="slack-setting">
                        <div class="card-header">
                            <h5>{{ __('Slack Settings') }}</h5>
                            <small class="text-dark font-weight-bold">{{ __('Edit your Slack Settings') }}</small>
                        </div>
                        <div class="card-body">
                            {{ Form::open(['route' => 'slack.setting', 'id' => 'slack-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="small-title">{{ __('Slack Webhook URL') }}</h4>
                                    <div class="col-md-8">
                                        {{ Form::text('slack_webhook', isset($settings['slack_webhook']) ? $settings['slack_webhook'] : '', ['class' => 'form-control w-100', 'placeholder' => __('Enter Slack Webhook URL'), 'required' => 'required']) }}
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4 mb-2">
                                    <h4 class="small-title">{{ __('Module Settings') }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Lead create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('lead_notification', '1', isset($settings['lead_notification']) && $settings['lead_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'lead_notification']) }}
                                                <label class="form-check-label" for="lead_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('deal create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('deal_notification', '1', isset($settings['deal_notification']) && $settings['deal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'deal_notification']) }}
                                                <label class="form-check-label" for="deal_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Estimate create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('estimate_notification', '1', isset($settings['estimate_notification']) && $settings['estimate_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'estimate_notification']) }}
                                                <label class="form-check-label" for="estimate_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>


                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Convert lead to deal') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('leadtodeal_notification', '1', isset($settings['leadtodeal_notification']) && $settings['leadtodeal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'leadtodeal_notification']) }}
                                                <label class="form-check-label" for="leadtodeal_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Contract create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('contract_notification', '1', isset($settings['contract_notification']) && $settings['contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'contract_notification']) }}
                                                <label class="form-check-label" for="contract_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Payment create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('payment_notification', '1', isset($settings['payment_notification']) && $settings['payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'payment_notification']) }}
                                                <label class="form-check-label" for="payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>


                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Invoice create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('invoice_notification', '1', isset($settings['invoice_notification']) && $settings['invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'invoice_notification']) }}
                                                <label class="form-check-label" for="invoice_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Invoice status updated') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('invoice_status_update_notification', '1', isset($settings['invoice_status_update_notification']) && $settings['invoice_status_update_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'invoice_status_update_notification']) }}
                                                <label class="form-check-label"
                                                    for="invoice_status_update_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group col-md-12 text-end mt-4">
                                <input type="submit" id="save-btn" value="{{ __('Save Changes') }}"
                                    class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>

                    <div class="card" id="telegram-setting">
                        <div class="card-header">
                            <h5>{{ __('Telegram Settings') }}</h5>
                            <small class="text-dark font-weight-bold">{{ __('Edit your Telegram Settings') }}</small>
                        </div>
                        <div class="card-body">
                            {{ Form::open(['route' => 'telegram.setting', 'id' => 'telegram-setting', 'method' => 'post', 'class' => 'd-contents']) }}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('telegrambot', __('Telegram Access Token'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('telegrambot', isset($settings['telegrambot']) ? $settings['telegrambot'] : '', ['class' => 'form-control active telegrambot', 'placeholder' => '1234567890:AAbbbbccccddddxvGENZCi8Hd4B15M8xHV0']) }}
                                        {{-- <small>{{ __('Get Chat ID') }} :
                                            https://api.telegram.org/bot-TOKEN-/getUpdates</small> --}}
                                        @if ($errors->has('telegrambot'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('telegrambot') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('telegramchatid', __('Telegram Chat Id'), ['class' => 'col-form-label']) }}
                                        {{ Form::text('telegramchatid', isset($settings['telegramchatid']) ? $settings['telegramchatid'] : '', ['class' => 'form-control active telegramchatid', 'placeholder' => '123456789']) }}
                                        @if ($errors->has('telegramchatid'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('telegramchatid') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4 mb-2">
                                    <h4 class="small-title">{{ __('Module Settings') }}</h4>
                                </div>
                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Lead create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_lead_notification', '1', isset($settings['telegram_lead_notification']) && $settings['telegram_lead_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_lead_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_lead_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('deal create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_deal_notification', '1', isset($settings['telegram_deal_notification']) && $settings['telegram_deal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_deal_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_deal_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Estimate create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_estimate_notification', '1', isset($settings['telegram_estimate_notification']) && $settings['telegram_estimate_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_estimate_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_estimate_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Convert lead to deal') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_leadtodeal_notification', '1', isset($settings['telegram_leadtodeal_notification']) && $settings['telegram_leadtodeal_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_leadtodeal_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_leadtodeal_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Contract create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_contract_notification', '1', isset($settings['telegram_contract_notification']) && $settings['telegram_contract_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_contract_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_contract_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Payment create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_payment_notification', '1', isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_payment_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_payment_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <div class="col-md-4">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span> {{ __('Invoice create') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_invoice_notification', '1', isset($settings['telegram_invoice_notification']) && $settings['telegram_invoice_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_invoice_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_invoice_notification"></label>
                                            </div>
                                        </li>

                                        <li class="list-group-item d-flex align-items-center justify-content-between">
                                            <span>{{ __('Invoice status updated') }}</span>
                                            <div class="form-check form-switch d-inline-block float-right">
                                                {{ Form::checkbox('telegram_invoice_status_update_notification', '1', isset($settings['telegram_invoice_status_update_notification']) && $settings['telegram_invoice_status_update_notification'] == '1' ? 'checked' : '', ['class' => 'form-check-input', 'id' => 'telegram_invoice_status_update_notification']) }}
                                                <label class="form-check-label"
                                                    for="telegram_invoice_status_update_notification"></label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group col-md-12 text-end mt-4">
                                <input type="submit" id="save-btn" value="{{ __('Save Changes') }}"
                                    class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>

                    <!--Email Notification Setting-->
                    <div id="email-notification-setting" class="card">
                        {{ Form::model($settings) }}
                        @csrf
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <h5>{{ __('Email Notification Settings') }}</h5>
                                        <small
                                            class="text-dark font-weight-bold">{{ __('Edit Email Notification Settings') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    @foreach ($EmailTemplates as $EmailTemplate)
                                        <div class="col-lg-4 col-md-6 col-sm-6 form-group">
                                            <div class="list-group">
                                                <div class="list-group-item form-switch form-switch-right">
                                                    <label class="form-label"
                                                        style="margin-left:5%;">{{ $EmailTemplate->name }}</label>

                                                    <input type="checkbox"
                                                        class="form-check-input email-template-checkbox"
                                                        id="email_tempalte_{{ $EmailTemplate->template->id }}"
                                                        @if ($EmailTemplate->template->is_active == 1) checked="checked"
                                                @endcan type="checkbox" value="{{ $EmailTemplate->template->is_active }}"
                                                data-url="{{ route('status.email.language', [$EmailTemplate->template->id]) }}"/>
                                                <label class="form-check-label" for="lead_notification"></label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="card-footer p-0">
                                    <div class="col-sm-12 mt-3 px-2">
                                        <div class="text-end">
                                            <input class="btn btn-print-invoice  btn-primary " type="submit"
                                                value="{{ __('Save Changes') }}">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>



                <div class="card" id="recaptcha-setting">
                    <form method="POST" action="{{ route('recaptcha.settings.store') }}"
                        accept-charset="UTF-8">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5>{{ __('ReCaptcha settings') }}</h5>
                                    <a href="https://phppot.com/php/how-to-get-google-recaptcha-site-and-secret-key/"
                                        target="_blank" class="text-blue">
                                        <small>({{ __('How to Get Google reCaptcha Site and Secret key') }})</small>
                                    </a>
                                </div>
                                <div class="col-6 text-end">
                                    <div class="col switch-width">
                                        <div class="form-group ml-2 mb-0">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input"
                                                    data-onstyle="primary" data-toggle="switchbutton"
                                                    name="recaptcha_module" id="recaptcha_module"
                                                    value="yes"
                                                    {{ $settings['recaptcha_module'] == 'yes' ? ' checked ' : '' }}>
                                                <label class="custom-control-label form-control-label px-2"
                                                    for="recaptcha_module"></label><br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                    <label for="google_recaptcha_key"
                                        class="col-form-label">{{ __('Google Recaptcha Key') }}</label>
                                    <input class="form-control"
                                        placeholder="{{ __('Enter Google Recaptcha Key') }}"
                                        name="google_recaptcha_key" type="text"
                                        value="{{ !isset($settings['google_recaptcha_key']) || is_null($settings['google_recaptcha_key']) ? '' : $settings['google_recaptcha_key'] }}" id="google_recaptcha_key">
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 form-group">
                                    <label for="google_recaptcha_secret"
                                        class="col-form-label">{{ __('Google Recaptcha Secret') }}</label>
                                    <input class="form-control"
                                        placeholder="{{ __('Enter Google Recaptcha Secret') }}"
                                        name="google_recaptcha_secret" type="text"
                                        value="{{ !isset($settings['google_recaptcha_secret']) || is_null($settings['google_recaptcha_secret']) ? '' : $settings['google_recaptcha_secret'] }}" id="google_recaptcha_secret">
                                </div>
                            </div>
                            <div class="col-lg-12 text-end">
                                <input type="submit" value="{{ __('Save Changes') }}"
                                    class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>
                        </div>
                    </form>
                </div>

                 <!--storage Setting-->
                 <div id="storage-setting" class="card mb-3">
                    {{ Form::open(['route' => 'storage.setting.store', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-lg-10 col-md-10 col-sm-10">
                                    <h5 class="">{{ __('Storage Settings') }}</h5>
                                    <small class="text-dark font-weight-bold">{{ __('Edit your Storage Settings') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="local-outlined" autocomplete="off" {{ $setting['storage_setting'] == 'local' ? 'checked' : '' }} value="local" checked>
                                    <label class="btn btn-outline-primary" for="local-outlined">{{ __('Local') }}</label>
                                </div>
                                <div  class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="s3-outlined" autocomplete="off" {{ $setting['storage_setting'] == 's3' ? 'checked' : '' }}  value="s3">
                                    <label class="btn btn-outline-primary" for="s3-outlined"> {{ __('AWS S3') }}</label>
                                </div>

                                <div  class="pe-2">
                                    <input type="radio" class="btn-check" name="storage_setting" id="wasabi-outlined" autocomplete="off" {{ $setting['storage_setting'] == 'wasabi' ? 'checked' : '' }} value="wasabi">
                                    <label class="btn btn-outline-primary" for="wasabi-outlined">{{ __('Wasabi') }}</label>
                                </div>
                            </div>
                            <div  class="mt-2">
                                <div class="local-setting row {{ $setting['storage_setting'] == 'local' ? ' ' : 'd-none' }}">
                                    {{-- <h4 class="small-title">{{ __('Local Settings') }}</h4> --}}
                                    <div class="form-group col-8 switch-width">
                                        {{ Form::label('local_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                            <select name="local_storage_validation[]" class="form-control multi-select"  id="local_storage_validation"  multiple>
                                                @foreach ($file_type as $f)
                                                    <option @if (in_array($f, $local_storage_validations)) selected @endif>{{ $f }}
                                                    </option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="local_storage_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                        <input type="number" name="local_storage_max_upload_size"
                                            class="form-control"
                                            value="{{ !isset($setting['local_storage_max_upload_size']) || is_null($setting['local_storage_max_upload_size']) ? '' : $setting['local_storage_max_upload_size'] }}"
                                            placeholder="{{ __('Max upload size') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="s3-setting row {{ $setting['storage_setting'] == 's3' ? ' ' : 'd-none' }}">

                                <div class=" row ">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_key">{{ __('S3 Key') }}</label>
                                            <input type="text" name="s3_key" class="form-control"
                                                value="{{ !isset($setting['s3_key']) || is_null($setting['s3_key']) ? '' : $setting['s3_key'] }}"
                                                placeholder="{{ __('S3 Key') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_secret">{{ __('S3 Secret') }}</label>
                                            <input type="text" name="s3_secret" class="form-control"
                                                value="{{ !isset($setting['s3_secret']) || is_null($setting['s3_secret']) ? '' : $setting['s3_secret'] }}"
                                                placeholder="{{ __('S3 Secret') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_region">{{ __('S3 Region') }}</label>
                                            <input type="text" name="s3_region" class="form-control"
                                                value="{{ !isset($setting['s3_region']) || is_null($setting['s3_region']) ? '' : $setting['s3_region'] }}"
                                                placeholder="{{ __('S3 Region') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_bucket">{{ __('S3 Bucket') }}</label>
                                            <input type="text" name="s3_bucket" class="form-control"
                                                value="{{ !isset($setting['s3_bucket']) || is_null($setting['s3_bucket']) ? '' : $setting['s3_bucket'] }}"
                                                placeholder="{{ __('S3 Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_url">{{ __('S3 URL') }}</label>
                                            <input type="text" name="s3_url" class="form-control"
                                                value="{{ !isset($setting['s3_url']) || is_null($setting['s3_url']) ? '' : $setting['s3_url'] }}"
                                                placeholder="{{ __('S3 URL') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_endpoint">{{ __('S3 Endpoint') }}</label>
                                            <input type="text" name="s3_endpoint" class="form-control"
                                                value="{{ !isset($setting['s3_endpoint']) || is_null($setting['s3_endpoint']) ? '' : $setting['s3_endpoint'] }}"
                                                placeholder="{{ __('S3 Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="form-group col-6 switch-width">
                                        {{ Form::label('s3_storage_validation', __('Only Upload Files'), ['class' => ' form-label']) }}
                                        <select name="s3_storage_validation[]" class="form-control multi-select"
                                            id="s3_storage_validation" multiple>
                                            @foreach ($file_type as $f)
                                                <option @if (in_array($f, $s3_storage_validations)) selected @endif>
                                                    {{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_max_upload_size">{{ __('Max upload size ( In KB)') }}</label>
                                            <input type="number" name="s3_max_upload_size" class="form-control"
                                                value="{{ !isset($setting['s3_max_upload_size']) || is_null($setting['s3_max_upload_size']) ? '' : $setting['s3_max_upload_size'] }}"
                                                placeholder="{{ __('Max upload size') }}">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div
                                class="wasabi-setting row {{ $setting['storage_setting'] == 'wasabi' ? ' ' : 'd-none' }}">
                                <div class=" row ">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="s3_key">{{ __('Wasabi Key') }}</label>
                                            <input type="text" name="wasabi_key" class="form-control"
                                                value="{{ !isset($setting['wasabi_key']) || is_null($setting['wasabi_key']) ? '' : $setting['wasabi_key'] }}"
                                                placeholder="{{ __('Wasabi Key') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_secret">{{ __('Wasabi Secret') }}</label>
                                            <input type="text" name="wasabi_secret" class="form-control"
                                                value="{{ !isset($setting['wasabi_secret']) || is_null($setting['wasabi_secret']) ? '' : $setting['wasabi_secret'] }}"
                                                placeholder="{{ __('Wasabi Secret') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="s3_region">{{ __('Wasabi Region') }}</label>
                                            <input type="text" name="wasabi_region" class="form-control"
                                                value="{{ !isset($setting['wasabi_region']) || is_null($setting['wasabi_region']) ? '' : $setting['wasabi_region'] }}"
                                                placeholder="{{ __('Wasabi Region') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_bucket">{{ __('Wasabi Bucket') }}</label>
                                            <input type="text" name="wasabi_bucket" class="form-control"
                                                value="{{ !isset($setting['wasabi_bucket']) || is_null($setting['wasabi_bucket']) ? '' : $setting['wasabi_bucket'] }}"
                                                placeholder="{{ __('Wasabi Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label" for="wasabi_url">{{ __('Wasabi URL') }}</label>
                                            <input type="text" name="wasabi_url" class="form-control"
                                                value="{{ !isset($setting['wasabi_url']) || is_null($setting['wasabi_url']) ? '' : $setting['wasabi_url'] }}"
                                                placeholder="{{ __('Wasabi URL') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_root">{{ __('Wasabi Root') }}</label>
                                            <input type="text" name="wasabi_root" class="form-control"
                                                value="{{ !isset($setting['wasabi_root']) || is_null($setting['wasabi_root']) ? '' : $setting['wasabi_root'] }}"
                                                placeholder="{{ __('Wasabi Bucket') }}">
                                        </div>
                                    </div>
                                    <div class="form-group col-6 switch-width">
                                        {{ Form::label('wasabi_storage_validation', __('Only Upload Files'), ['class' => 'form-label']) }}

                                        <select name="wasabi_storage_validation[]" class="form-control multi-select"
                                            id="wasabi_storage_validation" multiple>
                                            @foreach ($file_type as $f)
                                                <option @if (in_array($f, $wasabi_storage_validations)) selected @endif>
                                                    {{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="wasabi_root">{{ __('Max upload size ( In KB)') }}</label>
                                            <input type="number" name="wasabi_max_upload_size" class="form-control"
                                                value="{{ !isset($setting['wasabi_max_upload_size']) || is_null($setting['wasabi_max_upload_size']) ? '' : $setting['wasabi_max_upload_size'] }}"
                                                placeholder="{{ __('Max upload size') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit"
                                value="{{ __('Save Changes') }}">
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>

                {{-- google-calender --}}
                <div class="card" id="google-calender">
                    {{ Form::open(['url' => route('google.calender.settings'), 'enctype' => 'multipart/form-data']) }}
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ __('Google Calendar Settings') }}</h5>
                            </div>
                            <div class="col-6 text-end">
                                <input type="checkbox" value="on" name="enable_google_calendar"
                                    id="enable_google_calendar" data-toggle="switchbutton"
                                    {{ isset($settings['enable_google_calendar']) && $settings['enable_google_calendar'] == 'on' ? 'checked' : '' }}
                                    data-onstyle="primary">
                                <label class="form-check-label" for="enable_google_calendar"></label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                {{ Form::label('Google calendar id', __('Google Calendar Id'), ['class' => 'col-form-label']) }}
                                {{ Form::text('google_clender_id', !empty($settings['google_clender_id']) ? $settings['google_clender_id'] : '', ['class' => 'form-control ', 'placeholder' => 'Google Calendar Id']) }}
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                {{ Form::label('Google calendar json file', __('Google Calendar json File'), ['class' => 'col-form-label']) }}
                                <input type="file"
                                    value="{{ !empty($settings['google_calender_json_file']) ? $settings['google_calender_json_file'] : '' }}"
                                    class="form-control" name="google_calender_json_file" id="file">
                                {{-- {{Form::text('google_calender_json_file', !empty($settings['google_calender_json_file']) ? $settings['google_calender_json_file'] : '' ,array('class'=>'form-control', 'placeholder'=>'Google Calendar json File'))}} --}}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-print-invoice  btn-primary m-r-10" type="submit">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                    {{ Form::close() }}
                </div>

                <!--seo Setting-->
                <div class="card" id="seo-setting">
                    <div class="card-header d-flex justify-content-between">
                        <h5>{{ __('SEO Settings') }}</h5>

                        @if (\Auth::user()->enable_chatgpt())
                            <div class="d-flex justify-content-end">
                                <div class="mt-0">
                                    <a data-size="md" class="btn btn-primary text-white" data-ajax-popup-over="true"
                                        data-url="{{ route('generate', ['seo']) }}" data-bs-placement="top"
                                        data-title="{{ __('Generate content with AI') }}">
                                        <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">

                        <form method="post" action="{{ route('seo.settings.store') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('meta_keywords', __('Meta Keywords'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::text('meta_keywords', Utility::getValByName('meta_keywords'), ['class' => 'form-control', 'placeholder' => __('Enter Meta Keywords Text')]) }}
                                        @error('meta_keywords')
                                            <span class="invalid-meta_keywords" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        {{ Form::label('meta_description', __('Meta Description'), ['class' => 'col-form-label text-dark']) }}
                                        {{ Form::textArea('meta_description', Utility::getValByName('meta_description'), ['class' => 'form-control', 'placeholder' => __('Enter Meta Description Text'), 'rows' => '5']) }}
                                        @error('meta_description')
                                            <span class="invalid-meta_description" role="alert">
                                                <strong class="text-danger">{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-5 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <h5 class="col-form-label text-dark">{{ __('Meta Image') }}</h5>
                                        @if (!empty($settings['meta_image']))
                                            <a href="{{ $logo . 'meta_image.png' }}" target="_blank">
                                                <img id="meta" alt="your image"
                                                    src="{{ $logo . 'meta_image.png' }}"
                                                    style="height: 216px; width: 326px;" class="big-logo img_setting">
                                            </a>
                                        @endif
                                        <div class="choose-files logo-btn"
                                            style="margin-top: 20px;margin-left: 0px; padding: 0px;">
                                            <label for="meta_image">
                                                <div class=" bg-primary"> <i
                                                        class="ti ti-upload px-1"></i>{{ __('Choose file here') }}
                                                </div>
                                                <input type="file" class="form-control file" name="meta_image"
                                                    id="meta_image" data-filename="meta_image_update"
                                                    onchange="document.getElementById('meta').src = window.URL.createObjectURL(this.files[0])">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12  text-end">
                                <input type="submit" value="{{ __('Save Changes') }}"
                                    class="btn btn-print-invoice  btn-primary m-r-10">
                            </div>

                        </form>
                    </div>
                </div>

                <!--clear Cache-->
                <div class="" id="cache-setting">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('Cache Settings') }}</h5>
                            <small
                                class="text-dark font-weight-bold">{{ __('This is a setting meant for more advanced users, simply ignore it if you dont understand what cache is.') }}</small>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('clear.cache') }}" enctype="multipart/form-data"
                                role="form">
                                @csrf
                                <div class="row">
                                    <div class="form-group">
                                        <label for="size">{{ __('Current cache size') }}</label>
                                        <div class="input-group">
                                            <input id="size" name="size" type="text" class="form-control"
                                                value={{ $file_size }} readonly="readonly">
                                            <div class="input-group-append">
                                                <span class="input-group-text">{{ __('MB') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12  text-end">
                                    <input type="submit" value="{{ __('Clear Cache') }}"
                                        class="btn btn-print-invoice  btn-primary m-r-10">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!--webhook setting-->
                <div class="card" id="webhook-setting">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <h5 class="">{{ __('Webhook Settings') }}</h5>
                                        <small class="text-dark font-weight-bold">
                                            {{ __('Edit your Webhook Settings') }}
                                        </small>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                                        <div class="btn btn-sm btn-primary btn-icon m-1">
                                            <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ __('Create Webhook') }}"
                                                data-url="{{ route('webhook.create') }}" data-size="md"
                                                data-ajax-popup="true" data-title="{{ __('Create New Webhook') }}">
                                                <i class="ti ti-plus text-white"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                {{-- <th scope="sort">{{ __('Id') }}</th> --}}
                                                <th scope="sort">{{ __('Module') }}</th>
                                                <th scope="sort">{{ __('Url') }}</th>
                                                <th scope="sort">{{ __('Method') }}</th>
                                                <th scope="sort" class="text-end">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (!empty($webhook) && count($webhook) > 0)
                                                @foreach ($webhook as $data)
                                                    <tr>
                                                        <td>{{ $data->module }}</td>
                                                        <td>{{ $data->url }}</td>
                                                        <td>{{ $data->method }}</td>
                                                        <td class="text-end">
                                                            <div class="action-btn bg-info ms-2">
                                                                <a class="mx-3 btn btn-sm  align-items-center"
                                                                    data-url="{{ route('webhook.edit', $data->id) }}"
                                                                    data-size="md" data-ajax-popup="true"
                                                                    data-title="{{ __('Edit Webhook') }}"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-original-title="{{ __('Edit Webhook') }}"
                                                                    data-bs-placement="top" class="edit-icon"
                                                                    data-original-title="{{ __('Edit') }}">
                                                                    <i class="ti ti-pencil text-white"></i>
                                                                </a>
                                                            </div>
                                                            <div class="action-btn bg-danger ms-2"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Delete') }}">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['webhook.destroy', $data->id]]) !!}
                                                                <a href="#!" class="mx-3 btn btn-sm show_confirm">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="text-center">
                                                            <i class="fas fa-user-slash text-primary fs-40"></i>
                                                            <h2>{{ __('Opps...') }}</h2>
                                                            <h6> {!! __('No Data Available...!') !!} </h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Cookie Settings-->
                <div class="card" id="cookie-settings">
                    {{ Form::model($settings, ['route' => 'cookie.setting', 'method' => 'post']) }}
                    <div
                        class="card-header flex-column flex-lg-row  d-flex align-items-lg-center gap-2 justify-content-between">
                        <h5>{{ __('Cookie Settings') }}</h5>
                        <div class="d-flex align-items-center">
                            {{ Form::label('enable_cookie', __('Enable cookie'), ['class' => 'col-form-label p-0 fw-bold me-3']) }}
                            <div class="custom-control custom-switch" onclick="enablecookie()">
                                <input type="checkbox" data-toggle="switchbutton" data-onstyle="primary"
                                    name="enable_cookie" class="form-check-input input-primary " id="enable_cookie"
                                    {{ $settings['enable_cookie'] == 'on' ? ' checked ' : '' }}>
                                <label class="custom-control-label mb-1" for="enable_cookie"></label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body cookieDiv {{ $settings['enable_cookie'] == 'off' ? 'disabledCookie ' : '' }}">
                        <div class="row">
                            <div class="text-end">
                                 @if (\Auth::user()->enable_chatgpt())
                                    <div class="mt-0">
                                        <a data-size="md" class="btn btn-primary text-white btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['cookie']) }}"
                                           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row ">
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch-v1" id="cookie_log">
                                    <input type="checkbox" name="cookie_logging"
                                        class="form-check-input input-primary cookie_setting" id="cookie_logging"
                                        onclick="enableButton()"
                                        {{ $settings['cookie_logging'] == 'on' ? ' checked ' : '' }}>
                                    <label class="form-check-label"
                                        for="cookie_logging">{{ __('Enable logging') }}</label>
                                </div>
                                <div class="form-group">
                                    {{ Form::label('cookie_title', __('Cookie Title'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('cookie_title', null, ['class' => 'form-control cookie_setting']) }}
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('cookie_description', __('Cookie Description'), ['class' => ' form-label']) }}
                                    {!! Form::textarea('cookie_description', null, ['class' => 'form-control cookie_setting', 'rows' => '3']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch-v1 ">
                                    <input type="checkbox" name="necessary_cookies"
                                        class="form-check-input input-primary" id="necessary_cookies" checked
                                        onclick="return false">
                                    <label class="form-check-label"
                                        for="necessary_cookies">{{ __('Strictly necessary cookies') }}</label>
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('strictly_cookie_title', __(' Strictly Cookie Title'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('strictly_cookie_title', null, ['class' => 'form-control cookie_setting']) }}
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('strictly_cookie_description', __('Strictly Cookie Description'), ['class' => ' form-label']) }}
                                    {!! Form::textarea('strictly_cookie_description', null, [
                                        'class' => 'form-control cookie_setting ',
                                        'rows' => '3',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <h5>{{ __('More Information') }}</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{ Form::label('more_information_description', __('Contact Us Description'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('more_information_description', null, ['class' => 'form-control cookie_setting']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{ Form::label('contactus_url', __('Contact Us URL'), ['class' => 'col-form-label']) }}
                                    {{ Form::text('contactus_url', null, ['class' => 'form-control cookie_setting']) }}
                                </div>
                            </div>

                        </div>
                    </div>
                    <div
                        class="card-footer d-flex align-items-center gap-2 flex-sm-column flex-lg-row justify-content-between">
                        <div>
                            @if (isset($settings['cookie_logging']) && $settings['cookie_logging'] == 'on')
                                <label for="file"
                                    class="form-label">{{ __('Download cookie accepted data') }}</label>
                                <a href="{{ asset(Storage::url('uploads/sample')) . '/data.csv' }}"
                                    class="btn btn-primary mr-2">
                                    <i class="ti ti-download"></i>
                                </a>
                            @endif
                        </div>
                        <input type="submit" value="{{ __('Save Changes') }}" class="btn btn-primary">
                    </div>
                    {{ Form::close() }}
                </div>

                {{-- ChatGPT --}}
                <div class="" id="pills-chatgpt-settings" role="tabpanel" aria-labelledby="pills-chatgpt-tab">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="card">
                            {{ Form::model($settings, ['route' => 'settings.chatgptkey', 'method' => 'post']) }}
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-sm-8">
                                        <h5>{{ __('ChatGPT Key Settings') }}</h5>
                                        <small
                                            class="text-dark font-weight-bold">{{ __('Edit your key details') }}</small>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-4 text-end">
                                        <input type="checkbox" value="on" name="enable_chatgpt"
                                            id="enable_chatgpt" data-toggle="switchbutton"
                                            {{ isset($settings['enable_chatgpt']) && $settings['enable_chatgpt'] == 'on' ? 'checked' : '' }}
                                            data-onstyle="primary">
                                        <label class="form-check-label" for="enable_chatgpt"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group">
                                        {{ Form::text('chatgpt_key', isset($settings['chatgpt_key']) ? $settings['chatgpt_key'] : '', ['class' => 'form-control', 'placeholder' => __('Enter ChatGPT Key Here')]) }}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-primary" type="submit">{{ __('Save Changes') }}</button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ sample-page ] end -->
    </div>
    </div>
@endsection
