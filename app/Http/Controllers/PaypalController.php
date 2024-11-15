<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Models\Retainer;
use App\Models\RetainerPayment;
use App\Models\Shipping;
use App\Models\Store;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\PurchasedProducts;
use App\Models\Deal;


class PaypalController extends Controller
{
    private $invoiceData;
    public $paypal_client_id;
    public $paypal_mode;
    public $paypal_secret_key;
    public $currancy_symbol;
    public $currancy;

    public function paymentConfig()
    {
        if(\Auth::check())
        {
            $payment_setting = Utility::payment_settings();
        }
        else
        {
            $payment_setting = Utility::set_payment_settings($this->invoiceData);
        }

        if($payment_setting['paypal_mode'] == 'live'){
            config([
                      'paypal.live.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                      'paypal.live.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                      'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
                  ]);
      }else{
           config([
                      'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                      'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                      'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
                  ]);
      }
    }

    public function clientPayWithPaypal(Request $request, $invoice_id)
    {
        $this->paymentConfig();

        $invoice = Invoice::find($invoice_id);
        if (Auth::check()) {
            $settings = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');
            $user = \Auth::user();


        } else {
            $user = User::where('id', $invoice->created_by)->first();
            $settings = Utility::settingById($invoice->created_by);
        }

        $get_amount = $request->amount;

        $request->validate(['amount' => 'required|numeric|min:0']);

        $provider = new PayPalClient;


        $provider->setApiCredentials(config('paypal'));

        if ($invoice) {

            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                // $name = User::invoiceNumberFormat($settings, $invoice->invoice_id);
                // $name = Auth::user()->invoiceNumberFormat($invoice->invoice_id);


                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('client.get.payment.status', [$invoice->id, $get_amount]),
                        "cancel_url" => route('client.get.payment.status', [$invoice->id, $get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => Utility::getValByName('site_currency'),
                                "value" => $get_amount,
                            ],
                        ],
                    ],
                ]);

                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('invoices.show', \Crypt::encrypt($invoice->id))
                        ->with('error', 'Something went wrong.');
                } else {
                    return redirect()
                        ->route('invoices.show', \Crypt::encrypt($invoice->id))
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }

                return redirect()->route('invoices.show', \Crypt::encrypt($invoice_id))->back()->with('error', __('Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function clientGetPaymentStatus(Request $request, $invoice_id, $amount)
    {
        $this->paymentConfig();
        $invoice = Invoice::find($invoice_id);

        if (\Auth::check()) {
            $user = \Auth::user();
        } else {
            $user = User::where('id',$invoice->created_by)->first();
        }
        $payment_id = Session::get('paypal_payment_id');
        Session::forget('paypal_payment_id');

        if (empty($request->PayerID || empty($request->token))) {
            return redirect()->back()->with('error', __('Payment failed'));
        }
        $provider = new PayPalClient;
        $response = $provider->showAuthorizedPaymentDetails($request->PayerID);

        try {
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            if($order_id)
                {
                    $invoice_payment                 = new InvoicePayment();
                    $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                    $invoice_payment->invoice_id     = $invoice->id;
                    $invoice_payment->amount         = $amount;
                    $invoice_payment->date           = date('Y-m-d');
                    $invoice_payment->payment_id     = 0;
                    $invoice_payment->payment_type   = __('PAYPAL');
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
                        // $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                        // if($status == true)
                        // {
                        //     return redirect()->back()->with('success', __('Payment added Successfully'));
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

                }
                else
                {
                    if(\Auth::check())
                    {
                         return redirect()->route('invoices.show', $invoice_id)->with('error', __('Transaction has been ' . $status));
                    }
                    else
                    {
                        return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been ' . $status));
                    }


                }

        } catch (\Exception$e) {
            if (Auth::check()) {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed.'));
            } else {
                return redirect()->back()->with('success', __('Transaction has been complted.'));
            }
        }
    }

}
