@extends('layouts.admin')

@section('title')
    {{ __('Edit Profile') }}
@endsection

@push('script')
<script>
       var scrollSpy = new bootstrap.ScrollSpy(document.body, {
        target: '#useradd-sidenav',
        offset: 300,

    })
   $(".list-group-item").click(function(){
          $('.list-group-item').filter(function(){
                return this.href == id;
        }).parent().removeClass('text-primary');
    });
</script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{__('Profile')}}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-xl-3">
        <div class="card sticky-top" style="top:30px">
            <div class="list-group list-group-flush" id="useradd-sidenav">
                <a href="#personal_info" class="list-group-item list-group-item-action">{{__('Personal Information')}} <div class="float-end"><i class="ti ti-chevron-right"></i></div></a>

                <a href="#change_password" class="list-group-item list-group-item-action">{{__('Change Password')}}<div class="float-end"><i class="ti ti-chevron-right"></i></div></a>
            </div>
        </div>
    </div>
    <div class="col-xl-9">
        <div id="personal_info" class="card">
             <div class="card-header">
                <h5>{{__('Personal Information')}}</h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{route('update.profile')}}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-6 col-sm-6">
                        <div class="form-group">
                            <label class="col-form-label text-dark">{{__('Name')}}</label>
                            <input class="form-control @error('name') is-invalid @enderror" name="name" type="text" id="name" placeholder="{{ __('Enter Your Name') }}" value="{{ $user->name }}" required autocomplete="name">
                            @error('name')
                            <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6">
                        <div class="form-group">
                            <label for="email" class="col-form-label text-dark">{{__('Email')}}</label>
                            <input class="form-control @error('email') is-invalid @enderror" name="email" type="text" id="email" placeholder="{{ __('Enter Your Email Address') }}" value="{{ $user->email }}" required autocomplete="email">
                            @error('email')
                            <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="form-group">
                            <div class="choose-files">
                                <label for="avatar">
                                    <div class=" bg-primary"> <i class="ti ti-upload px-1"></i>{{__('Choose file here')}}</div>
                                    <input type="file" name="profile" id="avatar" class="form-control file " onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" data-multiple-caption="{count} files selected" multiple/>
                                    <img id="blah" width="50%" />
                                    <!-- <input type="file" class="form-control file" name="profile" id="avatar" data-filename="profile_update"> -->
                                </label>
                            </div>
                        <span class="text-xs text-muted">{{ __('Please upload a valid image file. Size of image should not be more than 2MB.')}}</span>
                            @error('avatar')
                            <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>
                    <div class="col-lg-12 text-end">
                        <input type="submit" value="{{__('Save Changes')}}" class="btn btn-print-invoice  btn-primary m-r-10">
                    </div>
                </div>
            </form>
            @if($user->avatar!='')
                <form action="{{route('delete.avatar')}}" method="post" id="delete_avatar">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
            </div>

        </div>
        <div id="change_password" class="card">
            <div class="card-header">
                <h5>{{__('Change Password')}}</h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{route('update.password')}}">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 col-sm-6 form-group">
                            <label for="old_password" class="col-form-label text-dark">{{ __('Old Password') }}</label>
                            <input class="form-control @error('old_password') is-invalid @enderror" name="old_password" type="password" id="old_password" required autocomplete="old_password" placeholder="{{ __('Enter Old Password') }}">
                            @error('old_password')
                            <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-lg-6 col-sm-6 form-group">
                            <label for="password" class="col-form-label text-dark">{{ __('Password') }}</label>
                            <input class="form-control @error('password') is-invalid @enderror" name="password" type="password" required autocomplete="new-password" id="password" placeholder="{{ __('Enter Your Password') }}">
                            @error('password')
                            <span class="invalid-feedback text-danger text-xs" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-lg-6 col-sm-6 form-group">
                            <label for="password_confirmation" class="col-form-label text-dark">{{ __('Confirm Password') }}</label>
                            <input class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" type="password" required autocomplete="new-password" id="password_confirmation" placeholder="{{ __('Enter Your Confirm Password') }}">
                        </div>
                        <div class="col-lg-12 text-end">
                            <input type="submit" value="{{__('Change Password')}}" class="btn btn-print-invoice  btn-primary m-r-10">
                        </div>
                    </div>
                </form>
            </div>

        </div>
</div>
@endsection
