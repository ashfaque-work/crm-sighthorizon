<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaytrController extends Controller
{

    public function invoicePayWithpaytr(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
        $paytr_merchant_id = $payment_setting['paytr_merchant_id'];
        $paytr_merchant_key = $payment_setting['paytr_merchant_key'];
        $paytr_merchant_salt = $payment_setting['paytr_merchant_salt'];
        $currency =isset($payment_setting['currency'])?$payment_setting['currency']:'USD';
        if (\Auth::check()) {
            $settings = \DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name')->toArray();
            $user     = \Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
            $settings = Utility::settingById($invoice->created_by);
        }
        $get_amount = $request->amount;

        if ($invoice) {
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            if ($get_amount > $invoice->getdue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                if(Auth::check()){
                    $name = Auth::user()->invoiceNumberFormat($settings, $invoice->invoice_id);
                }else{
                    $user = User::where('id', $invoice->created_by)->first();
                    $name = $user->invoiceNumberFormat($settings, $invoice->invoice_id);

                }
            }
        }
        try {

            $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
            $merchant_id    = $paytr_merchant_id;
            $merchant_key   = $paytr_merchant_key;
            $merchant_salt  = $paytr_merchant_salt;

            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $email = $user->email;
            $payment_amount = $get_amount;
            $merchant_oid = $orderID;
            $user_name = $user->name;
            $user_address = 'no address';
            $user_phone ='0000000000';

            $user_basket = base64_encode(json_encode(array(
                array("Plan", $payment_amount, 1),
            )));

            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }

            $user_ip = $ip;
            $timeout_limit = "30";
            $debug_on = 1;
            $test_mode = 0;
            $no_installment = 0;
            $max_installment = 0;
            $currency = isset($payment_setting['currency'])?$payment_setting['currency']:'USD';
            $paytr_price = $get_amount * 100;

            $payment_amount = $payment_amount * 100;
            $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
            $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

            $request['orderID'] = $orderID;
            $request['invoice_id'] = $invoice;
            $request['price'] = $get_amount;
            $request['payment_status'] = 'failed';
            $payment_failed = $request->all();
            $request['payment_status'] = 'success';
            $payment_success = $request->all();
            $post_vals = array(
                'merchant_id' => $merchant_id,
                'user_ip' => $user_ip,
                'merchant_oid' => $merchant_oid,
                'email' => $email,
                'payment_amount' => $payment_amount,
                'paytr_token' => $paytr_token,
                'user_basket' => $user_basket,
                'debug_on' => $debug_on,
                'no_installment' => $no_installment,
                'max_installment' => $max_installment,
                'user_name' => $user_name,
                'user_address' => $user_address,
                'user_phone' => $user_phone,
                'merchant_ok_url' => route('invoice.paytr.status', $payment_success),
                'merchant_fail_url' => route('invoice.paytr.status', $payment_failed),
                'timeout_limit' => $timeout_limit,
                'currency' => $currency,
                'test_mode' => $test_mode
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);


            $result = @curl_exec($ch);
            if (curl_errno($ch)) {
                die("PAYTR IFRAME connection error. err:" . curl_error($ch));
            }

            curl_close($ch);

            $result = json_decode($result, 1);

            if ($result['status'] == 'success') {
                $token = $result['token'];
            } else {
                return redirect()->route('plans.index')->with('error', $result['reason']);
            }
            return view('paytr_payment.index', compact('token'));
        } catch (\Throwable $th) {
            return redirect()->route('plans.index')->with('error', $th->getMessage());
        }

    }

    public function getInvociePaymentStatus(Request $request)
    {
        $getAmount = $request->amount;
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();

        }
        if ($invoice) {
            try {
                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->invoice_id     = $invoice->id;
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->amount         = $getAmount;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = "";
                $invoice_payment->payment_type   = 'PayTR';
                $invoice_payment->save();
                if ($invoice->getDue() == 0) {
                    $invoice->status = 3;
                    $invoice->save();
                } else {
                    $invoice->status = 2;
                    $invoice->save();
                }
                $settings  = Utility::settings($invoice->created_by);
                $deal_id = $invoice->deal_id;
                $deal = Deal::findOrFail($deal_id);
                $obj = [
                    'payer_name' => ucfirst($user->name),
                    'amount'  => $invoice_payment->amount,
                    'payment_type' => $invoice_payment->payment_type,
                    'deal_name' => $deal->name,

                ];
                if (isset($settings['payment_notification']) && $settings['payment_notification'] == 1) {
                    \Utility::send_slack_msg('new_payment', $obj, $invoice->created_by);
                }
                if (isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] == 1) {
                    \Utility::send_telegram_msg('new_payment', $obj, $invoice->created_by);
                }
                $module = 'New Payment';
                $webhook =  Utility::webhookSetting($module, $invoice->created_by);
                if ($webhook) {
                    $parameter = json_encode($invoice_payment);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {
                        return redirect()->back()->with('success', __('Payment added Successfully'));
                    } else {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }

                if (Auth::check()) {
                    return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment added Successfully'));
                } else {
                    return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', __('Invoice Paid Successfully'));
                }
            } catch (\Exception $e) {
                if (Auth::check()) {
                    return redirect()->route('invoices.show', $invoice->id)->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', $e->getMessage());
                }
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('invoices.show', $invoice->id)->with('error', __('Invoice not found.'));
            } else {
                return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', __('Invoice not found.'));
            }
        }
    }
}
