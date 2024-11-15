<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Coupon;
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
use Xendit\Xendit;
use App\Models\Deal;
use Illuminate\Support\Str;

class XenditPaymentController extends Controller
{


   
    public function invoicePayWithXendit(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $get_amount = $request->amount;
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        try {
            if ($invoice) {
                $payment_setting = Utility::invoice_payment_settings($user->id);
                $xendit_token = $payment_setting['xendit_token'];
                $xendit_api = $payment_setting['xendit_api'];
                $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'PHP';
                $response = ['orderId' => $orderID, 'user' => $user, 'get_amount' => $get_amount, 'invoice' => $invoice, 'currency' => $currency];
                Xendit::setApiKey($xendit_api);
                $params = [
                    'external_id' => $orderID,
                    'payer_email' => Auth::user()->email,
                    'description' => 'Payment for order ' . $orderID,
                    'amount' => $get_amount,
                    'callback_url' =>  route('invoice.xendit.status'),
                    'success_redirect_url' => route('invoice.xendit.status', $response),
                ];

                $Xenditinvoice = \Xendit\Invoice::create($params);
                Session::put('invoicepay',$Xenditinvoice);
                return redirect($Xenditinvoice['invoice_url']);

            } else {
                return redirect()->back()->with('error', 'Invoice not found.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __($e));
        }
    }

    public function getInvociePaymentStatus(Request $request){

        $session = Session::get('invoicepay');
        $invoice = Invoice::find($request->invoice);
        $user = User::where('id', $invoice->created_by)->first();
        $payment_setting = Utility::invoice_payment_settings($user->id);
        $get_amount = $request->get_amount;

        $xendit_api = $payment_setting['xendit_api'];
        Xendit::setApiKey($xendit_api);
        $getInvoice = \Xendit\Invoice::retrieve($session['id']);

        if($getInvoice['status'] == 'PAID'){


            $invoice_payment = new InvoicePayment();
            $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
            $invoice_payment->invoice_id = $invoice->id;
            $invoice_payment->amount = $get_amount;
            $invoice_payment->date = date('Y-m-d');
            $invoice_payment->payment_id = 0;
            $invoice_payment->payment_type = __('Xendit');
            $invoice_payment->client_id = 0;
            $invoice_payment->notes = '';
            $invoice_payment->save();

            if(($invoice->getDue() - $invoice_payment->amount) == 0)
            {
                $invoice->status = 'paid';
                $invoice->save();
            }

            $settings  = Utility::settings($invoice->created_by);
            $deal_id=$invoice->deal_id;
            $deal = Deal::findOrFail($deal_id);
            $obj = [
                'payer_name' => ucfirst($user->name),
                'amount'  => $invoice_payment->amount,
                'payment_type' => $invoice_payment->payment_type,
                'deal_name' => $deal->name,

            ];
            if(isset($settings['payment_notification']) && $settings['payment_notification'] ==1){
                \Utility::send_slack_msg('new_payment',$obj,$invoice->created_by);
            }
            if(isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] ==1){
                \Utility::send_telegram_msg('new_payment',$obj,$invoice->created_by);
                }
                $module ='New Payment';
                $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                if($webhook)
                {
                    $parameter = json_encode($invoice_payment);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                    if($status == true)
                    {
                        return redirect()->back()->with('success', __('Payment added Successfully'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }

            if(\Auth::check())
            {
                 return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment added Successfully'));
            }
            else
            {
                return redirect()->route('pay.invoice',\Crypt::encrypt($invoice->id))->with('success', __('Payment added Successfully'));
            }

        }
        else
        {
            if(\Auth::check())
            {
                 return redirect()->route('invoices.show', $invoice->id)->with('error', __('Transaction has been ' . $status));
            }
            else
            {
                return redirect()->route('pay.invoice',\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been ' . $status));
            }


        }
    }
}
