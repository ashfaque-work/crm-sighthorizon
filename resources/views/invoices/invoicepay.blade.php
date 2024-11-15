@extends('layouts.invoicepayheader')

@section('title')
    {{ __('Invoice') }} {{ '(' . $invoice->deal->name . ')' }}
@endsection

@section('action-button')
    <div class="row align-items-center m-1">

        <div class="col-auto pe-0">
            <a href="{{ route('invoice.download.pdf', \Crypt::encrypt($invoice->id)) }}"
                class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Print Invoice') }}" target="_blanks"><i class="ti ti-printer text-white"></i></a>
        </div>


        @if ($invoice->getDue() > 0)
            <div class="col-auto pe-0">

                <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="modal"
                    data-bs-target="#paymentModal" title="{{ __('Add Payment') }}"
                    data-bs-whatever="{{ __('add Payment') }}" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('add Payment') }}">
                    <i class="ti ti-credit-card text-white"></i>
                </a>

            </div>
        @endif

    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">{{ __('Invoice') }}</li>
@endsection


@section('content')


    <div class="row">
        <!-- [ Invoice ] start -->
        <div class="container">

            <div class="card">
                <div class="card-body">
                    <div class="row ">
                        <div class="col-md-4">
                            <div class="invoice-contact">
                                <div class="invoice-box row">
                                    <div class="col-sm-12">
                                        <h5>{{ __('From') }} :</h5>
                                        <table class="">
                                            <tbody>
                                                <tr>
                                                    <th>{{ $company_setting['company_name'] }}</th>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_address'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_city'] }},</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_state'] }} -
                                                        {{ $company_setting['company_zipcode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_country'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ $company_setting['company_telephone'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-md-3 invoice-client-info">
                            <div class="invoice-contact">
                                <div class="invoice-box row">
                                    {{-- <h5>{{ __('To') }}:</h5> --}}
                                    @if ($client)
                                        <h6 class="m-b-20">{{ __('To') }} :</h6>
                                        <p class="m-0 m-t-10">{{ $client->name }}</p>
                                        <p class="m-0 m-t-10">{{ $client->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>


                        <div class="col-md-3  invoice-client-info">
                            <div class="invoice-contact">

                                <div class="col-sm-12">
                                    <h5>{{ __('Description') }} :</h5>
                                    <table class="">
                                        <tbody>

                                            <tr>
                                                <th>{{ __('Issue Date') }} :</th>
                                                @php
                                                    $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                                @endphp
                                                <td>{{ $user->dateFormat($invoice->issue_date) }}</td>

                                            </tr>
                                            <tr>
                                                <th>{{ __('Due Date') }} :</th>
                                                @php
                                                    $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                                @endphp
                                                <td>{{ $user->dateFormat($invoice->due_date) }}</td>
                                                {{-- <td>{{ Auth::user()->dateFormat($invoice->due_date) }}</td> --}}
                                            </tr>
                                            <tr>
                                                <div>
                                                    <th>{{ __('Status') }} :</th>
                                                    @if ($invoice->status == 0)
                                                        <td>
                                                            <span
                                                                class="badge rounded p-2 px-3 bg-primary">{{ __(\App\Models\Invoice::$statues[0]) }}</span>
                                                        </td>
                                                    @elseif($invoice->status == 1)
                                                        <td>
                                                            <span
                                                                class="badge rounded p-2 px-3 bg-danger">{{ __(\App\Models\Invoice::$statues[1]) }}</span>
                                                        </td>
                                                    @elseif($invoice->status == 2)
                                                        <td>
                                                            <span
                                                                class="badge rounded p-2 px-3 bg-warning">{{ __(\App\Models\Invoice::$statues[2]) }}</span>
                                                        </td>
                                                    @elseif($invoice->status == 3)
                                                        <td>
                                                            <span
                                                                class="badge rounded p-2 px-3 bg-success">{{ __(\App\Models\Invoice::$statues[3]) }}</span>
                                                        </td>
                                                    @elseif($invoice->status == 4)
                                                        <td>
                                                            <span
                                                                class="badge rounded p-2 px-3 bg-info">{{ __(\App\Models\Invoice::$statues[4]) }}</span>
                                                        </td>
                                                    @endif
                                                </div>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2  mt-3 qr_code">
                            <table class="table table-responsive invoice-table table-borderless">
                                <tbody>
                                    <tr>
                                        @php
                                            $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                        @endphp
                                        <td>
                                            <h4>{{ $user->invoiceNumberFormat($invoice->invoice_id) }}</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{!! DNS2D::getBarcodeHTML(
                                            route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
                                            'QRCODE',
                                            2,
                                            2,
                                        ) !!}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                    </div>

                    <div class="row mb-5">
                        <div class="col-md-12">
                            <h4 class="h4 font-weight-400 float-left">{{ __('Item List') }}</h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table invoice-detail-table">
                                    <thead>
                                        <tr class="thead-default">
                                            <th>{{ __('product name') }}</th>
                                            <th>{{ __('total Quantity') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th class="text-center">{{ __('Price') }}</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalQuantity = 0;
                                            $totalRate = 0;
                                            $totalAmount = 0;
                                            $totalTaxPrice = 0;
                                            $totalDiscount = 0;
                                            $taxesData = [];
                                        @endphp

                                        @foreach ($invoice->getProducts as $invoiceitem)
                                            @php

                                                $taxes = \Utility::tax($invoiceitem->tax);
                                                $totalQuantity += $invoiceitem->quantity;
                                                $totalRate += $invoiceitem->price;
                                                $totalDiscount += $invoiceitem->discount;

                                            @endphp
                                            <tr>
                                                <td>{{ $invoiceitem->name }} </td>

                                                <td>{{ $invoiceitem->pivot->quantity }}</td>
                                                <td>{{ $users->priceFormat($invoiceitem->pivot->price) }} </td>


                                                <td>{{ $invoiceitem->description }} </td>
                                                @php
                                                    $price = $invoiceitem->pivot->price * $invoiceitem->pivot->quantity;
                                                @endphp
                                                <td class="text-center">{{ $users->priceFormat($price) }}</td>

                                                @php
                                                    $totalQuantity += $invoiceitem->quantity;
                                                    $totalRate += $invoiceitem->price;
                                                    $totalDiscount += $invoiceitem->discount;
                                                    $totalAmount += $invoiceitem->price * $invoiceitem->quantity;
                                                @endphp
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="invoice-total">
                                <table class="table invoice-table ">
                                    <tbody>
                                        <tr>

                                            <th>{{ __('Sub Total') }} :</th>
                                            <td>{{ $users->priceFormat($invoice->getSubTotal()) }}</td>
                                        </tr>
                                        @if (!empty($taxesData))
                                            @foreach ($taxesData as $taxName => $taxPrice)
                                                @if ($taxName != 'No Tax')
                                                    <tr>
                                                        <th>{{ $taxName }}</th>
                                                        <td>{{ $users->priceFormat($taxPrice) }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif

                                        <tr>
                                            <td>
                                                <h5 class="text-primary m-r-10">{{ __('Total') }} :</h5>
                                            </td>
                                            <td>
                                                <h5 class="text-primary">{{ $users->priceFormat($invoice->getTotal()) }}
                                                </h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>

                                                <h5 class="text-primary m-r-10">{{ __('Due Amount') }} :</h5>
                                            </td>
                                            <td>

                                                <h5 class="text-primary">
                                                    {{ $user->priceFormat($invoice->getDue()) }}</h5>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!-- [ Invoice ] end -->
    </div>

    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 pl-0">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Payment History') }}</h5>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Transaction ID') }}</th>
                                <th>{{ __('Payment Date') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Payment Type') }}</th>
                                <th>{{ __('Note') }}</th>
                                <th>{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i=0; @endphp
                            @if ($invoice->payments->count() || $bankpayment->count())
                                @foreach ($invoice->payments as $payment)
                                    <tr>
                                        <td>
                                            {{ sprintf('%05d', $payment->transaction_id) }}
                                        </td>
                                        <td>
                                            @php
                                                $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                            @endphp
                                            {{ $user->dateFormat($payment->date) }}

                                        </td>
                                        <td>
                                            {{ !empty($payment->payment) ? $payment->payment->name : '-' }}
                                        </td>
                                        <td>
                                            {{ $payment->payment_type }}
                                        </td>
                                        <td>
                                            {{ $payment->notes }}
                                        </td>
                                        <td>
                                            @php
                                                $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                            @endphp
                                            {{ $user->priceFormat($payment->amount) }}
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach ($bankpayment as $payment)
                                    @if ($payment->status == 'Pending')
                                        <tr>
                                            <td>-</td>
                                            <td>
                                                @php
                                                    $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                                @endphp
                                                {{ $user->dateFormat($payment->date) }}

                                            </td>
                                            <td>-</td>
                                            <td>
                                                {{ __('Bank Transfer') }}
                                            </td>
                                            <td>-</td>
                                            <td class="text-right">
                                                @php
                                                    $user = \App\Models\User::where('id', $invoice->created_by)->first();
                                                @endphp
                                                {{ $user->priceFormat($payment->amount) }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No Data Found!') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($invoice->getDue() > 0)
        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">

                            @if (isset($payment_setting['is_banktransfer_enabled']) && $payment_setting['is_banktransfer_enabled'] == 'on')
                                @if (isset($payment_setting['bank_details']) && !empty($payment_setting['bank_details']))
                                    <li class="nav-item">
                                        <a class="nav-link active" id="pills-banktransfer-tab" data-bs-toggle="pill"
                                            href="#banktransfer-payment" role="tab" aria-controls="banktransfer"
                                            aria-selected="true">{{ __('Bank Transfer') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_stripe_enabled']) && $payment_setting['is_stripe_enabled'] == 'on')
                                @if (isset($payment_setting['stripe_key']) &&
                                        !empty($payment_setting['stripe_key']) &&
                                        (isset($payment_setting['stripe_secret']) && !empty($payment_setting['stripe_secret'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-home-tab" data-bs-toggle="pill"
                                            href="#stripe-payment" role="tab" aria-controls="pills-home"
                                            aria-selected="false">{{ __('Stripe') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_paypal_enabled']) && $payment_setting['is_paypal_enabled'] == 'on')
                                @if (isset($payment_setting['paypal_client_id']) &&
                                        !empty($payment_setting['paypal_client_id']) &&
                                        (isset($payment_setting['paypal_secret_key']) && !empty($payment_setting['paypal_secret_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paypal-tab" data-bs-toggle="pill"
                                            href="#paypal-payment" role="tab" aria-controls="paypal"
                                            aria-selected="false">{{ __('Paypal') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_paystack_enabled']) && $payment_setting['is_paystack_enabled'] == 'on')
                                @if (isset($payment_setting['paystack_public_key']) &&
                                        !empty($payment_setting['paystack_public_key']) &&
                                        (isset($payment_setting['paystack_secret_key']) && !empty($payment_setting['paystack_secret_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paystack-tab" data-bs-toggle="pill"
                                            href="#paystack-payment" role="tab" aria-controls="paystack"
                                            aria-selected="false">{{ __('Paystack') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_flutterwave_enabled']) && $payment_setting['is_flutterwave_enabled'] == 'on')
                                @if (isset($payment_setting['flutterwave_secret_key']) &&
                                        !empty($payment_setting['flutterwave_secret_key']) &&
                                        (isset($payment_setting['flutterwave_public_key']) && !empty($payment_setting['flutterwave_public_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-flutterwave-tab" data-bs-toggle="pill"
                                            href="#flutterwave-payment" role="tab" aria-controls="flutterwave"
                                            aria-selected="false">{{ __('Flutterwave') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_razorpay_enabled']) && $payment_setting['is_razorpay_enabled'] == 'on')
                                @if (isset($payment_setting['razorpay_public_key']) &&
                                        !empty($payment_setting['razorpay_public_key']) &&
                                        (isset($payment_setting['razorpay_secret_key']) && !empty($payment_setting['razorpay_secret_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-razorpay-tab" data-bs-toggle="pill"
                                            href="#razorpay-payment" role="tab" aria-controls="razorpay"
                                            aria-selected="false">{{ __('Razorpay') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_mercado_enabled']) && $payment_setting['is_mercado_enabled'] == 'on')
                                @if (isset($payment_setting['mercado_access_token']) && !empty($payment_setting['mercado_access_token']))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-mercado-tab" data-bs-toggle="pill"
                                            href="#mercado-payment" role="tab" aria-controls="mercado"
                                            aria-selected="false">{{ __('Mercado Pago') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_paytm_enabled']) && $payment_setting['is_paytm_enabled'] == 'on')
                                @if (isset($payment_setting['paytm_merchant_id']) &&
                                        !empty($payment_setting['paytm_merchant_id']) &&
                                        (isset($payment_setting['paytm_merchant_key']) && !empty($payment_setting['paytm_merchant_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paytm-tab" data-bs-toggle="pill"
                                            href="#paytm-payment" role="tab" aria-controls="paytm"
                                            aria-selected="false">{{ __('Paytm') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_mollie_enabled']) && $payment_setting['is_mollie_enabled'] == 'on')
                                @if (isset($payment_setting['mollie_api_key']) &&
                                        !empty($payment_setting['mollie_api_key']) &&
                                        (isset($payment_setting['mollie_profile_id']) && !empty($payment_setting['mollie_profile_id'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-mollie-tab" data-bs-toggle="pill"
                                            href="#mollie-payment" role="tab" aria-controls="mollie"
                                            aria-selected="false">{{ __('Mollie') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_skrill_enabled']) && $payment_setting['is_skrill_enabled'] == 'on')
                                @if (isset($payment_setting['skrill_email']) && !empty($payment_setting['skrill_email']))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-skrill-tab" data-bs-toggle="pill"
                                            href="#skrill-payment" role="tab" aria-controls="skrill"
                                            aria-selected="false">{{ __('Skrill') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_coingate_enabled']) && $payment_setting['is_coingate_enabled'] == 'on')
                                @if (isset($payment_setting['coingate_auth_token']) && !empty($payment_setting['coingate_auth_token']))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-coingate-tab" data-bs-toggle="pill"
                                            href="#coingate-payment" role="tab" aria-controls="coingate"
                                            aria-selected="false">{{ __('CoinGate') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paymentwall_enabled']) && $payment_setting['is_paymentwall_enabled'] == 'on')
                                @if (isset($payment_setting['paymentwall_public_key']) &&
                                        !empty($payment_setting['paymentwall_public_key']) &&
                                        (isset($payment_setting['paymentwall_private_key']) && !empty($payment_setting['paymentwall_private_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paymentwall-tab" data-bs-toggle="pill"
                                            href="#paymentwall-payment" role="tab" aria-controls="paymentwall"
                                            aria-selected="false">{{ __('PaymentWall') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_toyyibpay_enabled']) && $payment_setting['is_toyyibpay_enabled'] == 'on')
                                @if (isset($payment_setting['toyyibpay_secret_key']) &&
                                        !empty($payment_setting['toyyibpay_secret_key']) &&
                                        (isset($payment_setting['category_code']) && !empty($payment_setting['category_code'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-toyyibpay-tab" data-bs-toggle="pill"
                                            href="#toyyibpay-payment" role="tab" aria-controls="toyyibpay"
                                            aria-selected="false">{{ __('Toyyibpay') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_payfast_enabled']) && $payment_setting['is_payfast_enabled'] == 'on')
                                @if (isset($payment_setting['payfast_merchant_id']) &&
                                        !empty($payment_setting['payfast_merchant_id']) &&
                                        (isset($payment_setting['payfast_merchant_key']) && !empty($payment_setting['payfast_merchant_key'])) &&
                                        (isset($payment_setting['payfast_signature']) && !empty($payment_setting['payfast_signature'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-payfast-tab" data-bs-toggle="pill"
                                            href="#payfast-payment" role="tab" aria-controls="payfast"
                                            aria-selected="false">{{ __('PayFast') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_iyzipay_enabled']) && $payment_setting['is_iyzipay_enabled'] == 'on')
                                @if (isset($payment_setting['iyzipay_public_key']) &&
                                        !empty($payment_setting['iyzipay_public_key']) &&
                                        (isset($payment_setting['iyzipay_secret_key']) && !empty($payment_setting['iyzipay_secret_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-iyzipay-tab" data-bs-toggle="pill"
                                            href="#iyzipay-payment" role="tab" aria-controls="iyzipay"
                                            aria-selected="false">{{ __('IyziPay') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_sspay_enabled']) && $payment_setting['is_sspay_enabled'] == 'on')
                                @if (isset($payment_setting['sspay_secret_key']) &&
                                        !empty($payment_setting['sspay_secret_key']) &&
                                        (isset($payment_setting['sspay_category_code']) && !empty($payment_setting['sspay_category_code'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-sspay-tab" data-bs-toggle="pill"
                                            href="#sspay-payment" role="tab" aria-controls="sspay"
                                            aria-selected="false">{{ __('SSPay') }}</a>
                                    </li>
                                @endif
                            @endif


                            @if (isset($payment_setting['is_paytab_enabled']) && $payment_setting['is_paytab_enabled'] == 'on')
                                @if (isset($payment_setting['paytab_profile_id']) &&
                                        !empty($payment_setting['paytab_profile_id']) &&
                                        (isset($payment_setting['paytab_region']) && !empty($payment_setting['paytab_region'])) &&
                                        (isset($payment_setting['paytab_server_key']) && !empty($payment_setting['paytab_server_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paytab-tab" data-bs-toggle="pill"
                                            href="#paytab-payment" role="tab" aria-controls="paytab"
                                            aria-selected="false">{{ __('Paytab') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_benefit_enabled']) && $payment_setting['is_benefit_enabled'] == 'on')
                                @if (isset($payment_setting['benefit_api_key']) &&
                                        !empty($payment_setting['benefit_api_key']) &&
                                        (isset($payment_setting['benefit_secret_key']) && !empty($payment_setting['benefit_secret_key'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-benefit-tab" data-bs-toggle="pill"
                                            href="#benefit-payment" role="tab" aria-controls="benefit"
                                            aria-selected="false">{{ __('Benefit') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_cashefree_enabled']) && $payment_setting['is_cashefree_enabled'] == 'on')
                                @if (isset($payment_setting['cashfree_key']) &&
                                        !empty($payment_setting['cashfree_key']) &&
                                        (isset($payment_setting['cashfree_secret']) && !empty($payment_setting['cashfree_secret'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-benefit-tab" data-bs-toggle="pill"
                                            href="#cashefree-payment" role="tab" aria-controls="cashefree"
                                            aria-selected="false">{{ __('Cashfree') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_aamarpay_enabled']) && $payment_setting['is_aamarpay_enabled'] == 'on')
                                @if (isset($payment_setting['aamarpay_store_id']) &&
                                        !empty($payment_setting['aamarpay_store_id']) &&
                                        (isset($payment_setting['aamarpay_signature_key']) && !empty($payment_setting['aamarpay_signature_key'])) &&
                                        (isset($payment_setting['aamarpay_description']) && !empty($payment_setting['aamarpay_description'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-aamarpay-tab" data-bs-toggle="pill"
                                            href="#aamarpay-payment" role="tab" aria-controls="aamarpay"
                                            aria-selected="false">{{ __('Aamarpay') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_paytr_enabled']) && $payment_setting['is_paytr_enabled'] == 'on')
                                @if (isset($payment_setting['paytr_merchant_id']) &&
                                        !empty($payment_setting['paytr_merchant_id']) &&
                                        (isset($payment_setting['paytr_merchant_key']) && !empty($payment_setting['paytr_merchant_key'])) &&
                                        (isset($payment_setting['paytr_merchant_salt']) && !empty($payment_setting['paytr_merchant_salt'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-paytr-tab" data-bs-toggle="pill"
                                            href="#paytr-payment" role="tab" aria-controls="paytr"
                                            aria-selected="false">{{ __('Pay TR') }}</a>
                                    </li>
                                @endif
                            @endif
                            @if (isset($payment_setting['is_yookassa_enabled']) && $payment_setting['is_yookassa_enabled'] == 'on')
                                @if (isset($payment_setting['yookassa_shop_id']) &&
                                        !empty($payment_setting['yookassa_shop_id']) &&
                                        (isset($payment_setting['yookassa_secret']) && !empty($payment_setting['yookassa_secret'])))
                                    <li class="nav-item">
                                        <a class="nav-link" id="pills-yookassa-tab" data-bs-toggle="pill"
                                            href="#yookassa-payment" role="tab" aria-controls="yookassa"
                                            aria-selected="false">{{ __('YooKassa') }}</a>
                                    </li>
                                @endif
                            @endif

                            @if (isset($payment_setting['is_midtrans_enabled']) && $payment_setting['is_midtrans_enabled'] == 'on')
                            @if (isset($payment_setting['midtrans_secret']) && !empty($payment_setting['midtrans_secret']))
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-midtrans-tab" data-bs-toggle="pill"
                                        href="#midtrans-payment" role="tab" aria-controls="midtrans"
                                        aria-selected="false">{{ __('Midtrans') }}</a>
                                </li>
                            @endif
                            @endif
                            @if (isset($payment_setting['is_xendit_enabled']) && $payment_setting['is_xendit_enabled'] == 'on')
                            @if (isset($payment_setting['xendit_api']) && !empty($payment_setting['xendit_api']))
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-xendit-tab" data-bs-toggle="pill"
                                        href="#xendit-payment" role="tab" aria-controls="xendit"
                                        aria-selected="false">{{ __('Xendit') }}</a>
                                </li>
                            @endif
                            @endif

                        </ul>



                        <div class="col-12">
                            <div class="tab-content tab-bordered">
                                @if (isset($payment_setting['is_banktransfer_enabled']) && $payment_setting['is_banktransfer_enabled'] == 'on')
                                    @if (isset($payment_setting['bank_details']) && !empty($payment_setting['bank_details']))
                                        <div class="tab-pane fade {{ isset($payment_setting['is_banktransfer_enabled']) && $payment_setting['is_banktransfer_enabled'] == 'on' ? 'show active' : '' }}"
                                            id="banktransfer-payment" role="tabpanel"
                                            aria-labelledby="banktransfer-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="banktransfer-payment-form"
                                                action="{{ route('invoice.pay.with.banktransfer', $invoice->id) }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <div class="row">
                                                    <h5 class="me-2">
                                                        {{ __('Bank Details:') }}
                                                    </h5>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                                        {!! !isset($payment_setting['bank_details']) || is_null($payment_setting['bank_details'])
                                                            ? ''
                                                            : $payment_setting['bank_details'] !!}
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                                        {{ Form::label('Payment Receipt', __('Payment Receipt'), ['class' => 'col-form-label']) }}
                                                        <input type="file" class="form-control" name="payment_receipt"
                                                            id="file" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-12">

                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_stripe_enabled']) && $payment_setting['is_stripe_enabled'] == 'on')
                                    @if (isset($payment_setting['stripe_key']) &&
                                            !empty($payment_setting['stripe_key']) &&
                                            (isset($payment_setting['stripe_secret']) && !empty($payment_setting['stripe_secret'])))
                                        <div class="tab-pane fade" role="tabpanel" id="stripe-payment"
                                            aria-labelledby="stripe-payment">
                                            <div class="card-body">
                                                <form method="post"
                                                    action="{{ route('client.invoice.payment', [$invoice->id]) }}"
                                                    class="require-validation" id="payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-sm-8">
                                                            <div class="custom-radio">
                                                                <label
                                                                    class="text-dark font-weight-bold">{{ __('Credit / Debit Card') }}</label>
                                                            </div>
                                                            <p class="mb-0 pt-1 text-sm">
                                                                {{ __('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.') }}
                                                            </p>
                                                        </div>
                                                        <div class="col-sm-4 text-sm-right mt-3 mt-sm-0">
                                                            <img src="{{ asset('custom/img/payments/master.png') }}"
                                                                height="24" alt="master-card-img">
                                                            <img src="{{ asset('custom/img/payments/discover.png') }}"
                                                                height="24" alt="discover-card-img">
                                                            <img src="{{ asset('custom/img/payments/visa.png') }}"
                                                                height="24" alt="visa-card-img">
                                                            <img src="{{ asset('custom/img/payments/american express.png') }}"
                                                                height="24" alt="american-express-card-img">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label for="card-name-on"
                                                                    class="col-form-label">{{ __('Name on card') }}</label>
                                                                <input type="text" name="name" id="card-name-on"
                                                                    class="form-control required"
                                                                    placeholder="{{ $users->name }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div id="card-element"></div>
                                                            <div id="card-errors" role="alert"></div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-12">

                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="error" style="display: none;">
                                                                <div class='alert-danger alert'>
                                                                    {{ __('Please correct the errors and try again.') }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 form-group mt-3 text-end">
                                                            <input type="submit" value="{{ __('Make Payment') }}"
                                                                class="btn btn-print-invoice  btn-primary m-r-10">
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_paypal_enabled']) && $payment_setting['is_paypal_enabled'] == 'on')
                                    @if (isset($payment_setting['paypal_client_id']) &&
                                            !empty($payment_setting['paypal_client_id']) &&
                                            (isset($payment_setting['paypal_secret_key']) && !empty($payment_setting['paypal_secret_key'])))
                                        <div class="tab-pane fade" id="paypal-payment" role="tabpanel"
                                            aria-labelledby="paypal-payment">

                                            <form class="w3-container w3-display-middle w3-card-4 " method="POST"
                                                id="payment-form"
                                                action="{{ route('client.pay.with.paypal', $invoice->id) }}">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                        @error('amount')
                                                            <span class="invalid-amount text-danger text-xs"
                                                                role="alert">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </div>
                                            </form>


                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_paystack_enabled']) && $payment_setting['is_paystack_enabled'] == 'on')
                                    @if (isset($payment_setting['paystack_public_key']) &&
                                            !empty($payment_setting['paystack_public_key']) &&
                                            (isset($payment_setting['paystack_secret_key']) && !empty($payment_setting['paystack_secret_key'])))
                                        <div class="tab-pane fade" id="paystack-payment" role="tabpanel"
                                            aria-labelledby="paystack-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.paystack') }}"
                                                    class="require-validation" id="paystack-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="button" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="pay_with_paystack">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_flutterwave_enabled']) && $payment_setting['is_flutterwave_enabled'] == 'on')
                                    @if (isset($payment_setting['flutterwave_secret_key']) &&
                                            !empty($payment_setting['flutterwave_secret_key']) &&
                                            (isset($payment_setting['flutterwave_public_key']) && !empty($payment_setting['flutterwave_public_key'])))
                                        <div class="tab-pane fade " id="flutterwave-payment" role="tabpanel"
                                            aria-labelledby="flutterwave-payment">

                                            <form method="post" action="{{ route('invoice.pay.with.flaterwave') }}"
                                                class="require-validation" id="flaterwave-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount" required>
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="button" value="{{ __('Make Payment') }}"
                                                        class="btn btn-print-invoice  btn-primary m-r-10"
                                                        id="pay_with_flaterwave">
                                                </div>
                                            </form>


                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_razorpay_enabled']) && $payment_setting['is_razorpay_enabled'] == 'on')
                                    @if (isset($payment_setting['razorpay_public_key']) &&
                                            !empty($payment_setting['razorpay_public_key']) &&
                                            (isset($payment_setting['razorpay_secret_key']) && !empty($payment_setting['razorpay_secret_key'])))
                                        <div class="tab-pane fade " id="razorpay-payment" role="tabpanel"
                                            aria-labelledby="flutterwave-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.razorpay') }}"
                                                    class="require-validation" id="razorpay-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="button" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="pay_with_razorpay">
                                                    </div>

                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_mollie_enabled']) && $payment_setting['is_mollie_enabled'] == 'on')
                                    @if (isset($payment_setting['mollie_api_key']) &&
                                            !empty($payment_setting['mollie_api_key']) &&
                                            (isset($payment_setting['mollie_profile_id']) && !empty($payment_setting['mollie_profile_id'])))
                                        <div class="tab-pane fade " id="mollie-payment" role="tabpanel"
                                            aria-labelledby="mollie-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.mollie') }}"
                                                    class="require-validation" id="mollie-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_mercado_enabled']) && $payment_setting['is_mercado_enabled'] == 'on')
                                    @if (isset($payment_setting['mercado_access_token']) && !empty($payment_setting['mercado_access_token']))
                                        <div class="tab-pane fade " id="mercado-payment" role="tabpanel"
                                            aria-labelledby="mercado-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.mercado') }}"
                                                    class="require-validation" id="mercado-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_paytm_enabled']) && $payment_setting['is_paytm_enabled'] == 'on')
                                    @if (isset($payment_setting['paytm_merchant_id']) &&
                                            !empty($payment_setting['paytm_merchant_id']) &&
                                            (isset($payment_setting['paytm_merchant_key']) && !empty($payment_setting['paytm_merchant_key'])))
                                        <div class="tab-pane fade " id="paytm-payment" role="tabpanel"
                                            aria-labelledby="paytm-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.paytm') }}"
                                                    class="require-validation" id="paytm-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">

                                                            <div class="form-group">

                                                                <label for="mobile"
                                                                    class="col-form-label text-dark">{{ __('Mobile Number') }}</label>
                                                                <input type="text" id="mobile" name="mobile"
                                                                    class="form-control mobile" data-from="mobile"
                                                                    placeholder="{{ __('Enter Mobile Number') }}"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_skrill_enabled']) && $payment_setting['is_skrill_enabled'] == 'on')
                                    @if (isset($payment_setting['skrill_email']) && !empty($payment_setting['skrill_email']))
                                        <div class="tab-pane fade " id="skrill-payment" role="tabpanel"
                                            aria-labelledby="skrill-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.skrill') }}"
                                                    class="require-validation" id="skrill-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $skrill_data = [
                                                            'transaction_id' => md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id'),
                                                            'user_id' => 'user_id',
                                                            'amount' => 'amount',
                                                            'currency' => 'currency',
                                                        ];
                                                        session()->put('skrill_data', $skrill_data);
                                                    @endphp
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif
                                @if (isset($payment_setting['is_coingate_enabled']) && $payment_setting['is_coingate_enabled'] == 'on')
                                    @if (isset($payment_setting['coingate_auth_token']) && !empty($payment_setting['coingate_auth_token']))
                                        <div class="tab-pane fade " id="coingate-payment" role="tabpanel"
                                            aria-labelledby="coingate-payment">
                                            <div class="card-body">
                                                <form method="post" action="{{ route('invoice.pay.with.coingate') }}"
                                                    class="require-validation" id="coingate-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="amount"
                                                                class="col-form-label">{{ __('Amount') }}</label>
                                                            <div class="input-group col-md-12">
                                                                <div class="input-group-text">
                                                                    {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                                </div>
                                                                <input class="form-control" required="required"
                                                                    min="0" name="amount" type="number"
                                                                    value="{{ $invoice->getDue() }}" min="0"
                                                                    step="0.01" max="{{ $invoice->getDue() }}"
                                                                    id="amount">
                                                                <input type="hidden" value="{{ $invoice->id }}"
                                                                    name="invoice_id">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10">
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endif
                                @endif


                                <div class="tab-pane fade" id="paymentwall-payment" role="tabpanel"
                                    aria-labelledby="paymentwall-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_paymentwall_enabled']) && $payment_setting['is_paymentwall_enabled'] == 'on')
                                            @if (isset($payment_setting['paymentwall_public_key']) &&
                                                    !empty($payment_setting['paymentwall_public_key']) &&
                                                    (isset($payment_setting['paymentwall_private_key']) && !empty($payment_setting['paymentwall_private_key'])))
                                                <form method="post" action="{{ route('paymentwall.invoice') }}"
                                                    class="require-validation" id="paymentwall-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="pay_with_paymentwall">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="toyyibpay-payment" role="tabpanel"
                                    aria-labelledby="toyyibpay-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_toyyibpay_enabled']) && $payment_setting['is_toyyibpay_enabled'] == 'on')
                                            @if (isset($payment_setting['toyyibpay_secret_key']) &&
                                                    !empty($payment_setting['toyyibpay_secret_key']) &&
                                                    (isset($payment_setting['category_code']) && !empty($payment_setting['category_code'])))
                                                <form method="post" action="{{ route('toyyibpay.invoice') }}"
                                                    class="require-validation" id="toyyibpay-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="pay_with_toyyibpay">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="payfast-payment" role="tabpanel"
                                    aria-labelledby="payfast-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_payfast_enabled']) && $payment_setting['is_payfast_enabled'] == 'on')
                                            @if (isset($payment_setting['payfast_merchant_id']) &&
                                                    !empty($payment_setting['payfast_merchant_id']) &&
                                                    (isset($payment_setting['payfast_merchant_key']) && !empty($payment_setting['payfast_merchant_key'])) &&
                                                    (isset($payment_setting['payfast_signature']) && !empty($payment_setting['payfast_signature'])))
                                                @php
                                                    $pfHost = $payment_setting['payfast_mode'] == 'sandbox' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
                                                @endphp
                                                <form method="post" action={{ 'https://' . $pfHost . '/eng/process' }}
                                                    class="require-validation" id="payfast-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control pay_payfast" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id" id="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div id="get-payfast-inputs"></div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10 "
                                                            id="pay_with_payfast">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="iyzipay-payment" role="tabpanel"
                                    aria-labelledby="iyzipay-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_iyzipay_enabled']) && $payment_setting['is_iyzipay_enabled'] == 'on')
                                            @if (isset($payment_setting['iyzipay_public_key']) &&
                                                    !empty($payment_setting['iyzipay_public_key']) &&
                                                    (isset($payment_setting['iyzipay_secret_key']) && !empty($payment_setting['iyzipay_secret_key'])))
                                                <form method="post" action="{{ route('invoice.pay.with.iyzipay') }}"
                                                    class="require-validation" id="iyzipay-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="iyzipay">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="sspay-payment" role="tabpanel"
                                    aria-labelledby="sspay-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_sspay_enabled']) && $payment_setting['is_sspay_enabled'] == 'on')
                                            @if (isset($payment_setting['sspay_secret_key']) &&
                                                    !empty($payment_setting['sspay_secret_key']) &&
                                                    (isset($payment_setting['sspay_category_code']) && !empty($payment_setting['sspay_category_code'])))
                                                <form method="post"
                                                    action="{{ route('invoice.pay.with.sspay', $invoice->id) }}"
                                                    class="require-validation" id="sspay-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="sspay">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="paytab-payment" role="tabpanel"
                                    aria-labelledby="paytab-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_paytab_enabled']) && $payment_setting['is_paytab_enabled'] == 'on')
                                            @if (isset($payment_setting['paytab_profile_id']) &&
                                                    !empty($payment_setting['paytab_profile_id']) &&
                                                    (isset($payment_setting['paytab_region']) && !empty($payment_setting['paytab_region'])) &&
                                                    (isset($payment_setting['paytab_server_key']) && !empty($payment_setting['paytab_server_key'])))
                                                <form method="post" action="{{ route('invoice.pay.with.paytab') }}"
                                                    class="require-validation" id="paytab-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="paytab">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="benefit-payment" role="tabpanel"
                                    aria-labelledby="benefit-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_benefit_enabled']) && $payment_setting['is_benefit_enabled'] == 'on')
                                            @if (isset($payment_setting['benefit_api_key']) &&
                                                    !empty($payment_setting['benefit_api_key']) &&
                                                    (isset($payment_setting['benefit_secret_key']) && !empty($payment_setting['benefit_secret_key'])))
                                                <form method="post" action="{{ route('invoice.pay.with.benefit') }}"
                                                    class="require-validation" id="benefit-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="benefit">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="cashefree-payment" role="tabpanel"
                                    aria-labelledby="cashefree-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_cashefree_enabled']) && $payment_setting['is_cashefree_enabled'] == 'on')
                                            @if (isset($payment_setting['cashfree_key']) &&
                                                    !empty($payment_setting['cashfree_key']) &&
                                                    (isset($payment_setting['cashfree_secret']) && !empty($payment_setting['cashfree_secret'])))
                                                <form method="post" action="{{ route('invoice.pay.with.cashefree') }}"
                                                    class="require-validation" id="cashefree-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="cashefree">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>


                                <div class="tab-pane fade" id="aamarpay-payment" role="tabpanel"
                                    aria-labelledby="aamarpay-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_aamarpay_enabled']) && $payment_setting['is_aamarpay_enabled'] == 'on')
                                            @if (isset($payment_setting['aamarpay_store_id']) &&
                                                    !empty($payment_setting['aamarpay_store_id']) &&
                                                    (isset($payment_setting['aamarpay_signature_key']) && !empty($payment_setting['aamarpay_signature_key'])) &&
                                                    (isset($payment_setting['aamarpay_description']) && !empty($payment_setting['aamarpay_description'])))
                                                <form method="post" action="{{ route('invoice.pay.with.aamarpay') }}"
                                                    class="require-validation" id="aamarpay-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="aamarpay">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="paytr-payment" role="tabpanel"
                                    aria-labelledby="paytr-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_paytr_enabled']) && $payment_setting['is_paytr_enabled'] == 'on')
                                            @if (isset($payment_setting['paytr_merchant_id']) &&
                                                    !empty($payment_setting['paytr_merchant_id']) &&
                                                    (isset($payment_setting['paytr_merchant_key']) && !empty($payment_setting['paytr_merchant_key'])) &&
                                                    (isset($payment_setting['paytr_merchant_salt']) && !empty($payment_setting['paytr_merchant_salt'])))
                                                <form method="post" action="{{ route('invoice.with.paytr') }}"
                                                    class="require-validation" id="paytr-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="paytr">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="yookassa-payment" role="tabpanel"
                                    aria-labelledby="yookassa-payment-tab">
                                    <div class="card-body">
                                        @if (isset($payment_setting['is_yookassa_enabled']) && $payment_setting['is_yookassa_enabled'] == 'on')
                                            @if (isset($payment_setting['yookassa_shop_id']) &&
                                                    !empty($payment_setting['yookassa_shop_id']) &&
                                                    (isset($payment_setting['yookassa_secret']) && !empty($payment_setting['yookassa_secret'])))
                                                <form method="post" action="{{ route('invoice.with.yookassa') }}"
                                                    class="require-validation" id="yookassa-payment-form">
                                                    @csrf
                                                    <div class="row">
                                                        <label for="amount"
                                                            class="col-form-label">{{ __('Amount') }}</label>
                                                        <div class="input-group col-md-12">
                                                            <div class="input-group-text">
                                                                {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                            </div>
                                                            <input class="form-control" required="required"
                                                                min="0" name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10"
                                                            id="yookassa">
                                                    </div>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="midtrans-payment" role="tabpanel"
                                aria-labelledby="midtrans-payment-tab">
                                <div class="card-body">
                                    @if (isset($payment_setting['is_midtrans_enabled']) && $payment_setting['is_midtrans_enabled'] == 'on')
                                        @if (isset($payment_setting['midtrans_secret']) &&
                                                !empty($payment_setting['midtrans_secret']))
                                            <form method="post" action="{{ route('invoice.with.midtrans') }}"
                                                class="require-validation" id="midtrans-payment-form">
                                                @csrf
                                                <div class="row">
                                                    <label for="amount"
                                                        class="col-form-label">{{ __('Amount') }}</label>
                                                    <div class="input-group col-md-12">
                                                        <div class="input-group-text">
                                                            {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                        </div>
                                                        <input class="form-control" required="required"
                                                            min="0" name="amount" type="number"
                                                            value="{{ $invoice->getDue() }}" min="0"
                                                            step="0.01" max="{{ $invoice->getDue() }}"
                                                            id="amount">
                                                        <input type="hidden" value="{{ $invoice->id }}"
                                                            name="invoice_id">
                                                    </div>
                                                </div>
                                                <div class="col-12 form-group mt-3 text-end">
                                                    <input type="submit" value="{{ __('Make Payment') }}"
                                                        class="btn btn-print-invoice  btn-primary m-r-10"
                                                        id="midtrans">
                                                </div>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </div>


                            <div class="tab-pane fade" id="xendit-payment" role="tabpanel"
                            aria-labelledby="xendit-payment-tab">
                            <div class="card-body">
                                @if (isset($payment_setting['is_xendit_enabled']) && $payment_setting['is_xendit_enabled'] == 'on')
                                    @if (isset($payment_setting['xendit_api']) && !empty($payment_setting['xendit_api']))
                                        <form method="post" action="{{ route('invoice.with.xendit') }}"
                                            class="require-validation" id="xendit-payment-form">
                                            @csrf
                                            <div class="row">
                                                <label for="amount"
                                                    class="col-form-label">{{ __('Amount') }}</label>
                                                <div class="input-group col-md-12">
                                                    <div class="input-group-text">
                                                        {{ isset($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '$' }}
                                                    </div>
                                                    <input class="form-control" required="required"
                                                        min="0" name="amount" type="number"
                                                        value="{{ $invoice->getDue() }}" min="0"
                                                        step="0.01" max="{{ $invoice->getDue() }}"
                                                        id="amount">
                                                    <input type="hidden" value="{{ $invoice->id }}"
                                                        name="invoice_id">
                                                </div>
                                            </div>
                                            <div class="col-12 form-group mt-3 text-end">
                                                <input type="submit" value="{{ __('Make Payment') }}"
                                                    class="btn btn-print-invoice  btn-primary m-r-10"
                                                    id="xendit">
                                            </div>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        $(".pay_payfast").keyup(function() {
            var invoice_amount = $('.pay_payfast').val();
            get_payfast_status(invoice_amount = 0);
        });


        $('.cp_link').on('click', function() {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            show_toastr('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
        });
    </script>


    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_paystack_enabled']) &&
            $payment_setting['is_paystack_enabled'] == 'on')
        <script src="https://js.paystack.co/v1/inline.js"></script>

        <script type="text/javascript">
            $(document).on("click", "#pay_with_paystack", function() {


                $('#paystack-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var coupon_id = res.coupon;

                        var paystack_callback = "{{ url('/invoice-pay-with-paystack') }}";
                        var order_id = '{{ time() }}';
                        var handler = PaystackPop.setup({
                            key: '{{ $payment_setting['paystack_public_key'] }}',
                            email: res.email,
                            amount: res.total_price * 100,
                            currency: res.currency,
                            ref: 'pay_ref_id' + Math.floor((Math.random() * 1000000000) +
                                1
                            ), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                            metadata: {
                                custom_fields: [{
                                    display_name: "Email",
                                    variable_name: "email",
                                    value: res.email,
                                }]
                            },

                            callback: function(response) {
                                console.log(response.reference, order_id);
                                window.location.href = "{{ url('/invoice/paystack') }}/" +
                                    response.reference + "/{{ encrypt($invoice->id) }}";
                            },
                            onClose: function() {
                                alert('window closed');
                            }
                        });
                        handler.openIframe();
                    } else if (res.flag == 2) {

                    } else {
                        show_toastr('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_stripe_enabled']) &&
            $payment_setting['is_stripe_enabled'] == 'on')
        <script src="https://js.stripe.com/v3/"></script>
        <script type="text/javascript">
            var stripe = Stripe('{{ $payment_setting['stripe_key'] }}');
            var elements = stripe.elements();

            // Custom styling can be passed to options when creating an Element.
            var style = {
                base: {
                    // Add your base input styles here. For example:
                    fontSize: '14px',
                    color: '#32325d',
                },
            };

            // Create an instance of the card Element.
            var card = elements.create('card', {
                style: style
            });

            // Add an instance of the card Element into the `card-element` <div>.
            card.mount('#card-element');

            // Create a token or display an error when the form is submitted.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe.createToken(card).then(function(result) {
                    if (result.error) {
                        show_toastr('Error', result.error.message, 'error');
                    } else {
                        // Send the token to your server.
                        stripeTokenHandler(result.token);
                    }
                });
            });

            function stripeTokenHandler(token) {
                // Insert the token ID into the form so it gets submitted to the server
                var form = document.getElementById('payment-form');
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                // Submit the form
                form.submit();
            }
        </script>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_flutterwave_enabled']) &&
            $payment_setting['is_flutterwave_enabled'] == 'on')
        <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

        <script type="text/javascript">
            //    Flaterwave Payment
            $(document).on("click", "#pay_with_flaterwave", function() {

                $('#flaterwave-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var coupon_id = res.coupon;
                        var API_publicKey = '';
                        if ("{{ isset($payment_setting['flutterwave_public_key']) }}") {
                            API_publicKey = "{{ $payment_setting['flutterwave_public_key'] }}";
                        }
                        var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                        var flutter_callback = "{{ url('/invoice-pay-with-flaterwave') }}";
                        var x = getpaidSetup({
                            PBFPubKey: API_publicKey,
                            customer_email: '{{ $users->email }}',
                            amount: res.total_price,
                            currency: '{{ $payment_setting['currency'] }}',
                            txref: nowTim + '__' + Math.floor((Math.random() * 1000000000)) +
                                'fluttpay_online-' +
                                {{ date('Y-m-d') }},
                            meta: [{
                                metaname: "payment_id",
                                metavalue: "id"
                            }],
                            onclose: function() {},
                            callback: function(response) {
                                var txref = response.tx.txRef;
                                if (response.tx.chargeResponseCode == "00" || response.tx
                                    .chargeResponseCode == "0") {
                                    window.location.href = "{{ url('/invoice/flaterwave') }}/" +
                                        txref + "/{{ encrypt($invoice->id) }}";
                                } else {
                                    // redirect to a failure page.
                                }
                                x.close(); // use this to close the modal immediately after payment.
                            }
                        });
                    } else if (res.flag == 2) {

                    } else {
                        show_toastr('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_razorpay_enabled']) &&
            $payment_setting['is_razorpay_enabled'] == 'on')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        <script type="text/javascript">
            // Razorpay Payment
            $(document).on("click", "#pay_with_razorpay", function() {
                $('#razorpay-payment-form').ajaxForm(function(res) {

                    if (res.flag == 1) {

                        var razorPay_callback = "{{ url('/invoice-pay-with-razorpay') }}";
                        var totalAmount = res.total_price * 100;
                        var coupon_id = res.coupon;
                        var API_publicKey = '';
                        if ("{{ isset($payment_setting['razorpay_public_key']) }}") {
                            API_publicKey = "{{ $payment_setting['razorpay_public_key'] }}";
                        }
                        var options = {
                            "key": API_publicKey, // your Razorpay Key Id
                            "amount": totalAmount,
                            "name": 'Invoice Payment',
                            "currency": '{{ $payment_setting['currency'] }}',
                            "description": "",
                            "handler": function(response) {
                                window.location.href = "{{ url('/invoice/razorpay') }}/" + response
                                    .razorpay_payment_id + "/{{ encrypt($invoice->id) }}";
                            },
                            "theme": {
                                "color": "#528FF0"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else if (res.flag == 2) {

                    } else {
                        show_toastr('Error', data.message, 'msg');
                    }
                }).submit();
            });
        </script>
    @endif

    @if (
        $invoice->getDue() > 0 &&
            isset($payment_setting['is_payfast_enabled']) &&
            $payment_setting['is_payfast_enabled'] == 'on')
        <script>
            $(document).ready(function() {
                get_payfast_status(amount = 0);

            })

            function get_payfast_status(amount) {
                var invoice_id = $('#invoice_id').val();
                var invoice_amount = $('.pay_payfast').val();

                $.ajax({
                    url: '{{ route('payfast.invoice') }}',
                    method: 'POST',
                    data: {
                        'invoice_id': invoice_id,
                        'amount': invoice_amount,
                        '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.success == true) {
                            $('#get-payfast-inputs').append(data.inputs);
                        } else {
                            show_toastr('Error', data.inputs, 'error');
                        }
                    }
                });
            }
        </script>
    @endif

@endpush
