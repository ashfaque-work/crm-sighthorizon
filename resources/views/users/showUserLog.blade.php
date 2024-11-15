@php
    $request = json_decode($userlog['details'],true);
@endphp
<div class="row">
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Status')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['status']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Country')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['country']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Country Code')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['countryCode']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Region')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['region']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Region Name')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['regionName']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('City')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['city']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Zip')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['zip']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Lat')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['lat']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Lon')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['lon']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Time Zone')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['timezone']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('ISP')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['isp']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Org')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['org']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('As')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['as']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Query')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['query']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Browser Name')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['browser_name']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Os Name')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['os_name']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Browser Language')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['browser_language']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Device Type')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['device_type']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Referrer Host')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['referrer_host']}}
        </p>
    </div>
    <div class="col-md-6 ">
        <div class="form-control-label"><b>{{__('Referrer Path')}}</b></div>
        <p class="text-muted mb-4">
            {{$request['referrer_path']}}
        </p>
    </div>
</div>




