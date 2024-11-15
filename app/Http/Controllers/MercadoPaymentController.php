<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LivePixel\MercadoPago\MP;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Validator;
use App\Models\Deal;


class MercadoPaymentController extends Controller
{
    public $token;
    public $is_enabled;
    public $currancy;
    public $mode;


    public function __construct()
    {
        $this->middleware('XSS');
    }

    public function invoicePayWithMercado(Request $request){


        //$this->paymentSetting();

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

            $admin_payment_setting = Utility::non_auth_payment_settings($user->id);
            $this->token = isset($admin_payment_setting['mercado_access_token'])?$admin_payment_setting['mercado_access_token']:'';
        $this->mode = isset($admin_payment_setting['mercado_mode'])?$admin_payment_setting['mercado_mode']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mercado_enabled'])?$admin_payment_setting['is_mercado_enabled']:'off';
        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        }

        if($invoice->getDue() < $request->amount){
            return Utility::error_res('not correct amount');
        }

        $preference_data       = array(
            "items" => array(
                array(
                    "title" => "Invoice Payment",
                    "quantity" => 1,
                    "currency_id" => $this->currancy,
                    "unit_price" => (float)$request->amount,
                ),
            ),
        );

         \MercadoPago\SDK::setAccessToken($this->token);
        try {

            // Create a preference object
            $preference = new \MercadoPago\Preference();
            // Create an item in the preference
            $item = new \MercadoPago\Item();
            $item->title = "Invoice : " . $request->invoice_id;
            $item->quantity = 1;
            $item->unit_price = (float)$request->amount;
            $preference->items = array($item);

            $success_url = route('invoice.mercado',[encrypt($invoice->id),'amount'=>(float)$request->amount,'flag'=>'success']);
            $failure_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'failure']);
            $pending_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'pending']);
            $preference->back_urls = array(
                "success" => $success_url,
                "failure" => $failure_url,
                "pending" => $pending_url
            );
            $preference->auto_return = "approved";
            $preference->save();

            // Create a customer object
            $payer = new \MercadoPago\Payer();
            // Create payer information
            $payer->name = $user->name;
            $payer->email = $user->email;
            $payer->address = array(
                "street_name" => ''
            );

            if($this->mode =='live'){
                $redirectUrl = $preference->init_point;
            }else{
                $redirectUrl = $preference->sandbox_init_point;
            }
            return redirect($redirectUrl);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getInvociePaymentStatus(Request $request,$invoice_id){

         if(!empty($invoice_id))
        {

            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::find($invoice_id);
             if(\Auth::check())
                {
                     $user = Auth::user();
                }
                else
                {
                   $user=User::where('id',$invoice->created_by)->first();
                }
            if($invoice && $request->has('status'))
            {

                try
                {



                    if($request->status == 'approved' && $request->flag =='success')
                    {


                    $invoice_payment                 = new InvoicePayment();
                    $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                    $invoice_payment->invoice_id     = $invoice_id;
                    $invoice_payment->amount         = $request->amount?$request->amount:0;
                    $invoice_payment->date           = date('Y-m-d');
                    $invoice_payment->payment_id     = 0;
                    $invoice_payment->payment_type   = 'Mercado Pago';
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
                                return redirect()->back()->with('success', __('Invoice paid Successfully!'));
                            }
                            else
                            {
                                return redirect()->back()->with('error', __('Webhook call failed.'));
                            }
                        }

                        if(\Auth::check())
                        {
                              return redirect()->route('invoices.show', $invoice_id)->with('success', __('Invoice paid Successfully!'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
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
                    return redirect()->route('invoices.index')->with('error', __('Plan not found!'));
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
        $this->token = isset($admin_payment_setting['mercado_access_token'])?$admin_payment_setting['mercado_access_token']:'';
        $this->mode = isset($admin_payment_setting['mercado_mode'])?$admin_payment_setting['mercado_mode']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mercado_enabled'])?$admin_payment_setting['is_mercado_enabled']:'off';
        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        return;
    }
}
