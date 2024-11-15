<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use YooKassa\Client;

class YooKassaController extends Controller
{




    public function invoicePayWithYookassa(Request $request)
    {
        $invoice_id = $request->invoice_id;

        $invoice = Invoice::find($invoice_id);
        $getAmount = $request->amount;

        $user = User::where('id', $invoice->created_by)->first();

        $payment_setting = Utility::invoice_payment_settings($user->id);

        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret'];
        $currency = isset($payment_setting['site_currency']) ? $payment_setting['site_currency'] : 'RUB';
        $get_amount = $request->amount;

        try {
            if ($invoice) {


                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $payment = $client->createPayment(
                        array(
                            'amount' => array(
                                'value' => $get_amount,
                                'currency' => $currency,
                            ),
                            'confirmation' => array(
                                'type' => 'redirect',
                                'return_url' => route('invoice.yookassa.status', [
                                    'invoice_id' => $invoice->id,
                                    'amount' => $get_amount
                                ]),
                            ),
                            'capture' => true,
                            'description' => 'Заказ №1',
                        ),
                        uniqid('', true)
                    );

                    Session::put('yookassa_payment_id', $payment['id']);

                    if ($payment['confirmation']['confirmation_url'] != null) {
                        return redirect($payment['confirmation']['confirmation_url']);
                    } else {
                        return redirect()->route('plans.index')->with('error', 'Something went wrong, Please try again');
                    }
                } else {
                    return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                }
            } else {
                return redirect()->back()->with('error', 'Invoice not found.');
            }
        } catch (\Throwable $e) {

            return redirect()->back()->with('error', __($e));
        }
    }
    public function getInvociePaymentStatus(Request $request)
    {
      
        $get_amount = $request->amount;

        $invoice = Invoice::find($request->invoice_id);
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();

        }

        $payment_setting = Utility::invoice_payment_settings($user->id);
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret'];

        if ($invoice) {
            try {
                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    $paymentId = Session::get('yookassa_payment_id');

                    if ($paymentId == null) {
                        return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
                    }
                    $payment = $client->getPaymentInfo($paymentId);

                    Session::forget('yookassa_payment_id');
                    if (isset($payment) && $payment->status == "succeeded") {

                        $user = auth()->user();

                        try {

                            $invoice_payment = new InvoicePayment();
                            $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                            $invoice_payment->invoice_id = $invoice->id;
                            $invoice_payment->amount = $get_amount;
                            $invoice_payment->date = date('Y-m-d');
                            $invoice_payment->payment_id = 0;
                            $invoice_payment->payment_type = __('Yookassa');
                            $invoice_payment->client_id = 0;
                            $invoice_payment->notes = '';
                            $invoice_payment->save();

                            $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');

                            if ($invoice_getdue <= 0.0) {

                                Invoice::change_status($invoice->id, 3);
                            } else {

                                Invoice::change_status($invoice->id, 2);
                            }


                            if (Auth::check()) {

                                return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment added Successfully'));
                            } else {

                                return redirect()->route('pay.invoice', encrypt($request->invoice_id))->with('ERROR', __('Transaction fail'));
                            }
                        } catch (\Exception $e) {

                            return redirect()->route('pay.invoice')->with('error', __($e->getMessage()));
                        }
                    } else {
                        return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                    }
                }
            } catch (\Exception $e) {
                if (Auth::check()) {
                    return redirect()->route('invoices.show', $invoice->id)->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', encrypt($request->invoice_id))->with('success', $e->getMessage());
                }
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('invoices.show', $invoice->id)->with('error', $e->getMessage());
            } else {
                return redirect()->route('pay.invoice', encrypt($request->invoice_id))->with('success', __('Invoice not found.'));
            }
        }
    }
}
