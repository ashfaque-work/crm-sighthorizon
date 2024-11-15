<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Models\PurchasedProducts;
use App\Models\ProductCoupon;
use App\Models\Store;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Shipping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;


class SspayController extends Controller
{
    public $secretKey, $callBackUrl, $returnUrl, $categoryCode, $is_enabled, $invoiceData;

    public function __construct()
    {

        $payment_setting = Utility::set_payment_settings();

        $this->secretKey = isset($payment_setting['sspay_secret_key']) ? $payment_setting['sspay_secret_key'] : '';
        $this->categoryCode                = isset($payment_setting['sspay_category_code']) ? $payment_setting['sspay_category_code'] : '';
        $this->is_enabled          = isset($payment_setting['is_sspay_enabled']) ? $payment_setting['is_sspay_enabled'] : 'off';
        return $this;
    }

    public function SspayPaymentPrepare(Request $request)
    {
        try {
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
            $plan   = Plan::find($planID);
            if ($plan) {
                $get_amount = $plan->{$request->sspay_payment_frequency . '_price'};
                $frequency = $request->sspay_payment_frequency;
                if (!empty($request->coupon)) {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if (!empty($coupons)) {
                        $usedCoupun     = $coupons->used_coupon();
                        $discount_value = ($get_amount / 100) * $coupons->discount;
                        $get_amount          = $get_amount - $discount_value;

                        if ($coupons->limit == $usedCoupun) {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }
                $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
                $this->callBackUrl = route('plan.sspay.callback', [$plan->id, $get_amount, $frequency, $coupon]);
                $this->returnUrl = route('plan.sspay.callback', [$plan->id, $get_amount, $frequency, $coupon]);
                $Date = date('d-m-Y');
                $ammount = $get_amount;
                $billName = $plan->name;
                $description = $plan->name;
                $billExpiryDays = 3;
                $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
                $billContentEmail = "Thank you for purchasing our product!";

                $some_data = array(
                    'userSecretKey' => $this->secretKey,
                    'categoryCode' => $this->categoryCode,
                    'billName' => $billName,
                    'billDescription' => $description,
                    'billPriceSetting' => 1,
                    'billPayorInfo' => 1,
                    'billAmount' => 100 * $ammount,
                    'billReturnUrl' => $this->returnUrl,
                    'billCallbackUrl' => $this->callBackUrl,
                    'billExternalReferenceNo' => 'AFR341DFI',
                    'billTo' => \Auth::user()->name,
                    'billEmail' => \Auth::user()->email,
                    'billPhone' => '000000000',
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
                curl_setopt($curl, CURLOPT_URL, 'https://sspay.my/index.php/api/createBill');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
                $result = curl_exec($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);
                $obj = json_decode($result);
                return redirect('https://sspay.my/' . $obj[0]->BillCode);
            } else {
                return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
            }
        } catch (Exception $e) {
            return redirect()->route('plans.index')->with('error', __($e->getMessage()));
        }
    }

    public function SspayPlanGetPayment(Request $request, $planId, $getAmount, $frequency, $couponCode)
    {
        if ($couponCode != 0) {
            $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
            $request['coupon_id'] = $coupons->id;
        } else {
            $coupons = null;
        }

        $plan = Plan::find($planId);
        $user = auth()->user();
        // $request['status_id'] = 1;

        // 1=success, 2=pending, 3=fail
        try {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            if ($request->status_id == 3) {
                $statuses = 'Fail';
                $order                 = new Order();
                $order->order_id       = $orderID;
                $order->name           = $user->name;
                $order->email           = '';
                $order->card_number    = '';
                $order->card_exp_month = '';
                $order->card_exp_year  = '';
                $order->plan_name      = $plan->name;
                $order->plan_id        = $plan->id;
                $order->price          = $getAmount;
                $order->price_currency = env('CURRENCY');
                $order->txn_id = '';
                $order->payment_type   = __('Sspay');
                $order->payment_status = $statuses;
                $order->receipt        = '';
                $order->user_id        = $user->id;
                $order->save();
                return redirect()->route('plans.index')->with('error', __('Your Transaction is fail please try again'));
            } else if ($request->status_id == 2) {
                $statuses = 'pending';
                $order                 = new Order();
                $order->order_id       = $orderID;
                $order->name           = $user->name;
                $order->email           = '';
                $order->card_number    = '';
                $order->card_exp_month = '';
                $order->card_exp_year  = '';
                $order->plan_name      = $plan->name;
                $order->plan_id        = $plan->id;
                $order->price          = $getAmount;
                $order->price_currency = env('CURRENCY');
                $order->txn_id = '';
                $order->payment_type   = __('Sspay');
                $order->payment_status = $statuses;
                $order->receipt        = '';
                $order->user_id        = $user->id;
                $order->save();
                return redirect()->route('plans.index')->with('error', __('Your transaction on pending'));
            } else if ($request->status_id == 1) {
                $statuses = 'success';
                $order                 = new Order();
                $order->order_id       = $orderID;
                $order->name           = $user->name;
                $order->email           = '';
                $order->card_number    = '';
                $order->card_exp_month = '';
                $order->card_exp_year  = '';
                $order->plan_name      = $plan->name;
                $order->plan_id        = $plan->id;
                $order->price          = $getAmount;
                $order->price_currency = env('CURRENCY');
                $order->txn_id = '';
                $order->payment_type   = __('Sspay');
                $order->payment_status = $statuses;
                $order->receipt        = '';
                $order->user_id        = $user->id;
                $order->save();
                $assignPlan = $user->assignPlan($plan->id, $frequency);
                $coupons = Coupon::find($request->coupon_id);
                if (!empty($request->coupon_id)) {
                    if (!empty($coupons)) {
                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
                        $userCoupon->save();
                        $usedCoupun = $coupons->used_coupon();
                        if ($coupons->limit <= $usedCoupun) {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }
                    }
                }
                if ($assignPlan['is_success']) {
                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
                } else {
                    return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                }
            } else {
                return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
            }
        } catch (Exception $e) {
            return redirect()->route('plans.index')->with('error', __($e->getMessage()));
        }
    }

    public function invoicepaywithsspaypay(Request $request)
    {
        $invoice = Invoice::find($request->invoice_id);
        $this->invoiceData = $invoice;
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
                if (Auth::check()) {
                    $name = Auth::user()->invoiceNumberFormat($settings, $invoice->invoice_id);
                } else {
                    $user = User::where('id', $invoice->created_by)->first();
                    $name = $user->invoiceNumberFormat($settings, $invoice->invoice_id);
                }
                $this->callBackUrl = route('customer.sspay', [$invoice->id, $get_amount]);
                $this->returnUrl = route('customer.sspay', [$invoice->id, $get_amount]);
            }
            $Date = date('d-m-Y');
            $ammount = $get_amount;
            $billExpiryDays = 3;
            $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
            $billContentEmail = "Thank you for purchasing our product!";
            $some_data = array(
                'userSecretKey' => $this->secretKey,
                'categoryCode' => $this->categoryCode,
                'billName' => "invoice",
                'billDescription' => "invoice",
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => 100 * $ammount,
                'billReturnUrl' => $this->returnUrl,
                'billCallbackUrl' => $this->callBackUrl,
                'billExternalReferenceNo' => 'AFR341DFI',
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => '0000000000',
                'billSplitPayment' => 0,
                'billSplitPaymentArgs' => '',
                'billPaymentChannel' => '0',
                'billContentEmail' => $billContentEmail,
                'billChargeToCustomer' => 1,
                'billExpiryDate' => $billExpiryDate,
                'billExpiryDays' => $billExpiryDays,
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://sspay.my/index.php/api/createBill');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
            $result = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $obj = json_decode($result);
            return redirect('https://sspay.my/' . $obj[0]->BillCode);

            return redirect()->route('invoices.show', \Crypt::encrypt($invoice->id))->back()->with('error', __('Unknown error occurred'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $payment_setting = Utility::set_payment_settings();

        $user             = Auth::user();
        $invoice    = Invoice::find($invoice_id);
        $invoices = Invoice::where('id', $invoice_id)->first();
        $payment_id = \Session::get('PayerID');
        \Session::forget('PayerID');
        if (empty($request->PayerID || empty($request->token))) {
            return redirect()->back()->with('error', __('Payment failed'));
        }
        if (\Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoices->created_by)->first();
        }


        if ($request->status_id == 3) {
            if (\Auth::check()) {
                return redirect()->route('invoices.show', $invoice_id)->with('error', __('Your Transaction is failed, please try again'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Your Transaction is failed, please try again'));
            }
        } else if ($request->status_id == 2) {
            if (\Auth::check()) {
                return redirect()->route('invoices.show', $invoice_id)->with('error', __('Your transaction is pending'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Your transaction is pending'));
            }
        } else if ($request->status_id == 1) {

            if ($invoice) {
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber($user);
                $invoice_payment->invoice_id     = $invoice->id;
                $invoice_payment->amount         = isset($amount) ? $amount : 0;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->payment_type   = __('Sspay');
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->notes          = '';
                $invoice_payment->save();


                if (($invoice->getDue() - $invoice_payment->amount) == 0) {
                    $invoice->status = 'paid';
                    $invoice->save();
                }

                $settings  = Utility::settings($invoice->created_by);
                $deal_id = $invoice->deal_id;
                $deal = Deal::findOrFail($deal_id);
                $obj = [
                    'payer_name' => ucfirst($user->name),
                    'amount'  => $invoice_payment->amount,
                    'payment_type' => $invoice_payment->payment_type,
                    'deal_name' => $deal->name,

                ];
                if (isset($settings['payment_notification']) && $settings['payment_notification'] == 1) {
                    \Utility::send_slack_msg('new_payment', $obj, $invoice->created_by);
                }

                if (isset($settings['telegram_payment_notification']) && $settings['telegram_payment_notification'] == 1) {
                    \Utility::send_telegram_msg('new_payment', $obj, $invoice->created_by);
                }
                $module = 'New Payment';
                $webhook =  Utility::webhookSetting($module, $invoice->created_by);
                if ($webhook) {
                    $parameter = json_encode($invoice_payment);
                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {
                        return redirect()->back()->with('success', __('Payment added Successfully'));
                    } else {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }


                if (\Auth::check()) {
                    return redirect()->route('invoices.show', $invoice_id)->with('success', __('Payment added Successfully'));
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                }
            } else {
                if (\Auth::check()) {
                    return redirect()->route('invoices.show', $invoice_id)->with('error', __('Transaction has been failed! '));
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
                }
            }
        }
    }
}
