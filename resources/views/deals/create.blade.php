
{{ Form::open(array('url' => 'deals')) }}
<div class="modal-body">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#tab-1" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Deal Detail')}}</a>
        </li>
        @if(\Auth::user()->enable_chatgpt())
        <div class="nav-item" style="margin-left: 490px">
            <a href="#!" data-size="lg" class="nav-link active" data-ajax-popup-over="true" data-url="{{ route('generate',['deal']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Deal Name') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
            </a>
        </div>
        @endif
        @if(!$customFields->isEmpty())
            <li class="nav-item">
                <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#tab-2" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('Custom Fields')}}</a>
            </li>
        @endif

    </ul>
    <div class="tab-content tab-bordered">
        <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
            <div class="row">
                <div class="col-6 form-group">
                    {{ Form::label('name', __('Deal Name'),['class'=>'col-form-label']) }}
                    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Deal Name')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('price', __('Price'),['class'=>'col-form-label']) }}
                    {{ Form::number('price', 0, array('class' => 'form-control','min'=>0)) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('phone', __('Phone No'),['class'=>'col-form-label']) }}
                    {{ Form::text('phone', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Phone no')) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('company_id', __('Clients'),['class'=>'col-form-label']) }}
                    {{ Form::select('clients[]', $clients,null, array('class' => 'form-control multi-select','id'=>'choices-multiple','multiple'=>'','required'=>'required')) }}

                    @if(count($clients) <= 0 && Auth::user()->type == 'Owner')
                        <div class="text-muted text-xs">
                            {{__('Please create new clients')}} <a href="{{route('clients.index')}}">{{__('here')}}</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if(!$customFields->isEmpty())
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                <div class="row">
                    @include('custom_fields.formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}

