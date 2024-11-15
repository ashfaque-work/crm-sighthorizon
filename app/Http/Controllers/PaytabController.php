<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Paytabscom\Laravel_paytabs\Facades\paypage;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Exception;
use App\Models\Utility;
use App\Models\Deal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PaytabController extends Controller
{
    //

    public function paymentSetting($id)
    {
        $payment_setting = Utility::non_auth_payment_settings($id);
        config([
            'paytabs.profile_id' => isset($payment_setting['paytab_profile_id']) ? $payment_setting['paytab_profile_id'] : '',
            'paytabs.server_key' => isset($payment_setting['paytab_server_key']) ? $payment_setting['paytab_server_key'] : '',
            'paytabs.region' => isset($payment_setting['paytab_region']) ? $payment_setting['paytab_region'] : '',
            'paytabs.currency' => isset($payment_setting['currency']) ? $payment_setting['currency'] : '',

        ]);
    }

    public function invoicepaywithpaytabpay(Request $request)
    {
        try {
            $invoice_id = $request->invoice_id;
            $invoice = Invoice::find($invoice_id);
            $this->paymentSetting($invoice->created_by);
            if (\Auth::check()) {
                $user     = \Auth::user();
            } else {
                $user = User::where('id', $invoice->created_by)->first();
            }
            $get_amount = $request->amount;
            if ($invoice && $get_amount != 0) {
                if ($get_amount > $invoice->getDue()) {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                } else {
                    $pay = paypage::sendPaymentCode('all')
                        ->sendTransaction('sale')
                        ->sendCart(1, $get_amount, 'invoice payment')
                        ->sendCustomerDetails(isset($user->name) ? $user->name : "", isset($user->email) ? $user->email : '', '', '', '', '', '', '', '')
                        ->sendURLs(
                            route('customer.paytab', ['success' => 1, 'data' => $request->all(), $invoice->id, 'amount' => $get_amount]),
                            route('customer.paytab', ['success' => 0, 'data' => $request->all(), $invoice->id, 'amount' => $get_amount])
                        )
                        ->sendLanguage('en')
                        ->sendFramed($on = false)
                        ->create_pay_page();
                    return $pay;
                }
            }
        } catch (Exception $e) {
      
            return redirect()->back()->with('error', __($e));
        }
    }


    public function getInvoicePaymentStatus(Request $request,$invoice_id, $amount)
    {

        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $objUser = $user;

         if(\Auth::check())
        {
            $user = Auth::user();
        }
        else
        {
           $user=User::where('id',$invoice->created_by)->first();
        }
        if($invoice){
            try {
                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->invoice_id     = $invoice_id;
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber( $user);
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->amount         = $amount;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = "";
                $invoice_payment->payment_type   = 'Paytab';
                $invoice_payment->save();


                if ($invoice->getDue() == 0) {
                    $invoice->status = 3;
                    $invoice->save();
                } else {
                    $invoice->status = 2;
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
                        Utility::send_slack_msg('new_payment',$obj,$invoice->created_by);
                    }
                    if(isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] ==1){
                        Utility::send_telegram_msg('new_payment',$obj,$invoice->created_by);
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

                if(Auth::check()){
                    return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment added Successfully'));
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', __('Invoice Paid Successfully'));
                }
            } catch (\Exception $e) {
                if(Auth::check()){
                    return redirect()->route('invoices.show', $invoice->id)->with('error',$e->getMessage());
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success',$e->getMessage());
                }
            }
        } else {
            if(Auth::check()){
                return redirect()->route('invoices.show', $invoice->id)->with('error',__('Invoice not found.'));
            }else{
                return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', __('Invoice not found.'));

            }
        }

    }
}
