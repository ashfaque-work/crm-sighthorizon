{{-- {{ Form::model($webhook, array('route' => array('update.webhook', $webhook->id), 'method' => 'PUT')) }} --}}
{{ Form::model($webhook, ['route' => ['webhook.update', $webhook->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
            <div class="form-group">
                {{Form::label('module',__('Module'), ['class'=>'form-label'] ) }}
                {{ Form::select('module',$module, null,['class' => 'form-control select', 'id' => 'module']) }}
            </div>
            <div class="form-group">
                {{Form::label('url',__('Url'), ['class'=>'form-label'] )}}
                {{Form::text('url',null,array('class'=>'form-control','placeholder'=>__('Enter Webhook Url'),'required'=>'required'))}}
            </div>
            <div class="form-group">
                {{Form::label('method',__('Method'), ['class'=>'form-label'])}}
                {{ Form::select('method',$method, null,['class' => 'form-control select', 'id' => 'method']) }}
            </div>
    </div>
</div>
            <div class="modal-footer">
                <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="submit" class="btn  btn-primary">{{ __('Update') }}</button>
            </div>
{{Form::close()}}


