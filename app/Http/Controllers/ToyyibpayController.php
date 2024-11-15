<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;


class ToyyibpayController extends Controller
{
    public $secretKey, $callBackUrl, $returnUrl, $categoryCode, $is_enabled, $invoiceData;

    public function __construct()
    {
        // if (\Auth::user()->type == 'company') {
        //     $payment_setting = Utility::getAdminPaymentSetting();
        // } else {
        //     $payment_setting = Utility::getCompanyPaymentSetting($this->invoiceData);
        // }


        $payment_setting = Utility::set_payment_settings();


        $this->secretKey = isset($payment_setting['toyyibpay_secret_key']) ? $payment_setting['toyyibpay_secret_key'] : '';
        $this->categoryCode = isset($payment_setting['category_code']) ? $payment_setting['category_code'] : '';
        $this->is_enabled = isset($payment_setting['is_toyyibpay_enabled']) ? $payment_setting['is_toyyibpay_enabled'] : 'off';
    }

    public function index()
    {
        return view('payment');
    }


    public function invoicepaywithtoyyibpay(Request $request)
    {

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
        $client   = $invoice->deal->clients->first();
;
        if (\Auth::check()) {
            $settings = \DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name')->toArray();
            $user     = \Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
            $settings = Utility::settingById($invoice->created_by);
        }

        $get_amount = $request->amount;


        if ($invoice) {
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                if(Auth::check()){
                    $name = Auth::user()->invoiceNumberFormat($settings, $invoice->invoice_id);
                }else{
                    $user = User::where('id', $invoice->created_by)->first();
                    $name = $user->invoiceNumberFormat($settings, $invoice->invoice_id);

                }

                $this->callBackUrl = route('invoice.toyyibpay.status', [$invoice->id,$get_amount]);
                $this->returnUrl = route('invoice.toyyibpay.status', [$invoice->id,$get_amount]);
            }

                $Date = date('d-m-Y');
                $ammount = $get_amount;
                $billExpiryDays = 3;
                $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
                $billContentEmail = "Thank you for purchasing our product!";
                $some_data = array(
                    'userSecretKey' => $this->secretKey,
                    'categoryCode' => $this->categoryCode,
                    'billName' => $name,
                    'billDescription' => $name,
                    'billPriceSetting' => 1,
                    'billPayorInfo' => 1,
                    'billAmount' => 100 * $ammount,
                    'billReturnUrl' => $this->returnUrl,
                    'billCallbackUrl' => $this->callBackUrl,
                    'billExternalReferenceNo' => 'AFR341DFI',
                    'billTo' => $client->name,
                    'billEmail' => $client->email,
                    'billPhone' => '0000000000',
                    'billSplitPayment' => 0,
                    'billSplitPaymentArgs' => '',
                    'billPaymentChannel' => '0',
                    'billContentEmail' => $billContentEmail,
                    'billChargeToCustomer' => 1,
                    'billExpiryDate' => $billExpiryDate,
                    'billExpiryDays' => $billExpiryDays

                );
               
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
                $result = curl_exec($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);
                $obj = json_decode($result);
                return redirect('https://toyyibpay.com/' . $obj[0]->BillCode);

                return redirect()->route('customer.invoice.show',\Crypt::encrypt($invoice_id))->back()->with('error', __('Unknown error occurred'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $payment_setting = Utility::set_payment_settings();

        $user             = Auth::user();
        $invoice    = Invoice::find($invoice_id);
        $invoices=Invoice::where('id',$invoice_id)->first();
        if(\Auth::check())
        {
             $user = Auth::user();
        }
        else
        {
           $user=User::where('id',$invoices->created_by)->first();

        }


        if ($request->status_id == 3) {
            if(\Auth::check())
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('error', __('Your Transaction is failed, please try again'));
                        }
                        else
                        {
                             return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Your Transaction is failed, please try again'));
                        }

        }else if( $request->status_id == 2){
            if(\Auth::check())
            {
                return redirect()->route('invoices.show', $invoice_id)->with('error', __('Your transaction is pending'));
            }
            else
            {
                 return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Your transaction is pending'));
            }


        }else if( $request->status_id == 1){

            if($invoice)
            {
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                $invoice_payment->invoice_id     = $invoice->id;
                $invoice_payment->amount         = isset($amount) ? $amount : 0;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->payment_type   = __('Toyyibpay');
                $invoice_payment->client_id      = $user->id;
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
            else
            {
                if(\Auth::check())
                {
                     return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction has been failed! '));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
                }
            }
        }
    }

}
