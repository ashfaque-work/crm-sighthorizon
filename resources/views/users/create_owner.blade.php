
<form class="pl-3 pr-3" method="post" action="{{ route('users.store') }}">
    @csrf
<div class="modal-body">
    <div class="row">
            <div class="col-6 form-group">
                <label class="col-form-label" for="name">{{ __('Name') }}</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter User Name" required/>
            </div>
            <div class="col-6 form-group">
                <label class="col-form-label" for="email">{{ __('E-Mail Address') }}</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required/>
            </div>
            <div class="col-6 form-group">
                <label class="col-form-label" for="password">{{ __('Password') }}</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required/>
            </div>
            <div class="col-6 form-group">
                <label class="col-form-label" for="job_title">{{ __('Job Title') }}</label>
                <input type="text" class="form-control" id="job_title" placeholder="Enter Job Title" name="job_title"/>
            </div>
            <div class="col-6 form-group">
                <label class="col-form-label" for="role">{{ __('Role') }}</label>
                <select name="role" class="form-control select2 multi-select" required id="role">
                    <option value="">{{__('Select Role')}}</option>
                    @foreach($roles as $role)
                        <option value="{{$role->id}}">{{$role->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @include('custom_fields.formBuilder')
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>

</div>
</form>

