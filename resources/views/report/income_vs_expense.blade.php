@extends('layouts.admin')

@section('title')
{{__('Report')}}
@endsection

@push('script')
<script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
<script>
(function () {
            var options = {
                chart: {
                    height: 180,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{__('Income')}}",
                    data:  {!! json_encode($totalIncome) !!},

                }, {
                    name: "{{__('Expense')}}",
                    data:  {!! json_encode($totalExpense) !!},

                }],
                xaxis: {
                    categories: {!! json_encode($monthList) !!},

                },
                colors: ['#6fd944', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                // markers: {
                //     size: 4,
                //     colors:  ['#3ec9d6', '#FF3A6E',],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // }
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();
</script>  
@endpush

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">{{__('Income Vs Expenses Report')}}</li>
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
                        {{ Form::open(array('route' => array('report.income.vs.expense.summary'),'method' => 'GET','id'=>'report_inex')) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-4">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('year', __('Year'),['class'=>'form-label'])}}
                                        {{ Form::select('year',$yearlist,isset($_GET['year'])?$_GET['year']:'', array('class' => 'form-control select')) }}
                                        
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Form::label('deal', __('Deal'),['class'=>'form-label']) }}
                                        {{ Form::select('deal',$deal,isset($_GET['deal'])?$_GET['deal']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_inex').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.income.vs.expense.summary')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                <input type="hidden" value="{{' '.__('Expense Summary').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange']}}" id="filename">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Report')}} :</h7>
                    <h6 class="report-text mb-0">{{__('Income VS Expense Summary')}}</h6>
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
            <div class="col">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Duration')}} :</h7>
                    <h6 class="report-text mb-0">{{$filter['startDateRange'].' to '.$filter['endDateRange']}}</h6>
                </div>
            </div>
        </div>
     <div class="row">
             <div class="expense-container">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                            <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab1">
                                        <div class="col-sm-12">
                                            <div class="scrollbar-inner">
                                                <div id="incExpBarChart" data-color="primary" data-type="area" data-height="300" ></div>
                                            </div>
                                        </div>
                               </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
             <div class="col-12">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{{__('Type')}}</th>
                                    @foreach($monthList as $month)
                                        <th>{{$month}}</th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td>{{(__('Income'))}}</td>
                                        @foreach($invoiceTotal as $invoice)
                                            <td>{{\Auth::user()->priceFormat($invoice)}}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>{{(__('Expense'))}}</td>
                                        @foreach($expenseTotal as $expense)
                                            <td>{{\Auth::user()->priceFormat($expense)}}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td colspan="13" class="text-dark"><span>{{__('Profit = Income - Expense ')}}</span></td>
                                    </tr>
                                    <tr>
                                        <td><h6>{{(__('Profit'))}}</h6></td>
                                        @foreach($profit as $prft)
                                            <td>{{\Auth::user()->priceFormat($prft)}}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
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
<script>
     
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();

        }
</script>
@endpush