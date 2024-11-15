<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\UserCoupon;
use App\Models\InvoicePayment;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use App\Models\Deal;


class SkrillPaymentController extends Controller
{
    public $email;
    public $is_enabled;
    public $currancy;

    public function __construct()
    {
        $this->middleware('XSS');
    }



 

    public function invoicePayWithSkrill(Request $request){


        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required'
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

            $admin_payment_setting = Utility::non_auth_payment_settings($user->id);

            $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
            $this->email = isset($admin_payment_setting['skrill_email'])?$admin_payment_setting['skrill_email']:'';
            $this->is_enabled = isset($admin_payment_setting['is_skrill_enabled'])?$admin_payment_setting['is_skrill_enabled']:'off';

        }


        if($invoice->getDue() < $request->amount){
            return Utility::error_res('not correct amount');
        }

        $tran_id = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
        $skill               = new SkrillRequest();
        $skill->pay_to_email = $this->email;
        $skill->return_url   = route('invoice.skrill',encrypt([$request->invoice_id]));
        $skill->cancel_url   = route('invoice.skrill',encrypt([$request->invoice_id]));

        // create object instance of SkrillRequest
        $skill->transaction_id  = MD5($tran_id); // generate transaction id
        $skill->amount          = $request->amount;
        $skill->currency        = $this->currancy;
        $skill->language        = 'EN';
        $skill->prepare_only    = '1';
        $skill->merchant_fields = 'site_name, customer_email';
        $skill->site_name       = $user->name;
        $skill->customer_email  = $user->email;


        // create object instance of SkrillClient
        $client = new SkrillClient($skill);

        $sid    = $client->generateSID();
         //return SESSION ID

        // handle error
        $jsonSID = json_decode($sid);


        if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
        {

            //return redirect()->back()->with('error', $jsonSID->message);
        }


        // do the payment
        $redirectUrl = $client->paymentRedirectUrl($sid);
         //return redirect url
        if($tran_id)
        {
            $data = [
                'amount' => $request->amount,
                'trans_id' => MD5($request['transaction_id']),
                'currency' =>$this->currancy,
            ];

            session()->put('skrill_data', $data);
        }

        try{

            return new RedirectResponse($redirectUrl);
        }catch(\Exception $e)
        {
            if(\Auth::check())
            {
                return redirect()->route('invoices.show',$$request->invoice_id)->with('error', __('Transaction has been failed!'));
            }
            else
            {
                return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
            }

        }
    }

    public function getInvociePaymentStatus(Request $request,$invoice_id){

        if(!empty($invoice_id))
        {
            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::where('id',$invoice_id)->first();
             if(\Auth::check())
            {
                 $user = Auth::user();
            }
            else
            {
               $user=User::where('id',$invoice->created_by)->first();
            }

            if($invoice)
            {
                try
                {

                    if(session()->has('skrill_data'))
                    {
                        $get_data = session()->get('skrill_data');

                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice->id;
                        $invoice_payment->amount         = isset($get_data['amount']) ? $get_data['amount'] : 0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = 'skrill';
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
                        $module ='New Payment';
                        $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                        if($webhook)
                        {
                            $parameter = json_encode($invoice_payment);
                            // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                            // $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                            // if($status == true)
                            // {
                            //     return redirect()->back()->with('success', __('Invoice paid Successfully!'));
                            // }
                            // else
                            // {
                            //     return redirect()->back()->with('error', __('Webhook call failed.'));
                            // }
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
                             return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed!'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
                        }
                    }
                }catch(\Exception $e)
                {
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
        }else{
            if(\Auth::check())
                {
                     return redirect()->route('invoices.index')->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }

        }
    }

    public function paymentSetting()
    {

        $admin_payment_setting = Utility::payment_settings();

        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        $this->email = isset($admin_payment_setting['skrill_email'])?$admin_payment_setting['skrill_email']:'';
        $this->is_enabled = isset($admin_payment_setting['is_skrill_enabled'])?$admin_payment_setting['is_skrill_enabled']:'off';
    }
}
