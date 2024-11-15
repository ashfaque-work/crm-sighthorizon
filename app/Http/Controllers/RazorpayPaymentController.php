<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Validator;
use App\Models\Deal;


class RazorpayPaymentController extends Controller
{
    //
    public $secret_key;
    public $public_key;
    public $is_enabled;
    public $currancy;

    public function __construct()
    {
        $this->middleware('XSS');
    }

    public function invoicePayWithRazorpay(Request $request){


        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
        ];
        $validator      = Validator::make(
            $request->all(), $validatorArray
        )->setAttributeNames(
            ['invoice_id' => 'Invoice']
        );
        if($validator->fails())
        {
            return Utility::error_res($validator->errors()->first());
        }
        $invoice = Invoice::find($request->invoice_id);
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
             $this->paymentSetting();
        }else{

           $payment_setting = Utility::non_auth_payment_settings($user->id);

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';
        $this->secret_key = isset($payment_setting['razorpay_secret_key'])?$payment_setting['razorpay_secret_key']:'';
        $this->public_key = isset($payment_setting['razorpay_public_key'])?$payment_setting['razorpay_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_razorpay_enabled'])?$payment_setting['is_razorpay_enabled']:'off';

        }
        if($invoice->getDue() < $request->amount){
            return Utility::error_res('not currect amount');
        }

        $res_data['email'] = $user->email;
        $res_data['total_price'] = $request->amount;
        $res_data['currency'] = $this->currancy;
        $res_data['flag'] = 1;
        $res_data['invoice_id'] = $invoice->id;
        $request->session()->put('invoice_data', $res_data);
        $this->pay_amount =$request->amount;
        return $res_data;
    }


    public function getInvociePaymentStatus($pay_id,$invoice_id,Request $request){

         $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::find($invoice_id);

         $user=User::where('id',$invoice->created_by)->first();


         if(\Auth::check())
        {
             $user = Auth::user();
             $this->paymentSetting();
        }else{

           $payment_setting = Utility::non_auth_payment_settings($user->id);

        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';
        $this->secret_key = isset($payment_setting['razorpay_secret_key'])?$payment_setting['razorpay_secret_key']:'';
        $this->public_key = isset($payment_setting['razorpay_public_key'])?$payment_setting['razorpay_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_razorpay_enabled'])?$payment_setting['is_razorpay_enabled']:'off';

        }

        $invoice_data =  $request->session()->get('invoice_data') ;


        if(!empty($invoice_id) && !empty($pay_id))
        {



            if($invoice && !empty($invoice_data))
            {


                try
                {

                    $orderID = time();
                $ch      = curl_init('https://api.razorpay.com/v1/payments/' . $pay_id . '');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_USERPWD, $this->public_key . ':' . $this->secret_key); // Input your Razorpay Key Id and Secret Id here
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = json_decode(curl_exec($ch));





                    if(isset($response->status) && $response->status == 'authorized')
                    {

                        //$paydata = $response->data;

                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice_id;
                        $invoice_payment->amount         = isset($invoice_data['total_price'])?$invoice_data['total_price']:0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   =  __('Razorpay');
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
                        if(\Auth::check())
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('success', __('Payment added Successfully'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                        }

                    }else{
                        if(\Auth::check())
                        {
                             return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction fail'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction fail'));
                        }

                    }
                }
                catch(\Exception $e)
                {
                    if(\Auth::check())
                    {
                         return redirect()->route('plans.index')->with('error', __('Invoice not found.'));
                    }
                    else
                    {
                        return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                    }

                }
            }else{
                if(\Auth::check())
                {
                     return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }

            }
        }else{
            if(\Auth::check())
                {
                     return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }
        }
    }

    public function paymentSetting()
    {

        $payment_setting = Utility::payment_settings();
        $this->currancy = isset($payment_setting['currency'])?$payment_setting['currency']:'';
        $this->secret_key = isset($payment_setting['razorpay_secret_key'])?$payment_setting['razorpay_secret_key']:'';
        $this->public_key = isset($payment_setting['razorpay_public_key'])?$payment_setting['razorpay_public_key']:'';
        $this->is_enabled = isset($payment_setting['is_razorpay_enabled'])?$payment_setting['is_razorpay_enabled']:'off';
    }
}
