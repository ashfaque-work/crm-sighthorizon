<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PaytmWallet;
use App\Models\InvoicePayment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Session;
use App\Models\Deal;


class PaytmPaymentController extends Controller
{
    public $currancy;

    public function __construct()
    {
        $this->middleware('XSS');
    }

    public function invoicePayWithPaytm(Request $request){

       // $this->paymentSetting();

        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
            'mobile' => 'required'
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
            $this->paymentSetting();
        }else{

            $admin_payment_setting = Utility::non_auth_payment_settings($user->id);

            $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
            config(
                [
                    'services.paytm-wallet.env' => isset($admin_payment_setting['paytm_mode'])?$admin_payment_setting['paytm_mode']:'',
                    'services.paytm-wallet.merchant_id' => isset($admin_payment_setting['paytm_merchant_id'])?$admin_payment_setting['paytm_merchant_id']:'',
                    'services.paytm-wallet.merchant_key' =>  isset($admin_payment_setting['paytm_merchant_key'])?$admin_payment_setting['paytm_merchant_key']:'',
                    'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                    'services.paytm-wallet.channel' => 'WEB',
                    'services.paytm-wallet.industry_type' =>isset($admin_payment_setting['paytm_industry_type'])?$admin_payment_setting['paytm_industry_type']:'',
                ]
            );
        }


        if($invoice->getDue() < $request->amount){
            // return Utility::error_res('not correct amount');
        }

        // $call_back = route('invoice.paytm',[encrypt($request->invoice_id)]);

        $payment = PaytmWallet::with('receive');
        $payment->prepare(
            [
                'order' => date('Y-m-d') . '-' . strtotime(date('Y-m-d H:i:s')),
                'user' => $user->id,
                'mobile_number' => $request->mobile,
                'email' => $user->email,
                'amount' => $request->amount,
                'invoice_id' => $request->invoice_id,
                // 'callback_url' => $call_back,
                // 'callback_url' => route('invoice.paytm', '_token=' . Session::token().'&invoice_id=' . encrypt($request->invoice_id)),
                'callback_url' => route('invoice.paytm', '_token=' . Session::token().'&invoice_id=' . encrypt($request->invoice_id)),
            ]
        );

        return $payment->receive();
    }

    public function getInvociePaymentStatus(Request $request){


        $invoice_id= $request->invoice_id;

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
                $this->paymentSetting();
            }else{

                $admin_payment_setting = Utility::non_auth_payment_settings($user->id);

                $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
                    config(
                    [
                        'services.paytm-wallet.env' => isset($admin_payment_setting['paytm_mode'])?$admin_payment_setting['paytm_mode']:'',
                        'services.paytm-wallet.merchant_id' => isset($admin_payment_setting['paytm_merchant_id'])?$admin_payment_setting['paytm_merchant_id']:'',
                        'services.paytm-wallet.merchant_key' =>  isset($admin_payment_setting['paytm_merchant_key'])?$admin_payment_setting['paytm_merchant_key']:'',
                        'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                        'services.paytm-wallet.channel' => 'WEB',
                        'services.paytm-wallet.industry_type' =>isset($admin_payment_setting['paytm_industry_type'])?$admin_payment_setting['paytm_industry_type']:'',
                    ]
                );
            }

            if($invoice)
            {
                $invoice_data =  $request->session()->get('invoice_data');
                try
                {
                    $transaction = PaytmWallet::with('receive');
                  $invoice_payment                 = new InvoicePayment();
                        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                        $invoice_payment->invoice_id     = $invoice_id;
                        $invoice_payment->amount         = isset($request->TXNAMOUNT) ? $request->TXNAMOUNT : 0;
                        $invoice_payment->date           = date('Y-m-d');
                        $invoice_payment->payment_id     = 0;
                        $invoice_payment->payment_type   = 'paytm';
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


                }
                catch(\Exception $e)
                {
                 
                    if(\Auth::check())
                    {
                        return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed!'));
                    }
                    else
                    {
                        return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
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
        // if(\Auth::user()->type == 'Owner')
        // {
        $admin_payment_setting = Utility::payment_settings();
        $this->currancy = isset($admin_payment_setting['currency'])?$admin_payment_setting['currency']:'';
        config(
            [
                'services.paytm-wallet.env' => isset($admin_payment_setting['paytm_mode'])?$admin_payment_setting['paytm_mode']:'',
                'services.paytm-wallet.merchant_id' => isset($admin_payment_setting['paytm_merchant_id'])?$admin_payment_setting['paytm_merchant_id']:'',
                'services.paytm-wallet.merchant_key' =>  isset($admin_payment_setting['paytm_merchant_key'])?$admin_payment_setting['paytm_merchant_key']:'',
                'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                'services.paytm-wallet.channel' => 'WEB',
                'services.paytm-wallet.industry_type' =>isset($admin_payment_setting['paytm_industry_type'])?$admin_payment_setting['paytm_industry_type']:'',
            ]
        );
        }
    // }
}
