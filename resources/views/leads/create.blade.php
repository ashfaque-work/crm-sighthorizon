
    {{ Form::open(array('url' => 'leads')) }}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->enable_chatgpt())
        <div class="text-end">
            <a href="#" data-size="lg" class="btn btn-sm btn-primary btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Lead Name & Subject') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
            </a>
        </div>
        @endif
        <div class="col-6 form-group">
            {{ Form::label('subject', __('Subject'),['class'=>'col-form-label']) }}
            {{ Form::text('subject', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Subject Name')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('user_id', __('Assign Manager'),['class'=>'col-form-label']) }}
            {{ Form::select('user_id', $users,null, array('class' => 'form-control select2 multi-select','required'=>'required')) }}
            @if(count($users) == 1)
                <div class="text-muted text-xs">
                    {{__('Please create new users')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                </div>
            @endif
        </div>
        <div class="col-6 form-group">
            {{ Form::label('name', __('Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Lead Name')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('email', __('Email'),['class'=>'col-form-label']) }}
            {{ Form::text('email', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Email')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('phone', __('Phone No'),['class'=>'col-form-label']) }}
            {{ Form::number('phone', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Phone No')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}

