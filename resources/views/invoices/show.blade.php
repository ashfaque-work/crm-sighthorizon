@extends('layouts.admin')

@section('title')
    {{ __('Invoice Detail') }}
@endsection

@push('head')
    <style>
        #card-element {
            border: 1px solid #e4e6fc;
            border-radius: 5px;
            padding: 10px;
        }
    </style>
@endpush

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


    @if (Auth::user()->type == 'Client' &&
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
                                window.location.href = "{{ url('/invoice/paystack') }}/" + response
                                    .reference + "/{{ encrypt($invoice->id) }}";
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

    @if (Auth::user()->type == 'Client' &&
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

    @if (Auth::user()->type == 'Client' &&
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
                            customer_email: '{{ Auth::user()->email }}',
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

    @if (Auth::user()->type == 'Client' &&
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

    @if (Auth::user()->type == 'Client' &&
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

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page"><a href="{{ route('invoices.index') }}">{{ __('Invoices') }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Invoice Detail') }}</li>
@endsection

@section('action-button')

    @if (Auth::user()->type == 'Owner')
        <a href="#" class="btn btn-sm btn-primary btn-icon cp_link" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Click to copy invoice link') }}"
            data-link="{{ route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
            data-toggle="tooltip" data-original-title="{{ __('Click to copy invoice link') }}"><i
                class="ti ti-file-text text-white"></i></a>
    @endif


    @can('Create Invoice Payment')
        <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Add Payment') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Add Payment') }}"
            data-url="{{ route('invoices.payments.add', $invoice->id) }}"><i class="ti ti-report-money text-white"></i></a>
    @endcan
    @if (Auth::user()->type == 'Client' && $invoice->getDue() > 0)
        <a href="#" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="modal" title="{{ __('Add Payment') }}"
            data-bs-target="#paymentModal" data-bs-whatever="{{ __('add Payment') }}" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('add Payment') }}">
            <i class="ti ti-credit-card text-white"></i>
        </a>
    @endif
    @can('Edit Invoice')
        <a href="#" data-size="lg" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit Invoice') }}"
            data-url="{{ URL::to('invoices/' . $invoice->id . '/edit') }}" data-ajax-popup="true"
            data-title="{{ __('Edit Invoice') }}" class="btn btn-sm btn-primary btn-icon"><i
                class="ti ti-pencil text-white"></i> </a>
    @endcan
    @can('View Invoice')
        <a data-bs-toggle="tooltip" class="btn btn-sm btn-primary btn-icon" data-bs-placement="top"
            title="{{ __('Print Invoice') }}" href="{{ route('get.invoice', $invoice->id) }}"
            title="{{ __('Print Invoice') }}" target="_blanks">
            <span><i class="ti ti-printer text-white"></i></span>
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <!-- [ Invoice ] start -->
        <div class="container">
            <div>
                <div class="card" id="printTable">
                    <div class="card-body">
                        <div class="row ">
                            <div class="col-md-12 invoice-contact">
                                <div class="invoice-box row">
                                    <div class="col-sm-2">
                                        <table class="table table-responsive invoice-table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <h4>{{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                                        </h4>
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
                                    <div class="col-sm-6 invoice-client-info">
                                        <h6>{{ __('From') }} :</h6>
                                        <h6 class="m-0">{{ $settings['company_name'] }}</h6>
                                        <p class="m-0 m-t-10">{{ $settings['company_address'] }}</p>
                                        <p class="m-0">{{ $settings['company_city'] }}
                                            @if (isset($settings['company_city']) && !empty($settings['company_city'])),
                                            @endif
                                        </p>
                                        <p class="m-0">{{ $settings['company_state'] }} @if (isset($settings['company_zipcode']) && !empty($settings['company_zipcode']))-@endif
                                            {{ $settings['company_zipcode'] }}</p>
                                        <p>{{ $settings['company_country'] }}
                                        </p>

                                    </div>
                                    <div class="col-sm-4  text-end">
                                        @if ($client)
                                            <h6 class="m-b-20">{{ __('To') }} :</h6>
                                            <p class="m-0 m-t-10">{{ $client->name }}</p>
                                            <p class="m-0 m-t-10">{{ $client->email }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                        <div class="row invoive-info d-print-inline-flex">
                            <div class="col-sm-3 invoice-client-info">
                                <h6>{{ __('Status') }} :</h6>
                                @if ($invoice->status == 0)
                                    <span
                                        class="badge rounded p-2 px-3 bg-primary">{{ __(\App\Models\Invoice::$statues[0]) }}</span>
                                @elseif($invoice->status == 1)
                                    <span
                                        class="badge rounded p-2 px-3 bg-danger">{{ __(\App\Models\Invoice::$statues[1]) }}</span>
                                @elseif($invoice->status == 2)
                                    <span
                                        class="badge rounded p-2 px-3 bg-warning">{{ __(\App\Models\Invoice::$statues[2]) }}</span>
                                @elseif($invoice->status == 3)
                                    <span
                                        class="badge rounded p-2 px-3 bg-success">{{ __(\App\Models\Invoice::$statues[3]) }}</span>
                                @elseif($invoice->status == 4)
                                    <span
                                        class="badge rounded p-2 px-3 bg-info">{{ __(\App\Models\Invoice::$statues[4]) }}</span>
                                @endif
                            </div>
                            <div class="col-sm-3">
                                <h6 class="m-b-20">{{ __('Deal') }}:</h6>
                                <p class="m-0 m-t-10"><a href="{{ route('deals.show', $invoice->deal_id) }}"
                                        class="text-danger">{{ $invoice->deal->name }}</a></p>
                            </div>
                            <div class="col-sm-3">
                                <h6 class="m-b-20">{{ __('Issue Date') }} :</h6>
                                <p class="m-0 m-t-10">{{ Auth::user()->dateFormat($invoice->issue_date) }}</p>
                            </div>
                            <div class="col-sm-3 text-end">
                                <h6 class="m-b-20">{{ __('Due Date') }} :</h6>
                                <p class="m-0 m-t-10">{{ Auth::user()->dateFormat($invoice->due_date) }}</p>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <h4 class="h4 font-weight-400 float-left">{{ __('Order Summary') }}</h4>
                            </div>
                            <div class="col-md-6 text-end">
                                @can('Estimation Add Product')
                                    <div class="btn btn-sm btn-primary btn-icon mt-1">

                                        <a href="#" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ __('Add Product') }}" data-ajax-popup="true" data-size="lg"
                                            data-title="{{ __('Add Product') }}"
                                            data-url="{{ route('invoices.products.add', $invoice->id) }}"><i
                                                class="ti ti-plus text-white"></i></a>

                                    </div>
                                @endcan
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table invoice-detail-table">
                                        <thead>
                                            <tr class="thead-default">
                                                <th style="width: 250px;">{{ __('Action') }}</th>
                                                <th style="width: 250px;">{{ __('#') }}</th>
                                                <th style="width: 250px;">{{ __('Item') }}</th>
                                                <th style="width: 250px;">{{ __('Price') }}</th>
                                                <th style="width: 250px;">{{ __('Quantity') }}</th>
                                                <th style="width: 250px;" class="text-end">{{ __('Totals') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach ($invoice->getProducts as $product)
                                                <tr>
                                                    <td class="Action">
                                                        <span>
                                                            @can('Invoice Edit Product')
                                                                <div class="action-btn bg-info ms-2">
                                                                    <a href="#" data-size="lg"
                                                                        data-url="{{ route('invoices.products.edit', [$invoice->id, $product->pivot->id]) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Edit Invoice Product') }}"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="{{ __('Edit Invoice Product') }}"><i
                                                                            class="ti ti-pencil text-white"></i></a>
                                                                </div>
                                                            @endcan
                                                            @can('Invoice Delete Product')
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['invoices.products.delete', $invoice->id, $product->pivot->id],
                                                                    ]) !!}
                                                                    <a href="#!"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="{{ __('Delete Invoice Product') }}">
                                                                        <span class="text-white"> <i
                                                                                class="ti ti-trash"></i></span></a>
                                                                    {!! Form::close() !!}
                                                                </div>

                                                @endif
                                                </span>
                                                </td>
                                                <td class="invoice-order">{{ ++$i }}</td>
                                                <td class="small-order">{{ $product->name }}</td>
                                                <td class="small-order">
                                                    {{ Auth::user()->priceFormat($product->pivot->price) }}</td>
                                                <td class="small-order">{{ $product->pivot->quantity }}</td>
                                                @php
                                                    $price = $product->pivot->price * $product->pivot->quantity;
                                                @endphp
                                                <td class="invoice-order text-end">{{ Auth::user()->priceFormat($price) }}
                                                </td>
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
                                                    @php
                                                        $subTotal = $invoice->getSubTotal();
                                                    @endphp
                                                    <th>{{ __('Subtotal') }} :</th>
                                                    <td>{{ Auth::user()->priceFormat($subTotal) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('Discount') }} :</th>
                                                    <td>{{ Auth::user()->priceFormat($invoice->discount) }}</td>
                                                </tr>
                                                <tr>
                                                    @php
                                                        $tax = $invoice->getTax();
                                                    @endphp
                                                    <th>{{ $invoice->tax->name }} ({{ $invoice->tax->rate }} %) :</th>
                                                    <td>{{ Auth::user()->priceFormat($tax) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <hr />
                                                        <h5 class="text-primary m-r-10">{{ __('Total') }} :</h5>
                                                    </td>
                                                    <td>
                                                        <hr />
                                                        <h5 class="text-primary">
                                                            {{ Auth::user()->priceFormat($subTotal - $invoice->discount + $tax) }}
                                                        </h5>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>

                                                        <h5 class="text-primary m-r-10">{{ __('Due Amount') }} :</h5>
                                                    </td>
                                                    <td>

                                                        <h5 class="text-primary">
                                                            {{ Auth::user()->priceFormat($invoice->getDue()) }}</h5>
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
            </div>
            <!-- [ Invoice ] end -->
        </div>

        @can('Manage Invoice Payments')
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 pl-0">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Payments') }}</h5>
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
                                        @if (\Auth::user()->type == 'Owner')
                                            <th class="text-right">{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($invoice->payments->count() || $bankpayment->count())
                                        @foreach ($invoice->payments as $payment)
                                            <tr>
                                                <td>
                                                    {{ sprintf('%05d', $payment->transaction_id) }}
                                                </td>
                                                <td>
                                                    {{ Auth::user()->dateFormat($payment->date) }}
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
                                                    {{ Auth::user()->priceFormat($payment->amount) }}
                                                </td>
                                                <td class="text-right">
                                                    @if (\Auth::user()->type == 'Owner')
                                                        {{-- @if ($payment->status == 'Pending')
                                    <div class="action-btn bg-warning ms-2">
                                        <a href="#" data-size="lg" data-url="{{ route('payment.show',$payment->id) }}" data-ajax-popup="true" data-title="{{__('Payment Status')}}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Approve')}}" ><i class="ti ti-caret-right text-white"></i></a>
                                    </div>
                                    @endif --}}
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id]]) !!}
                                                            <a href="#!"
                                                                class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="{{ __('Delete Order') }}">
                                                                <span class="text-white"> <i class="ti ti-trash"></i></span></a>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @foreach ($bankpayment as $payment)
                                            @if ($payment->status == 'Pending')
                                                <tr>
                                                    <td>-</td>
                                                    <td>
                                                        {{ Auth::user()->dateFormat($payment->date) }}

                                                    </td>
                                                    <td>-</td>
                                                    <td>
                                                        {{ __('Bank Transfer') }}
                                                    </td>
                                                    <td>-</td>
                                                    <td class="text-right">
                                                        {{ Auth::user()->priceFormat($payment->amount) }}
                                                    </td>
                                                    <td class="text-right">
                                                        @if (\Auth::user()->type == 'Owner')
                                                            @if ($payment->status == 'Pending')
                                                                <div class="action-btn bg-warning ms-2">
                                                                    <a href="#" data-size="lg"
                                                                        data-url="{{ route('payment.show', $payment->id) }}"
                                                                        data-ajax-popup="true"
                                                                        data-title="{{ __('Payment Status') }}"
                                                                        class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                                        title="{{ __('Approve') }}"><i
                                                                            class="ti ti-caret-right text-white"></i></a>
                                                                </div>
                                                            @endif
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['payment.delete', $payment->id]]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="{{ __('Delete Payment') }}">
                                                                    <span class="text-white"> <i
                                                                            class="ti ti-trash"></i></span></a>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endif
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




        @endcan

        @if (Auth::user()->type == 'Client' && $invoice->getDue() > 0)

            <div class="modal fade" id="paymentModal" tabindex="-1" data-backdrop="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModalLabel">{{ __('Add Payment') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">


                            <div class="row pb-3 px-2">
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
                                                <a class="nav-link " id="pills-home-tab" data-bs-toggle="pill"
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
                            </div>


                            <div class="tab-content">
                                @if (isset($payment_setting['is_banktransfer_enabled']) && $payment_setting['is_banktransfer_enabled'] == 'on')
                                    @if (isset($payment_setting['bank_details']) && !empty($payment_setting['bank_details']))
                                        <div class="tab-pane fade {{ isset($payment_setting['is_banktransfer_enabled']) && $payment_setting['is_banktransfer_enabled'] == 'on' ? 'show active' : '' }}"
                                            id="banktransfer-payment" role="tabpanel" aria-labelledby="banktransfer-payment">

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
                                                            id="file" required="required">
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
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
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
                                        <div class="tab-pane fade" id="stripe-payment" role="tabpanel"
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
                                                                    class="form-control-label">{{ __('Name on card') }}</label>
                                                                <input type="text" name="name" id="card-name-on"
                                                                    class="form-control required"
                                                                    placeholder="{{ \Auth::user()->name }}">
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
                                                                    {{ __('Please correct the errors and try again.') }}</div>
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
                                            <div class="card-body">
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
                                            <div class="card-body">
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
                                                                    id="amount">
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
                                                                    class="form-control-label text-dark">{{ __('Mobile Number') }}</label>
                                                                <input type="text" id="mobile" name="mobile"
                                                                    class="form-control mobile" data-from="mobile"
                                                                    placeholder="{{ __('Enter Mobile Number') }}" required>
                                                            </div>
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
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
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
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10" id="iyzipay">
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
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
                                                                value="{{ $invoice->getDue() }}" min="0"
                                                                step="0.01" max="{{ $invoice->getDue() }}"
                                                                id="amount">
                                                            <input type="hidden" value="{{ $invoice->id }}"
                                                                name="invoice_id">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 form-group mt-3 text-end">
                                                        <input type="submit" value="{{ __('Make Payment') }}"
                                                            class="btn btn-print-invoice  btn-primary m-r-10" id="paytab">
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
                                                <li class="nav-item">
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
                                                                id="paytab">
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
                                                <li class="nav-item">
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
                                                                id="paytab">
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
                                                <li class="nav-item">
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
                                                <li class="nav-item">
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
                                                            <input class="form-control" required="required" min="0"
                                                                name="amount" type="number"
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
        @endif
    @endsection
