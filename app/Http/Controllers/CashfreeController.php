<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\UserCoupon;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Models\InvoicePayment;
use App\Models\Deal;
use Exception;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;

class CashfreeController extends Controller
{
    public function paymentConfig()
    {
        $payment_setting = Utility::payment_settings();

        config(
            [
                'services.cashfree.key' => isset($payment_setting['cashfree_key']) ? $payment_setting['cashfree_key'] : '',
                'services.cashfree.secret' => isset($payment_setting['cashfree_secret']) ? $payment_setting['cashfree_secret'] : '',
            ]
        );
    }



    public function invoicepaywithcashefree (Request $request)
    {
        try{
            $invoice = Invoice::find($request->invoice_id);
            $user = User::where('id',$invoice->created_by)->first();
            $paymentSetting = Utility::non_auth_payment_settings($user->id);

            config(
                [
                    'services.cashfree.key' => isset($paymentSetting['cashfree_key']) ? $paymentSetting['cashfree_key'] : '',
                    'services.cashfree.secret' => isset($paymentSetting['cashfree_secret']) ? $paymentSetting['cashfree_secret'] : '',
                    ]
                );

                $url = config('services.cashfree.url');

                $get_amount = $request->amount;
                if($invoice && $get_amount != 0)
                {
                    if($get_amount > $invoice->getDue())
                    {
                        return redirect()->back()->with('error', __('Invalid amount.'));
                    }else{

                        $headers = array(
                            "Content-Type: application/json",
                            "x-api-version: 2022-01-01",
                            "x-client-id: " .  config('services.cashfree.key'),
                            "x-client-secret: " .  config('services.cashfree.secret'),
                        );


                        $data = json_encode([
                            // 'order_id' => $orderID,
                            'order_amount' => $request->amount,
                            "order_currency" => 'INR',
                            "order_name" => $user['name'],
                            "customer_details" => [
                                "customer_id" => 'customer_' . $user['id'],
                                "customer_name" => $user['name'],
                                "customer_email" => $user['email'],
                                "customer_phone" => '1234567890',
                            ],
                            "order_meta" => [
                                "return_url" => route('customer.cashefree') . '?order_id={order_id}&order_token={order_token}&invoice_id=' . $invoice->id . '&amount=' . $get_amount . ''
                                ]
                            ]);

                        try {

                            $curl = curl_init($url);
                            curl_setopt($curl, CURLOPT_URL, $url);
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

                            $resp = curl_exec($curl);
                            curl_close($curl);
                            return redirect()->to(json_decode($resp)->payment_link);
                        } catch (\Throwable $th) {
                            return redirect()->back()->with('error', 'Currency Not Supported.Contact To Your Site Admin');
                        }
                    }
                }
        }
        catch (\Throwable $e) {
            return redirect()->back()->with('error', __($e->getMessage()));
        }
    }

    public function getInvoicePaymentStatus(Request $request)
    {

        $invoice_id = $request->invoice_id;
        $invoice    = Invoice::find($request->invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $objUser = $user;

        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));

        $paymentSetting = Utility::non_auth_payment_settings($user->id);
        config(
            [
                'services.cashfree.key' => isset($paymentSetting['cashfree_key']) ? $paymentSetting['cashfree_key'] : '',
                'services.cashfree.secret' => isset($paymentSetting['cashfree_secret']) ? $paymentSetting['cashfree_secret'] : '',
                ]
            );


        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', config('services.cashfree.url') . '/' . $request->get('order_id') . '/settlements', [
            'headers' => [
                'accept' => 'application/json',
                'x-api-version' => '2022-09-01',
                "x-client-id" => config('services.cashfree.key'),
                "x-client-secret" => config('services.cashfree.secret')
            ],
        ]);
        $respons = json_decode($response->getBody());
        if ($respons->order_id && $respons->cf_payment_id != NULL) {

            $response = $client->request('GET', config('services.cashfree.url') . '/' . $respons->order_id . '/payments/' . $respons->cf_payment_id . '', [
                'headers' => [
                    'accept' => 'application/json',
                    'x-api-version' => '2022-09-01',
                    'x-client-id' => $paymentSetting['cashfree_key'],
                    'x-client-secret' => $paymentSetting['cashfree_secret'],
                ],
            ]);
            $info = json_decode($response->getBody());
            try {
                if ($info->payment_status == "SUCCESS") {

                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->invoice_id     = $invoice_id;
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->amount         = $request->amount;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = "";
                $invoice_payment->payment_type   = 'Cashfree';
                $invoice_payment->save();
                $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');
                if ($invoice_getdue <= 0.0) {

                    Invoice::change_status($invoice->id, 3);
                } else {

                    Invoice::change_status($invoice->id, 2);
                }
            }
            else {
                return redirect()->back()->with('error', __('Your Transaction is fail please try again'));
            }
                //Notification
                $setting  = Utility::settingsById($objUser->creatorId());
                if (isset($setting['payment_notification']) && $setting['payment_notification'] == 1) {
                    $uArr = [
                        'amount' => $invoice_payment->amount,
                        'payment_type' => $invoice_payment->payment_type,
                        'user_name' => $invoice->name,
                    ];
                    Utility::send_twilio_msg($invoice->contacts->phone, 'new_invoice_payment', $uArr, $invoice->created_by);
                }

                //webhook
                $module = 'New Invoice Payment';
                $webhook =  Utility::webhookSetting($module, $invoice->created_by);
                if ($webhook) {
                    $parameter = json_encode($invoice);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status != true) {
                        $msg = "Webhook call failed.";
                    }
                }
                if (Auth::user()) {
                    return redirect()->route('invoice.show', $invoice_id)->with('success', __('Invoice paid Successfully!!') . ((isset($msg) ? '<br> <span class="text-danger">' . $msg . '</span>' : '')));
                } else {
                    $id = \Crypt::encrypt($invoice_id);
                    return redirect()->route('pay.invoice', $id)->with('success', __('Invoice paid Successfully!!') . ((isset($msg) ? '<br> <span class="text-danger">' . $msg . '</span>' : '')));
                }

                if (Auth::check()) {
                    return redirect()->route('invoices.show', $invoice_id['invoice_id'])->with('success', __('Invoice paid Successfully!'));
                } else {
                    return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('ERROR', __('Transaction fail'));
                }
            } catch (\Exception $e) {

                if (Auth::check()) {
                    return redirect()->route('invoice.show', $invoice_id['invoice_id'])->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', $e->getMessage());
                }
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('invoices.show', $invoice_id['invoice_id'])->with('error', __('Invoice not found.'));
            } else {
                return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice not found.'));
            }
        }
    }

}
