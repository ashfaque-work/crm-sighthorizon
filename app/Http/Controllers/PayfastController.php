<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Deal;




class PayfastController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            $payment_setting = Utility::payment_settings();
            $planID = $request->plan_id;
            $frequency = $request->frequency;
            $plan = Plan::find($planID);
            if ($plan) {
                $plan_amount = $plan->{$request->frequency . '_price'};
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
                $user = Auth::user();
                if(isset($request->coupon_code) && !empty($request->coupon_code))
                {
                    $coupons = Coupon::where('code', strtoupper($request->coupon_code))->where('is_active', '1')->first();
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order = $order_id;
                        $userCoupon->save();
                        $usedCoupun = $coupons->used_coupon();
                        if ($coupons->limit <= $usedCoupun) {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }
                        $discount_value = ($plan_amount / 100) * $coupons->discount;
                        $plan_amount          = $plan_amount - $discount_value;
                    }
                }
                $success = Crypt::encrypt([
                    'plan' => $plan->toArray(),
                    'order_id' => $order_id,
                    'plan_amount' => $plan_amount,
                    'frequency'    => $frequency
                ]);
                $data = array(
                    // Merchant details
                    'merchant_id' => !empty($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '',
                    'merchant_key' => !empty($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '',
                    'return_url' => route('payfast.payment.success',$success),
                    'cancel_url' => route('plans.index'),
                    'notify_url' => route('plans.index'),
                    // Buyer details
                    'name_first' => $user->name,
                    'name_last' => 'abc',
                    'email_address' => $user->email,
                    // Transaction details
                    'm_payment_id' => $order_id, //Unique payment ID to pass through to notify_url
                    'amount' => number_format(sprintf('%.2f', $plan_amount), 2, '.', ''),
                    'item_name' => $plan->name,
                );
                $passphrase = !empty($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
                $signature = $this->generateSignature($data, $passphrase);
                $data['signature'] = $signature;
                $htmlForm = '';

                foreach ($data as $name => $value) {
                    $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
                }

                return response()->json([
                    'success' => true,
                    'inputs' => $htmlForm,
                ]);

            }
        }

    }
    public function generateSignature($data, $passPhrase = null)
    {

        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }

        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    public function success($success){
        $payment_setting = Utility::payment_settings();


        try{
            $user = Auth::user();
            $data = Crypt::decrypt($success);
            $order = new Order();
            $order->order_id = $data['order_id'];
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $data['plan']['name'];
            $order->plan_id = $data['plan']['id'];
            $order->price = $data['plan_amount'];
            $order->price_currency = !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
            $order->txn_id = $data['order_id'];
            $order->payment_type = __('PayFast');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();
            $assignPlan = $user->assignPlan($data['plan']['id'],$data['frequency']);

            if ($assignPlan['is_success']) {
                return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
            } else {
                return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
            }
        }catch(Exception $e){
            return redirect()->route('plans.index')->with('error', __($e));
        }
    }

    public function invoicepaywithpayfast(Request $request)
    {

        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        // $user = User::where('id', $invoice->created_by)->first();
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
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

        $success = Crypt::encrypt([

            'order_id' => $order_id,
            'amount' => $get_amount,
            'invoice_id' => $invoice->id
        ]);

        $data = array(
            // Merchant details
            'merchant_id' => !empty($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '',
            'merchant_key' => !empty($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '',
            'return_url' => route('invoice.payfast', $success),
            'cancel_url' => route('invoices.show',$invoice->id),
            'notify_url' => route('invoices.show',$invoice->id),
            // Buyer details
            'name_first' => $user->name,
            'name_last' => '',
            'email_address' => $user->email,
            // Transaction details
            'm_payment_id' => $order_id, //Unique payment ID to pass through to notify_url
            'amount' => number_format(sprintf('%.2f', $get_amount), 2, '.', ''),
            'item_name' => 'Invoice',
        );

        $passphrase = !empty($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
        $signature = $this->generateSignature($data, $passphrase);
        $data['signature'] = $signature;

        $htmlForm = '';

        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
        }

        return response()->json([
            'success' => true,
            'inputs' => $htmlForm,
        ]);
    }

    public function invoicepayfaststatus(Request $request, $success)
    {
        $payment_setting = Utility::set_payment_settings();
        $user             = Auth::user();
        $invoice_id = Crypt::decrypt($success);
        $invoice = Invoice::find($invoice_id['invoice_id']);
        $get_amount = $invoice_id['amount'];

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
                $invoice_payment->invoice_id     = $invoice_id['invoice_id'];
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber( $user);
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->amount         = $get_amount;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = "";
                $invoice_payment->payment_type   = 'Payfast';
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

                if(Auth::check()){
                    return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment added Successfully'));
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice Paid Successfully'));
                }
            } catch (\Exception $e) {
                if(Auth::check()){
                    return redirect()->route('invoices.show', $invoice->id)->with('error',$e->getMessage());
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success',$e->getMessage());
                }
            }
        } else {
            if(Auth::check()){
                return redirect()->route('invoices.show', $invoice->id)->with('error',__('Invoice not found.'));
            }else{
                return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice not found.'));
            }
        }
    }
}
