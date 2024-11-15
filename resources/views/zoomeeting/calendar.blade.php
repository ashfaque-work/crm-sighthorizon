@php
$settings = App\Models\Utility::settings();
@endphp
@extends('layouts.admin')

@section('title')
    {{__('Zoom Meetings Calendar')}}
@endsection

@section('action-button')
        <a href="{{ route('zoommeeting.index') }}" class="btn btn-sm btn-primary btn-icon"  data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('List View')}}"><i class="ti ti-list"></i></a>

        @if(\Auth::user()->type=='Owner')
            <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Create Zoom Meeting')}}" data-ajax-popup="true" data-size="lg" data-title="{{__('Create Zoom Meeting')}}" data-url="{{route('zoommeeting.create')}}"><i class="ti ti-plus text-white"></i></a>
        @endif

@endsection

@push('head')
<link rel="stylesheet" href="{{asset('assets/css/plugins/main.css')}}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{__('Zoom Meetings')}}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 style="width: 150px;">{{ __('Calendar') }}</h5>
                    <input type="hidden" id="path_admin" value="{{url('/')}}">
                    @if($settings['enable_google_calendar'] == 'on')
                    <select class="form-control float-end" name="calender_type" id="calender_type"  style="float: right;width: 200px;margin-top: -30px;" onchange="get_data()">
                        <option value="goggle_calender">{{ __('Google Calendar') }}</option>
                        <option value="local_calender" selected="true">{{ __('Local Calendar') }}</option>
                    </select>
                    @endif
                </div>
                <div class="card-body">
                    <div  id='calendar' class='calendar' data-toggle="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">{{__('Meetings')}}</h4>
                    <ul class="event-cards list-group list-group-flush mt-3 w-100">
                        @foreach($current_month_event as $event)

                            @php
                                $month = date("m",strtotime($event['start_date']));
                            @endphp
                            @if($month == date('m'))
                                <li class="list-group-item card mb-3">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <div class="d-flex align-items-center">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-video"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <h6 class="  text-primary">
                                                        <a href="#" data-size="lg" data-url="{{ route('zoommeeting.show',$event->id) }}" data-ajax-popup="true" data-title="{{__('Edit Event')}}" class="text-primary">{{$event->title}}</a>
                                                    </h6>
                                                    <small class="text-muted">{{$event['start_date']}}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
<script src="{{asset('assets/js/plugins/main.min.js')}}"></script>
    <script>


$(document).ready(function()
            {
                get_data();
            });
            function get_data()
            {
                var calender_type=$('#calender_type :selected').val();
                if(calender_type==null){
                    var calender_type = 'local_calender'
                }
                $('#calendar').removeClass('local_calender');
                $('#calendar').removeClass('goggle_calender');
                $('#calendar').addClass(calender_type);
                $.ajax({
                    url: $("#path_admin").val() + "/zoommeeting/calendar",
                    method:"POST",
                    data: {"_token": "{{ csrf_token() }}",'calender_type':calender_type},
                    success: function(data) {
                     (function() {
                            var etitle;
                            var etype;
                            var etypeclass;
                            var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                                headerToolbar: {
                                    left: 'prev,next today',
                                    center: 'title',
                                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                },
                                buttonText: {
                                    timeGridDay: "{{ __('Day') }}",
                                    timeGridWeek: "{{ __('Week') }}",
                                    dayGridMonth: "{{ __('Month') }}"
                                },
                                slotLabelFormat: {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false,
                                },
                                themeSystem: 'bootstrap',
                                initialDate: '{{$transdate}}',
                                // slotDuration: '00:10:00',
                                allDaySlot:false,
                                navLinks: true,
                                droppable: true,
                                selectable: true,
                                selectMirror: true,
                                editable: false,
                                disableDragging: true,
                                dayMaxEvents: true,
                                handleWindowResize: true,
                                height: 'auto',
                                timeFormat: 'H(:mm)',
                                events: data,
                                eventStartEditable: false,
                            });
                            calendar.render();
                        })();
                    }
                });

            }



            $(document).on('click','.fc-event', function (e) {
            if ($(this).attr('href') != undefined) {
            if (!$(this).hasClass('deal')) {
                e.preventDefault();
                var event = $(this);
                var title = $(this).find('.fc-event-title').html();
                var size = 'md';
                var url = $(this).attr('href') ;

                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);

                $.ajax({
                    url: url,
                    success: function (data) {
                        $('#commonModal .body').html(data);
                        $("#commonModal").modal('show');
                    },
                    error: function (data) {
                        data = data.responseJSON;
                        show_toastr('Error', data.error, 'error')
                    }
                });
            }
        }
        });


    </script>
@endpush
