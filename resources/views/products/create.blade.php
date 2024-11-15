{{ Form::open(array('url' => 'products','enctype'=>'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->enable_chatgpt())
        <div class="text-end">
            <a href="#" data-size="lg" class="btn btn-sm btn-primary btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['productservice']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Product Name & Description') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
            </a>
        </div>
        @endif
        <div class="col-6 form-group">
            {{ Form::label('name', __('Product Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Product Name')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('price', __('Price'),['class'=>'col-form-label']) }}
            {{ Form::number('price', '', array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Price')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('type', __('Type'),['class'=>'col-form-label']) }}
            {{ Form::select('type', ['Product'=>'Product','Service'=>'Service'], null, array('class'=>'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('image', __('Image'),['class'=>'col-form-label']) }}
            <div class="choose-files ">
                <label for="image">
                    <div class=" bg-primary image_update"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                    <input type="file" class="form-control file" name="image" id="image" data-filename="image_update" onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">

                    <img id="blah" src="{{asset('custom/img/news/img01.jpg')}}" alt="your image" width="100" height="100" />
                </label>
            </div>
        </div>

        <div class="col-12 form-group">
            {{ Form::label('description', __('Description'),['class'=>'col-form-label']) }}
            {{ Form::textarea('description', '', array('class' => 'form-control','placeholder'=>'Enter Description','required'=>'required')) }}
        </div>
        @include('custom_fields.formBuilder')
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}
