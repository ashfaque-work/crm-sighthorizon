<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Models\InvoicePayment;
use App\Models\Deal;
use App\Models\UserCoupon;
use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Models\PurchasedProducts;
use App\Models\ProductCoupon;
use App\Models\Store;
use GuzzleHttp\Client;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Shipping;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use PhpParser\Node\Stmt\TryCatch;

class BenefitPaymentController extends Controller
{
    //

    public function invoicepaywithbenefit(Request $request)
    {
        $admin_payment_setting = Utility::payment_settings();
        $secret_key = $admin_payment_setting['benefit_secret_key'];
        $invoice = Invoice::find($request->invoice_id);
        $user = User::where('id',$invoice->created_by)->first();
        $paymentSetting = Utility::non_auth_payment_settings($user->id);

        try {
            $get_amount = $request->amount;


            if ($invoice) {

                if ($get_amount > $invoice->getDue()) {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                }
                else{

                    $userData =
                    [
                        "amount" => $get_amount,
                        "currency" => !empty($paymentSetting['currency']) ?  $paymentSetting['currency'] : 'USD',
                        "customer_initiated" => true,
                        "threeDSecure" => true,
                        "save_card" => false,
                        "metadata" => ["udf1" => "Metadata 1"],
                        "reference" => ["transaction" => "txn_01", "order" => "ord_01"],
                        "receipt" => ["email" => true, "sms" => true],
                        "customer" => ["first_name" => $user->name, "middle_name" => "", "last_name" => "", "email" => $user->email, "phone" => ["country_code" => 965, "number" => 51234567]],
                        "source" => ["id" => "src_bh.benefit"],
                        "post" => ["url" => "https://webhook.site/fd8b0712-d70a-4280-8d6f-9f14407b3bbd"],
                        "redirect" => ["url" => route('customer.benefit', ['invoice' => $invoice->id, 'amount' => $get_amount])],

                    ];
                $responseData = json_encode($userData);
                $client = new Client();
                try {
                    $response = $client->request('POST', 'https://api.tap.company/v2/charges', [
                        'body' => $responseData,
                        'headers' => [
                            'Authorization' => 'Bearer ' . $secret_key,
                            'accept' => 'application/json',
                            'content-type' => 'application/json',
                        ],
                    ]);
                } catch (\Throwable $th) {
                    return redirect()->back()->with('error','Currency Not Supported.Contact To Your Site Admin');
                }

                $data = $response->getBody();
                $res = json_decode($data);
                return redirect($res->transaction->url);
                }
            }
        } catch (Exception $e) {
             
                return redirect()->back()->with('error', $e);
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
                $invoice_payment->payment_type   = 'Benefit';
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
