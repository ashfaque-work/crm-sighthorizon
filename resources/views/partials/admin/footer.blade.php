@php
    $settings = App\Models\Utility::settings();
@endphp
@if($settings['enable_cookie'] == 'on')
    @include('layouts.cookie_consent')
@endif
<footer class="dash-footer">
  <div class="footer-wrapper">
    <div class="py-1">
        <span>&copy; {{ date('Y') }}
            {{ App\Models\Utility::getValByName('footer_text') ? App\Models\Utility::getValByName('footer_text') : config('app.name', 'Sight Horizon') }}
            </span>
    </div>
  </div>
</footer>

