
    {{ Form::open(array('url' => 'labels')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
        {{ Form::label('name', __('Label Name'), ['class' => 'col-form-label']) }}
        {{ Form::text('name', old('name'), ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Labels Name']) }}
        @error('name')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-12">
        {{ Form::label('category', __('Category'), ['class' => 'col-form-label']) }}
        {{ Form::select('category', $category, old('category'), ['class' => 'form-control select2', 'required' => 'required']) }}
        @error('category')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-12">
        {{ Form::label('pipeline_id', __('Pipeline'), ['class' => 'col-form-label']) }}
        {{ Form::select('pipeline_id', $pipelines, old('pipeline_id'), ['class' => 'form-control select2', 'required' => 'required']) }}
        @error('pipeline_id')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-12">
        {{ Form::label('color', __('Color'), ['class' => 'col-form-label']) }}
        <div class="row gutters-xs">
            @foreach($colors as $color)
                <div class="col-auto">
                    <label class="colorinput">
                        <input name="color" type="radio" value="{{ $color }}" class="colorinput-input">
                        <span class="colorinput-color bg-{{ $color }}"></span>
                    </label>
                </div>
            @endforeach
        </div>
        @error('color')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>

    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}
