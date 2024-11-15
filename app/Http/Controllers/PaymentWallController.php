<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\User;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Validator;
use App\Models\Deal;

class PaymentWallController extends Controller
{

    public function invoicepay(Request $request)
    {
        $data=$request->all();
        $invoice=Invoice::find($request->invoice_id);
        $admin_payment_setting =$this->paymentSetting($invoice->created_by);

        return view('invoices.paymentwallpay',compact('data','admin_payment_setting'));

    }


    public function invoiceerror(Request $request,$flag,$invoice_id)
    {

         if(\Auth::check())
        {
            if($flag == 1){
                     return redirect()->route('invoices.show',$invoice_id)->with('success', __('Payment added Successfully'));
            }else{
                    return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed! '));
            }

        }
        else
        {
            if($flag == 1){
                     return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully '));
            }else{
                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
            }
        }

    }

    public function invoicePayWithPaymentWall(Request $request,$invoice_id){

        $invoice = Invoice::find($invoice_id);

        if(\Auth::check())
        {
             $user = Auth::user();
        }
        else
        {
           $user=User::where('id',$invoice->created_by)->first();
        }

        if(\Auth::check())
            {
                 $user = Auth::user();
                 $this->paymentSetting($user->id);
            }else{

                $payment_setting = Utility::non_auth_payment_settings($user->id);

                $this->currancy =isset($payment_setting['currency'])?$payment_setting['currency']:'';

                $this->secret_key = isset($payment_setting['flutterwave_secret_key'])?$payment_setting['flutterwave_secret_key']:'';
                $this->public_key = isset($payment_setting['flutterwave_public_key'])?$payment_setting['flutterwave_public_key']:'';
                $this->is_enabled = isset($payment_setting['is_flutterwave_enabled'])?$payment_setting['is_flutterwave_enabled']:'off';
            }
            if($invoice->getDue() < $request->amount){
                return Utility::error_res('not currect amount');
            }
            \Paymentwall_Config::getInstance()->set(array(

                'private_key' => $this->secret_key
            ));

            $parameters = $request->all();

            $chargeInfo = array(
                'email' => $parameters['email'],
                'history[registration_date]' => '1489655092',
                'amount' => isset($request['amount'])?$request['amount']:0,
                'currency' => !empty($this->currancy) ? $this->currancy : 'USD',
                'token' => $parameters['brick_token'],
                'fingerprint' => $parameters['brick_fingerprint'],
                'description' => 'Order #123'
            );

            $charge = new \Paymentwall_Charge();
            $charge->create($chargeInfo);
            $responseData = json_decode($charge->getRawResponseData(),true);
            $response = $charge->getPublicData();

            if ($charge->isSuccessful() AND empty($responseData['secure'])) {
                if ($charge->isCaptured()) {

                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice_id;
                        $invoice_payment->amount         = isset($request['amount'])?$request['amount']:0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = 'PaymentWall';
                        $invoice_payment->client_id      =  $user->id;
                        $invoice_payment->notes          = '';
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

                        $module ='Payment create';
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

                        $res['invoice']=$invoice_id;
                         $res['flag'] = 1;
                         return $res;

                } elseif ($charge->isUnderReview()) {
                    $res['invoice']=$invoice_id;
                     $res['flag'] = 2;
                     return $res;
                }
            }
             else {
                $errors = json_decode($response, true);
                $res['invoice']=$invoice_id;
                 $res['flag'] = 2;
                 return $res;
            }

    }



    public function paymentSetting($id)
    {
        $payment_setting = Utility::invoice_payment_settings($id);

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['paymentwall_private_key'])?$payment_setting['paymentwall_private_key']:'';
        $this->public_key = isset($payment_setting['paymentwall_public_key'])?$payment_setting['paymentwall_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_paymentwall_enabled'])?$payment_setting['is_paymentwall_enabled']:'off';
        return $this;
    }




    public function planpaymentSetting()
    {
        $payment_setting = Utility::payment_settings();

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';

        $this->secret_key = isset($payment_setting['paymentwall_private_key'])?$payment_setting['paymentwall_private_key']:'';
        $this->public_key = isset($payment_setting['paymentwall_public_key'])?$payment_setting['paymentwall_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_paymentwall_enabled'])?$payment_setting['is_paymentwall_enabled']:'off';
        return $this;
    }
}
