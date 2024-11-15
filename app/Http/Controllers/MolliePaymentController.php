<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Utility;
use App\Models\Plan;
use App\Models\User;
use App\Models\Order;
use App\Models\UserCoupon;
use App\Models\InvoicePayment;
use App\Models\Invoice;
use App\Models\Deal;


class MolliePaymentController extends Controller
{

    public $api_key;
    public $profile_id;
    public $partner_id;
    public $is_enabled;
    public $currancy;

    public function __construct()
    {
        $this->middleware('XSS');
    }


  

    public function invoicePayWithMollie(Request $request){

        $amount = $request->amount;





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

        $this->currancy =isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';

        $this->api_key = isset($admin_payment_setting['mollie_api_key'])?$admin_payment_setting['mollie_api_key']:'';
        $this->profile_id = isset($admin_payment_setting['mollie_profile_id'])?$admin_payment_setting['mollie_profile_id']:'';
        $this->partner_id = isset($admin_payment_setting['mollie_partner_id'])?$admin_payment_setting['mollie_partner_id']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mollie_enabled'])?$admin_payment_setting['is_mollie_enabled']:'off';

        }

        if($invoice->getDue() < $request->amount){
            return Utility::error_res('not correct amount');
        }

        $mollie  = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($this->api_key);

        $payment = $mollie->payments->create(
            [
                "amount" => [
                    "currency" => $this->currancy,
                    "value" => number_format((float)$amount, 2, '.', ''),
                ],
                "description" => "payment for product",
                "redirectUrl" => route('invoice.mollie', encrypt($invoice->id)),
            ]
        );

        session()->put('mollie_payment_id', $payment->id);
        return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
    }

    public function getInvociePaymentStatus($invoice_id,Request $request){



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

            if(\Auth::check())
            {
                 $user = Auth::user();
                 $this->paymentSetting();
            }else{

                $admin_payment_setting = Utility::non_auth_payment_settings($user->id);

            $this->currancy =isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';

            $this->api_key = isset($admin_payment_setting['mollie_api_key'])?$admin_payment_setting['mollie_api_key']:'';
            $this->profile_id = isset($admin_payment_setting['mollie_profile_id'])?$admin_payment_setting['mollie_profile_id']:'';
            $this->partner_id = isset($admin_payment_setting['mollie_partner_id'])?$admin_payment_setting['mollie_partner_id']:'';
            $this->is_enabled = isset($admin_payment_setting['is_mollie_enabled'])?$admin_payment_setting['is_mollie_enabled']:'off';

            }


            if($invoice)
            {
                $invoice_data =  $request->session()->get('invoice_data') ;


                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if(session()->has('mollie_payment_id'))
                {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));
                     $invoice_data =  $request->session()->get('invoice_data') ;

                    if($payment->isPaid())
                    {
                        $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice_id;
                        $invoice_payment->amount         = isset($invoice_data['total_price'])?$invoice_data['total_price']:0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = 'mollie';
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
                            'amount'  => $invoice_data['total_price'],
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
                            return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed! '));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
                        }

                    }
                }else{
                    if(\Auth::check())
                    {
                        return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed! '));
                    }
                    else
                    {
                        return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
                    }

                }

            }else{
                if(\Auth::check())
                {
                    return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice not found. '));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }
            }

        }else{
            if(\Auth::check())
                {
                    return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice not found. '));
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

        $this->currancy =isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';

        $this->api_key = isset($admin_payment_setting['mollie_api_key'])?$admin_payment_setting['mollie_api_key']:'';
        $this->profile_id = isset($admin_payment_setting['mollie_profile_id'])?$admin_payment_setting['mollie_profile_id']:'';
        $this->partner_id = isset($admin_payment_setting['mollie_partner_id'])?$admin_payment_setting['mollie_partner_id']:'';
        $this->is_enabled = isset($admin_payment_setting['is_mollie_enabled'])?$admin_payment_setting['is_mollie_enabled']:'off';
        return $this;
    }
}
