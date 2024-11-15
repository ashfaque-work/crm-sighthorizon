@php
$settings = App\Models\Utility::settings();
@endphp
{{-- <style type="text/css">
	/* Estilo iOS */
.switch__container {
  margin-top: 10px;
  width: 120px;
}

.switch {
  visibility: hidden;
  position: absolute;
  margin-left: -9999px;
}

.switch + label {
  display: block;
  position: relative;
  cursor: pointer;
  outline: none;
  user-select: none;
}

.switch--shadow + label {
  padding: 2px;
  width: 100px;
  height: 40px;
  background-color: #dddddd;
  border-radius: 60px;
}
.switch--shadow + label:before,
.switch--shadow + label:after {
  display: block;
  position: absolute;
  top: 1px;
  left: 1px;
  bottom: 1px;
  content: "";
}
.switch--shadow + label:before {
  right: 1px;
  background-color: #f1f1f1;
  border-radius: 60px;
  transition: background 0.4s;
}
.switch--shadow + label:after {
  width: 40px;
  background-color: #fff;
  border-radius: 100%;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  transition: all 0.4s;
}
.switch--shadow:checked + label:before {
  background-color: #8ce196;
}
.switch--shadow:checked + label:after {
  transform: translateX(60px);
}

</style> --}}

{{ Form::open(array('url' => 'zoommeeting')) }}
<div class="modal-body">
    <div class="tab-content tab-bordered">
        <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
            <div class="row">
                @if(\Auth::user()->enable_chatgpt())
                <div class="text-end">
                    <a href="#" data-size="lg" class="btn btn-sm btn-primary btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['zoom meeting']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Zoom Meeting Title') }}">
                        <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
                    </a>
                </div>
                @endif
                <div class="col-6 form-group">
                    {{ Form::label('title', __('Title'),['class'=>'col-form-label']) }}
                    {{ Form::text('title', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('lead_id', __('Lead'),['class'=>'col-form-label']) }}
                    {{ Form::select('lead_id', $Leads,null, array('class' => 'form-control select2 multi-select')) }}
                    @if(count($Leads) == 1)
                        <div class="text-muted text-xs">
                            {{__('Please create new Leads')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('client_id', __('Client'),['class'=>'col-form-label']) }}
                    {{ Form::select('client_id', $clients,null, array('class' => 'form-control select2 multi-select')) }}
                    @if(count($clients) == 1)
                        <div class="text-muted text-xs">
                            {{__('Please create new Clients')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('user_id', __('Users'),['class'=>'col-form-label']) }}
                    {{ Form::select('user_id', $users,null, array('class' => 'form-control select2 multi-select')) }}
                    @if(count($users) == 1)
                        <div class="text-muted text-xs">
                            {{__('Please create new users')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
                 <div class="col-6 form-group">
                    {{ Form::label('password', __('Password'),['class'=>'col-form-label']) }}
                    {{ Form::text('password', null, array('class' => 'form-control','placeholder'=>'Enter Password')) }}
                </div>

                 <div class="form-group col-md-6">
                    {{ Form::label('datetime', __('Start Date / Time'),['class'=>'col-form-label']) }}
                    <input type="datetime-local" class="form-control" id="birthdaytime" name="start_date">

                </div>
                <div class="col-6 form-group">
                    {{ Form::label('duration', __('Duration'),['class'=>'col-form-label']) }}
                    {{ Form::text('duration', null, array('class' => 'form-control','required'=>'required')) }}
                </div>
                @if ($settings['enable_google_calendar'] == 'on')
                <div class="col-4 form-check form-switch mt-5" style="margin-left:20px;" >
                    <input type="checkbox" class="form-check-input" value="1" id="is_check" name="is_check"/>
                    <label class="form-check-label f-w-600 pl-1"
                        for="is_check">{{ __('Synchronize in Google Calendar') }}</label>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}

<script>
     const d_week = new Datepicker(document.querySelector('.d_week'), {
                    buttonClass: 'btn',
                    timePicker: true,
                    singleDatePicker: true,
                    timePicker24Hour: true,
                    format: 'yyyy-mm-dd H-i-s',
                    locale: {
                            format: 'MM/DD/YYYY H:mm'
                        },
                });

</script>
