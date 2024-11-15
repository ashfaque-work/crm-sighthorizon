@extends('layouts.admin')

@section('title')
{{__('Report')}}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">{{__('Deal Report')}}</li>
@endsection

@section('content')

<div class="row">
    <!-- [ sample-page ] start -->
    <div class="col-md-12">
        <div class="p-3 card">
            <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-user-tab-1" data-bs-toggle="pill" data-bs-target="#pills-user-1" type="button">{{ __('General Report') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-user-tab-2" data-bs-toggle="pill" data-bs-target="#pills-user-2" type="button">{{ __('Staff Report') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                   <button class="nav-link" id="pills-user-tab-3" data-bs-toggle="pill" data-bs-target="#pills-user-3" type="button">{{ __('Pipeline Report') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                   <button class="nav-link" id="pills-user-tab-4" data-bs-toggle="pill" data-bs-target="#pills-user-4" type="button">{{ __('Client Report') }}</button>
                </li>
            </ul>
        </div>

            <!-- <div class="card-body"> -->
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-user-1" role="tabpanel" aria-labelledby="pills-user-tab-1">
                        <div class="row">
                            <div class="col-md-6">

                                <div class="card">
                                <div class="panel_s">
                                    <div class="panel-heading">
                                        <h5 class="panel-title" style="padding-top:15px; padding-left:20px;">{{ __('This Week Deals Conversions') }}</h5><hr>
                                    </div>
                                    <div class="panel-body">

                                        <div id="deals-this-week" height="353" style="display: block; width: 707px; height: 353px;"
                                        data-color="primary" data-height="280"></div>
                                    </div>
                                </div>
                                 </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                <div class="panel_s">
                                    <div class="panel-heading">
                                        <h5 class="panel-title"  style="padding-top:15px; padding-left:20px;">{{ __('Sources Conversion') }}</h5><hr>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="deals-sources-report" id="deals-sources-report" height="353" style="display: block; width: 600px; height: 353px;"
                                            data-color="primary" data-height="280"></div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                <div class="panel_s">
                                    <div class="panel-heading">
                                        <h5 class="panel-title"  style="padding-top:15px; padding-left:20px;">{{ __('Monthly') }}</h5><hr>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-2"  style="padding-top:15px; padding-left:30px;">
                                                <select name="month" class="form-control selectpicker" id="selectmonth" data-none-selected-text="Nothing selected" >
                                                <option value="select month">{{ __('Select Month') }}</option>
                                                    <option value="1">{{ __('January') }}</option>
                                                    <option value="2">{{ __('February') }}</option>
                                                    <option value="3">{{ __('March') }}</option>
                                                    <option value="4">{{ __('April') }}</option>
                                                    <option value="5">{{ __('May') }}</option>
                                                    <option value="6">{{ __('June') }}</option>
                                                    <option value="7">{{ __('July') }}</option>
                                                    <option value="8">{{ __('August') }}</option>
                                                    <option value="9">{{ __('September') }}</option>
                                                    <option value="10">{{ __('October') }}</option>
                                                    <option value="11">{{ __('November') }}</option>
                                                    <option value="12">{{ __('December') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="relative" style="max-height:400px;">
                                        <div id="deals-monthly" data-color="primary" data-height="280"></div>

                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-user-2" role="tabpanel" aria-labelledby="pills-user-tab-2">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                {{ Form::label('From_Date', __('From Date'),['class'=>'col-form-label']) }}
                                                {{ Form::date('From_Date',null, array('class' => 'form-control from_date','id'=>'data_picker1',)) }}
                                                <span id="fromDate" style="color: red;"></span>
                                                </div>
                                                <div class="col-md-4">
                                                {{ Form::label('To_Date', __('To Date'),['class'=>'col-form-label']) }}
                                                {{ Form::date('To_Date',null, array('class' => 'form-control to_date','id'=>'data_picker2',)) }}
                                                <span id="toDate"  style="color: red;"></span>
                                                </div>
                                                <div class="col-md-4" id="filter_type" style="padding-top : 38px;">
                                                <button  class="btn btn-primary label-margin generate_button" >{{ __('Generate') }}</button>
                                                </div>
                                        <div id="deals-staff-report"  height="400" style="display: block; width: 1293px; height: 400px;" width="1100"
                                        data-color="primary" data-height="280"></div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   <div class="tab-pane fade" id="pills-user-3" role="tabpanel" aria-labelledby="pills-user-tab-3">

                         <div class="card">
                         <div class="col-md-12">
                         <div class="panel_s">
                            <div class="card-body">
                               <div class="panel-body">
                               <h5>{{ __('Pipelines Conversion') }}</h5><hr>
                               <div class="row">
                                <div id="deals-piplines-report"  height="400" style="display: block; width: 1293px; height: 400px;" width="1100"
                              data-color="primary" data-height="280"></div>
                              </div>
                               </div>
                            </div>
                            </div>
                         </div>
                        </div>
                   </div>
                   <div class="tab-pane fade" id="pills-user-4" role="tabpanel" aria-labelledby="pills-user-tab-4">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-body">
                                         <div class="row">
                                            <div class="col-md-4">
                                            {{ Form::label('from_date', __('From Date'),['class'=>'col-form-label']) }}
                                            {{ Form::date('from_date',null, array('class' => 'form-control from_date1','id'=>'data_picker1',)) }}
                                            <span id="fromDate1" style="color: red;"></span>
                                            </div>
                                            <div class="col-md-4">
                                            {{ Form::label('to_date', __('To Date'),['class'=>'col-form-label']) }}
                                            {{ Form::date('to_date',null, array('class' => 'form-control to_date1','id'=>'data_picker2',)) }}
                                            <span id="toDate1"  style="color: red;"></span>
                                            </div>
                                            <div class="col-md-4" id="filter_type" style="padding-top : 38px;">
                                            <button  class="btn btn-primary label-margin" id="generatebtn" >{{ __('Generate') }}</button>
                                            </div>
                                    <div id="deals-clients-report"  height="400" style="display: block; width: 1293px; height: 400px;" width="1100"
                                    data-color="primary" data-height="280"></div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
            <!-- </div> -->

    </div>
    <!-- [ Main Content ] end -->
</div>

@endsection

@push('script')
<script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $("#selectmonth").change(function(){
            var selectedVal = $(this).val()
            if( selectedVal == 'select month'){
                var selectedVal = null;
            }
           $.ajax({
             url:  "{{route('report.lead')}}",
             type: "get",
             data:{
                "start_month": selectedVal,
                "_token": "{{ csrf_token() }}",
             },
             cache: false,
             success: function(data){

                $("#deals-monthly").empty();
                var chartBarOptions = {
                series: [{
                    name: 'Lead',
                    data: data.data,
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories:data.name,

                    title: {
                        text: '{{ __("Deals Per Month") }}'
                    }
                },
                colors: ['#ff3a6e', '#ff3a6e'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-monthly"), chartBarOptions);
            arChart.render();

             }
           })
        });
    });



    $(".generate_button").click(function(){
        var from_date = $('.from_date').val();
        if(from_date == ''){
            $("#fromDate").text("Please select date");
        }else{
            $("#fromDate").empty();
        }
        var to_date = $('.to_date').val();
        if(to_date == ''){
            $("#toDate").text("Please select date");
        }else{
            $("#toDate").empty();
        }
        $.ajax({
            url: "{{ route('report.deal') }}",
            type: "get",
            data: {
                "From_Date": from_date,
                "To_Date": to_date,
                "type": 'deal_staff_repport',
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {
                $("#deals-staff-report").empty();
                var chartBarOptions = {
                series: [{
                    name: 'Deal',
                    data: data.data,
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: data.name,


                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-staff-report"), chartBarOptions);
            arChart.render();
            }
        })
    });

    $("#generatebtn").click(function(){
        var from_date = $('.from_date1').val();
        if(from_date == ''){
            $("#fromDate1").text("Please select date");
        }else{
            $("#fromDate1").empty();
        }
        var to_date = $('.to_date1').val();
        if(to_date == ''){
            $("#toDate1").text("Please select date");
        }else{
            $("#toDate1").empty();
        }
        $.ajax({
            url: "{{route('report.deal')}}",
            type: "get",
            data: {
                "from_date": from_date,
                "to_date": to_date,
                "type": 'client_repport',
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {
                // console.log(data);
                $("#deals-clients-report").empty();
                var chartBarOptions = {
                series: [{
                    name: 'Deal',
                    data: data.data,
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: data.name,


                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-clients-report"), chartBarOptions);
            arChart.render();
            }
        })
    });

</script>

<script>

            var options = {
            series: {!! json_encode($devicearray['data']) !!},
            chart: {
                width: 350,
                type: 'pie',
            },

            colors: ["#35abb6","#ffa21d","#ff3a6e","#6fd943","#5c636a","#181e28","#0288d1"],
            labels: {!! json_encode($devicearray['label']) !!},


            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom',
                    }
                }
            }]
        };
        var chart = new ApexCharts(document.querySelector("#deals-this-week"), options);
        chart.render();

        (function () {
            var chartBarOptions = {
                series: [{
                    name: 'Source',
                    data: {!! json_encode($dealsourceeData) !!},
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($dealsourceName) !!},

                    title: {
                        text: '{{ __("Source") }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-sources-report"), chartBarOptions);
            arChart.render();
         })();

         (function () {
            var chartBarOptions = {
                series: [{
                    name: 'Deal',
                    data: {!! json_encode($data) !!},
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($monthList) !!},

                    title: {
                        text: '{{ __("Deal Per Month") }}'
                    }
                },
                colors: ['#ff3a6e', '#ff3a6e'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-monthly"), chartBarOptions);
            arChart.render();
         })();

         (function () {
            var chartBarOptions = {
                series: [{
                    name: 'Pipeline',
                    data: {!! json_encode($dealpipelineeData) !!},
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($dealpipelineName) !!},

                    title: {
                        text: '{{ __("Pipelines") }}'
                    }
                },
                yaxis: {
                    title: {
                        text: '{{ __("Deals") }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-piplines-report"), chartBarOptions);
            arChart.render();
         })();

         (function () {
            var chartBarOptions = {
                series: [{
                    name: 'Deal',
                    data: {!! json_encode($dealUserData) !!},
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($dealUserName) !!},


                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-staff-report"), chartBarOptions);
            arChart.render();
         })();

         (function () {
            var chartBarOptions = {
                series: [{
                    name: 'Deal',
                    data: {!! json_encode($dealClientData) !!},
                }],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($dealClientName) !!},
                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                }

            };
            var arChart = new ApexCharts(document.querySelector("#deals-clients-report"), chartBarOptions);
            arChart.render();
         })();


</script>
@endpush
