
{{ Form::open(array('url' => 'mdf_sub_type')) }}
<div class="modal-body">
     <div class="row">
        <div class="form-group col-12">
            {{ Form::label('MDF Type', __('MDF Type'),['class'=>'col-form-label']) }}
            {{ Form::select('mdf_type',$mdfTypes ,'', array('class' => 'form-control select2','required'=>'required')) }}
            @if(count($mdfTypes) == 0)
                <div class="text-muted text-info text-xs">
                    {{__('Please create new MDF Type')}} <a href="{{route('mdf_type.index')}}">{{__('here')}}</a>.
                </div>
            @endif
        </div>
        <div class="form-group col-12">
            {{ Form::label('name', __('Sub Type Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', '', array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Sub Type Name')) }}
        </div>
     </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}

