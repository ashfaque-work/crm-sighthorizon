
{{ Form::open(array('url' => 'expense_categories')) }}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->enable_chatgpt())
        <div class="text-end">
            <a href="#" data-size="lg" class="btn btn-sm btn-primary btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['expense_categorie']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Expense Category Name & Description') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
            </a>
        </div>
        @endif
        <div class="form-group col-12">
            {{ Form::label('name', __('Expense Category Name'),['class'=>'col-form-label']) }}
            {{ Form::text('name', '', array('class' => 'form-control','required'=> 'required','placeholder'=>'Enter Expense Category Name')) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('description', __('Description'),['class'=>'col-form-label']) }}
            {{ Form::textarea('description', '', array('class' => 'form-control','placeholder'=>'Enter Expense Category Description')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}

