{{ Form::model($invoice, array('route' => array('invoices.update', $invoice->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->enable_chatgpt())
        <div class="text-end">
            <a href="#" data-size="lg" class="btn btn-sm btn-primary btn-icon" data-ajax-popup-over="true" data-url="{{ route('generate',['invoice']) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate invoice terms') }}">
                <i class="fas fa-robot"></i>{{ __(' Generate with AI') }}
            </a>
        </div>
        @endif
        <div class="col-6 form-group">
            {{ Form::label('deal_id', __('Deal'),['class'=>'col-form-label']) }}
            {{ Form::select('deal_id', $deals,null, array('class' => 'form-control select2 multi-select','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('status', __('Status'),['class'=>'col-form-label']) }}
            {{ Form::select('status', \App\Models\Invoice::$statues,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('issue_date', __('Issue Date'),['class'=>'col-form-label']) }}
            {{ Form::date('issue_date',null, array('class' => 'form-control','id'=>'data_picker1','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('due_date', __('Due Date'),['class'=>'col-form-label']) }}
            {{ Form::date('due_date',null, array('class' => 'form-control','id'=>'data_picker2','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('discount', __('Discount'),['class'=>'col-form-label']) }}
            {{ Form::number('discount',null, array('class' => 'form-control','required'=>'required','min'=>"0")) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('tax_id', __('Tax %'),['class'=>'col-form-label']) }}
            {{ Form::select('tax_id', $taxes,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Form::label('terms', __('Terms'),['class'=>'col-form-label']) }}
            {{ Form::textarea('terms',null, array('class' => 'form-control')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>

</div>

{{ Form::close() }}

