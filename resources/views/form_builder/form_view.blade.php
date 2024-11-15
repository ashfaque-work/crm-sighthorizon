@extends('layouts.auth')
@section('content')
    @php
        $logo = asset(Storage::url('logo/'));
        $dark_logo = Utility::getValByName('dark_logo');
        $favicon = Utility::getValByName('company_favicon');
        $setting = App\Models\Utility::colorset();
        $color = 'theme-3';
        if (!empty($setting['color'])) {
            $color = $setting['color'];
        }
    @endphp
    <style>
        .area {
            height: 50px;
        }
    </style>
    <div class="card">
        <div class="card-body mx-auto">
            @if ($form->is_active == 1)
                <div class="page-title">
                    <h5>{{ $form->name }}</h5>
                </div>
                <form method="POST" action="{{ route('form.view.store') }}">
                    @csrf
                    @if ($objFields && $objFields->count() > 0)
                        @foreach ($objFields as $objField)
                            @if ($objField->type == 'text')
                                <div class="form-group">
                                    {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'col-form-label']) }}
                                    {{ Form::text('field[' . $objField->id . ']', old('field.' . $objField->id), ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                </div>
                            @elseif($objField->type == 'email')
                                <div class="form-group">
                                    {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'col-form-label']) }}
                                    {{ Form::email('field[' . $objField->id . ']', old('field.' . $objField->id), ['class' => 'form-control', 'required' => 'required', 'unique' => 'response.Email', 'id' => 'field-' . $objField->id]) }}
                                </div>
                            @elseif($objField->type == 'number')
                                <div class="form-group">
                                    {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'col-form-label']) }}
                                    {{ Form::number('field[' . $objField->id . ']', old('field.' . $objField->id), ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                </div>
                            @elseif($objField->type == 'date')
                                <div class="form-group">
                                    {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'col-form-label']) }}
                                    {{ Form::date('field[' . $objField->id . ']', old('field.' . $objField->id), ['class' => 'form-control', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                </div>
                            @elseif($objField->type == 'textarea')
                                <div class="form-group">
                                    {{ Form::label('field-' . $objField->id, __($objField->name), ['class' => 'col-form-label']) }}
                                    {{ Form::textarea('field[' . $objField->id . ']', old('field.' . $objField->id), ['class' => 'form-control area', 'required' => 'required', 'id' => 'field-' . $objField->id]) }}
                                </div>
                            @endif

                        @endforeach
                        <input type="hidden" value="{{ $code }}" name="code">
                        <div class="d-grid">
                            <button class="btn btn-primary btn-block mt-2">{{ __('Submit') }}</button>
                        </div>
                    @endif
                </form>
            @else
                <div class="page-title">
                    <h5>{{ __('Form is not active.') }}</h5>
                </div>
            @endif

        </div>
    </div>
    <!-- [ auth-signup ] end -->

    <!-- Required Js -->
    <script src="{{ asset('custom/js/jquery.min.js') }}"></script>
    <script src="{{asset('custom/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor-all.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    <script>
        feather.replace();
    </script>

    <script>
        function show_toastr(title, message, type) {
            var o, i;
            var icon = '';
            var cls = '';
            if (type == 'success') {
                icon = 'fas fa-check-circle';
                // cls = 'success';
                cls = 'primary';
            } else {
                icon = 'fas fa-times-circle';
                cls = 'danger';
            }
            console.log(type,cls);
            $.notify({ icon: icon, title: " " + title, message: message, url: "" }, {
                element: "body",
                type: cls,
                allow_dismiss: !0,
                placement: {
                    from: 'top',
                    align: 'right'
                },
                offset: { x: 15, y: 15 },
                spacing: 10,
                z_index: 1080,
                delay: 2500,
                timer: 2000,
                url_target: "_blank",
                mouse_over: !1,
                animate: { enter: o, exit: i },
                // danger
                template: '<div class="toast text-white bg-'+cls+' fade show" role="alert" aria-live="assertive" aria-atomic="true">'
                        +'<div class="d-flex">'
                            +'<div class="toast-body"> '+message+' </div>'
                            +'<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" data-notify="dismiss" aria-label="Close"></button>'
                        +'</div>'
                    +'</div>'
                // template: '<div class="alert alert-{0} alert-icon alert-group alert-notify" data-notify="container" role="alert"><div class="alert-group-prepend alert-content"><span class="alert-group-icon"><i data-notify="icon"></i></span></div><div class="alert-content"><strong data-notify="title">{1}</strong><div data-notify="message">{2}</div></div><button type="button" class="close"  aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
            });
        }
    </script>
    <script>
        var toster_pos="{{'right'}}";
    </script>

    @if ($message = Session::get('success'))
    <script>show_toastr('Success', '{!! $message !!}', 'success')</script>
    @endif

    @if ($message = Session::get('error'))
    <script>show_toastr('Error', '{!! $message !!}', 'error')</script>
    @endif

    @if ($message = Session::get('info'))
    <script>show_toastr('Info', '{!! $message !!}', 'info')</script>
    @endif



    @stack('script')

    <script>
        a = document.getElementById('toastPlacement');
        a && document.getElementById('selectToastPlacement').addEventListener('change', function () {
        a.dataset.originalClass || (a.dataset.originalClass = a.className),
        a.className = a.dataset.originalClass + ' ' + this.value
        });
        
        
        d = document.getElementById('liveToastBtn'),
        f = document.getElementById('liveToast'),
        d && d.addEventListener('click', function () {
        var a = new bootstrap.Toast(f);
        a.show()
        });
    </script>


@endsection