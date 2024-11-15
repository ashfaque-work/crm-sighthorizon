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
                        name: '{{ __("Expense") }}',
                        data:  {!! json_encode($expenseTotal) !!},

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
                colors: ['#ff3a6e', '#ff3a6e'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                yaxis: {
                    title: {
                        text: '{{ __("Expense") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#expensechart"), chartBarOptions);
            arChart.render();
        })();
</script>

@endpush

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">{{__('Expense Report')}}</li>
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
                    {{ Form::open(array('route' => array('report.expenses'),'method' => 'GET','id'=>'report_expense')) }}

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
                                                {{ Form::label('category', __('Category'),['class'=>'form-label']) }}
                                                {{ Form::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_expense').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.expenses')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                <input type="hidden" value="{{$filter['category'].' '.__('Expense').' '.'Report of'.' '.$filter['startDateRange'].' to '.$filter['endDateRange'].' '.__('of').' '.$filter['deal']}}" id="filename">
                <div class="card p-4 mb-4">
                    <h7 class="report-text gray-text mb-0">{{__('Report')}} :</h7>
                    <h6 class="report-text mb-0">{{__('Expense Summary')}}</h6>
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
            @if($filter['category']!= __('All'))
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h7 class="report-text gray-text mb-0">{{__('Category')}} :</h7>
                        <h6 class="report-text mb-0">{{$filter['category'] }}</h6>
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
                    <h7 class="report-text gray-text mb-0">{{__('Total Expense')}}</h7>
                    <h6 class="report-text mb-0">{{Auth::user()->priceFormat($totalExpense)}}</h6>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-12" id="expense-container">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab1" data-bs-toggle="pill" href="#summary" role="tab" aria-controls="pills-summary" aria-selected="true">{{__('Summary')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab2" data-bs-toggle="pill" href="#expenses" role="tab" aria-controls="pills-expense" aria-selected="false">{{__('Expenses')}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade fade" id="expenses" role="tabpanel" aria-labelledby="profile-tab2">
                                        <div class="table-responsive">
                                        <table class="table table-flush" id="report-dataTable">
                                            <thead>
                                            <tr>
                                                <th> {{__('Category')}}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th> {{__('Date')}}</th>
                                                <th> {{__('Deal')}}</th>
                                                <th>{{ __('User') }}</th>
                                                <th width="100px">{{ __('Attachment') }}</th>
                                            </thead>
                                            <tbody>
                                            @foreach ($expenses as $expense)
                                                <tr>
                                                    {{-- <td class="Id">
                                                        <a href="#" class="btn btn-outline-primary">{{ Auth::user()->invoiceNumberFormat($expense->id) }}</a>                                                    </td>

                                                    </td> --}}
                                                    <td>{{!empty($expense->category)?$expense->category->name:'-'}}</td>
                                                    <td>{{!empty($expense->description)?$expense->description:'-'}}</td>
                                                    <td>{{ Auth::user()->priceFormat($expense->amount) }}</td>
                                                    <td>{{\Auth::user()->dateFormat($expense->date)}}</td>
                                                    <td>{{!empty($expense->deal)? $expense->deal->name:'-' }} </td>
                                                    <td>{{!empty($expense->user)?$expense->user->name:'-' }}</td>
                                                    <td>
                                                        @if ($expense->attachment)
                                                            {{-- <a href="{{asset(Storage::url('attachment/'.$expense->attachment))}}" download="" class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip" data-original-title="{{__('Download')}}"><i class="fas fa-download"></i> <span>{{__('Download')}}</span></a> --}}
                                                            @php
                                                                $attachments = \App\Models\Utility::get_file('');

                                                            @endphp
                                                            <a href="{{ $attachments . $expense->attachment }}" download=""
                                                                class="btn btn-outline-primary btn-sm mr-1" data-toggle="tooltip"
                                                                data-original-title="{{ __('Download') }}"><i class="fas fa-download"></i>
                                                                <span>{{ __('Download') }}</span>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab1">
                                        <div class="col-sm-12">
                                            <div class="scrollbar-inner">
                                                <div id="expensechart" data-color="primary" data-type="bar" data-height="300" ></div>
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
