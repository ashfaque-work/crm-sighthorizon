@extends('layouts.admin')

@section('title')
{{__('Report')}}
@endsection

@push('script')
<script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
<script>
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: '{{ __("Amount") }}',
                        data:  {!! json_encode($invoiceTotal) !!},

                    },
                ],

                chart: {
                    height: 300,
                    type: 'area',
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
                        text: '{{ __("Months") }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: '{{ __("Amount") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#invoicechart"), chartBarOptions);
            arChart.render();
        })();
</script>

@endpush

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">{{__('Invoice Report')}}</li>
@endsection

@section('action-button')
<div class="float-end">
                <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}"
                 data-size="md" data-title="{{__('Download')}}" onclick="saveAsPDF();"><span class="btn-inner--icon"><i class="ti ti-download"></i></span></a>
    </div>

@endsection

@section('content')
<div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                    {{ Form::open(array('route' => array('report.invoice'),'method' => 'GET','id'=>'report_invoice')) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('start_month', __('Start Month'),['class'=>'form-label']) }}
                                        {{ Form::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'', array('class' => 'month-btn form-control')) }}


                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('end_month', __('End Month'),['class'=>'form-label']) }}
                                        {{ Form::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'', array('class' => 'month-btn form-control')) }}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('deal', __('Deal'),['class'=>'form-label']) }}
                                        {{ Form::select('deal',$deal,isset($_GET['deal'])?$_GET['deal']:'', array('class' => 'form-control select')) }}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('status', __('Status'),['class'=>'form-label']) }}
                                        {{ Form::select('status', [''=>'Select Status']+$status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_invoice').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.invoice')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
</div>

<div id="printableArea">
        <div class="row mt-3">
            <div class="col">
                <input type="hidden" value="{{$filter['status'].' '.__('Invoice').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['deal']}}" id="filename">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Report')}} :</h7>
                    <h6 class="report-text mb-0">{{__('Invoice Summary')}}</h6>
                </div>
            </div>
            @if($filter['deal']!= __('All'))
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h7 class="report-text gray-text mb-0">{{__('Deal')}} :</h7>
                        <h6 class="report-text mb-0">{{$filter['deal'] }}</h6>
                    </div>
                </div>
            @endif
            @if($filter['status']!= __('All'))
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h7 class="report-text gray-text mb-0">{{__('Status')}} :</h7>
                        <h6 class="report-text mb-0">{{$filter['status'] }}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Duration')}} :</h7>
                    <h6 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-4 col-md-6 col-lg-4">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Total Invoice')}}</h7>
                    <h6 class="report-text mb-0">{{Auth::user()->priceFormat($totalInvoice)}}</h6>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-lg-4">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Total Paid')}}</h7>
                    <h6 class="report-text mb-0">{{Auth::user()->priceFormat($totalPaidInvoice)}}</h6>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 col-lg-4">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Total Due')}}</h7>
                    <h6 class="report-text mb-0">{{Auth::user()->priceFormat($totalDueInvoice)}}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12" id="invoice-container">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab1" data-bs-toggle="pill" href="#summary" role="tab" aria-controls="pills-summary" aria-selected="true">{{__('Summary')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab2" data-bs-toggle="pill" href="#invoices" role="tab" aria-controls="pills-invoice" aria-selected="false">{{__('Invoices')}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade fade" id="invoices" role="tabpanel" aria-labelledby="profile-tab2">
                                        <div class="table-responsive">
                                        <table class="table table-flush" id="report-dataTable">
                                            <thead>
                                            <tr>
                                                <th> {{__('Invoice')}}</th>
                                                <th> {{__('Date')}}</th>
                                                <th> {{__('Deal')}}</th>
                                                <th> {{__('Status')}}</th>
                                                <th> {{__('	Paid Amount')}}</th>
                                                <th> {{__('Due Amount')}}</th>
                                                <th> {{__('Amount')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($invoices as $invoice)
                                                <tr>
                                                    <td class="Id">
                                                        <a href="#" class="btn btn-outline-primary">{{ Auth::user()->invoiceNumberFormat($invoice->id) }}</a>                                                    </td>


                                                    </td>
                                                    <td>{{\Auth::user()->dateFormat($invoice->issue_date)}}</td>
                                                    <td>{{!empty($invoice->deal)? $invoice->deal->name:'-' }} </td>
                                                    <td>
                                                        @if($invoice->status == 0)
                                                        <span class="badge rounded p-2 px-3 bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                    @elseif($invoice->status == 1)
                                                        <span class="badge rounded p-2 px-3 bg-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                    @elseif($invoice->status == 2)
                                                        <span class="badge rounded p-2 px-3 bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                    @elseif($invoice->status == 3)
                                                        <span class="badge rounded p-2 px-3 bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                    @elseif($invoice->status == 4)
                                                        <span class="badge rounded p-2 px-3 bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                    @endif
                                                    </td>
                                                    <td> {{\Auth::user()->priceFormat($invoice->getTotal()-$invoice->getDue())}}</td>
                                                    <td> {{\Auth::user()->priceFormat($invoice->getDue())}}</td>
                                                    <td> {{\Auth::user()->priceFormat($invoice->getTotal())}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab1">
                                        <div class="col-sm-12">
                                            <div class="scrollbar-inner">
                                                <div id="invoicechart" data-color="primary" data-type="bar" data-height="300" ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection
@push('script')
<script  src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script src="{{ asset('js/datatable/jszip.min.js') }}"></script>
<script src="{{ asset('js/datatable/pdfmake.min.js') }}"></script>
<script src="{{ asset('js/datatable/vfs_fonts.js') }}"></script>
<script type="text/javascript">

        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            // alert('click on download');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();
        }

        $(document).ready(function () {
            var filename = $('#filename').val();
            $('#report-dataTable').DataTable({
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        title: filename
                    },
                    {
                        extend: 'pdf',
                        title: filename
                    }, {
                        extend: 'csv',
                        title: filename
                    }
                ]
            });
        });
    </script>
@endpush
