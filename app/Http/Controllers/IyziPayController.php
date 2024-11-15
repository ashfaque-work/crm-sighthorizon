<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Order;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\ProductCoupon;
use App\Models\ProductVariantOption;
use App\Models\PurchasedProducts;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Models\Deal;
use Exception;

class IyziPayController extends Controller
{
    public $invoiceData;
    public function initiatePayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $frequency = $request->iyzipay_payment_frequency;
        $authuser  = \Auth::user();
        $adminPaymentSettings = Utility::payment_settings();
        $iyzipay_public_key = $adminPaymentSettings['iyzipay_public_key'];
        $iyzipay_secret_key = $adminPaymentSettings['iyzipay_secret_key'];
        $iyzipay_mode = $adminPaymentSettings['iyzipay_mode'];

        $currency = env('CURRENCY');
        $plan = Plan::find($planID);
        $coupon_id = '0';
        $price = $plan->{$request->iyzipay_payment_frequency . '_price'};
        $coupon_code = null;
        $discount_value = null;
        $coupons = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
        if ($coupons) {
            $coupon_code = $coupons->code;
            $usedCoupun     = $coupons->used_coupon();
            if ($coupons->limit == $usedCoupun) {
                $res_data['error'] = __('This coupon code has expired.');
            } else {
                $discount_value = ($price / 100) * $coupons->discount;
                $price  = $price - $discount_value;
                if ($price < 0) {
                    $price = $plan->price;
                }
                $coupon_id = $coupons->id;
            }
        }
        $res_data['total_price'] = $price;
        $res_data['coupon']      = $coupon_id;

        // set your Iyzico API credentials
        try {
            $setBaseUrl = ($iyzipay_mode == 'sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
            $options = new \Iyzipay\Options();
            $options->setApiKey($iyzipay_public_key);

            $options->setSecretKey($iyzipay_secret_key);
            $options->setBaseUrl($setBaseUrl); // or "https://api.iyzipay.com" for production
            $ipAddress = Http::get('https://ipinfo.io/?callback=')->json();
            $address = ($authuser->address) ? $authuser->address : 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1';
            // create a new payment request
            $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
            $request->setLocale('en');
            $request->setPrice($res_data['total_price']);
            $request->setPaidPrice($res_data['total_price']);
            $request->setCurrency($currency);
            $request->setCallbackUrl(route('iyzipay.payment.callback',[$plan->id,$price,$frequency,$coupon_code]));
            $request->setEnabledInstallments(array(1));
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId($authuser->id);
            $buyer->setName(explode(' ', $authuser->name)[0]);
            $buyer->setSurname(explode(' ', $authuser->name)[0]);
            $buyer->setGsmNumber("+" . $authuser->dial_code . $authuser->phone);
            $buyer->setEmail($authuser->email);
            $buyer->setIdentityNumber(rand(0, 999999));
            $buyer->setLastLoginDate("2023-03-05 12:43:35");
            $buyer->setRegistrationDate("2023-04-21 15:12:09");
            $buyer->setRegistrationAddress($address);
            $buyer->setIp($ipAddress['ip']);
            $buyer->setCity($ipAddress['city']);
            $buyer->setCountry($ipAddress['country']);
            $buyer->setZipCode($ipAddress['postal']);
            $request->setBuyer($buyer);
            $shippingAddress = new \Iyzipay\Model\Address();
            $shippingAddress->setContactName($authuser->name);
            $shippingAddress->setCity($ipAddress['city']);
            $shippingAddress->setCountry($ipAddress['country']);
            $shippingAddress->setAddress($address);
            $shippingAddress->setZipCode($ipAddress['postal']);
            $request->setShippingAddress($shippingAddress);
            $billingAddress = new \Iyzipay\Model\Address();
            $billingAddress->setContactName($authuser->name);
            $billingAddress->setCity($ipAddress['city']);
            $billingAddress->setCountry($ipAddress['country']);
            $billingAddress->setAddress($address);
            $billingAddress->setZipCode($ipAddress['postal']);
            $request->setBillingAddress($billingAddress);
            $basketItems = array();
            $firstBasketItem = new \Iyzipay\Model\BasketItem();
            $firstBasketItem->setId("BI101");
            $firstBasketItem->setName("Binocular");
            $firstBasketItem->setCategory1("Collectibles");
            $firstBasketItem->setCategory2("Accessories");
            $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $firstBasketItem->setPrice($res_data['total_price']);
            $basketItems[0] = $firstBasketItem;
            $request->setBasketItems($basketItems);

            $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, $options);
            return redirect()->to($checkoutFormInitialize->getpaymentPageUrl());
        } catch (\Exception $e) {

            return redirect()->route('plans.index')->with('errors', $e->getMessage());
        }
    }

    public function iyzipayCallback(Request $request,$planID,$price,$frequency,$coupanCode = null)
    {
        $plan = Plan::find($planID);
        $user = \Auth::user();
        $order = new Order();
        $order->order_id = time();
        $order->name = $user->name;
        $order->card_number = '';
        $order->card_exp_month = '';
        $order->card_exp_year = '';
        $order->plan_name = $plan->name;
        $order->plan_id = $plan->id;
        $order->price = $price;
        $order->price_currency = env('CURRENCY');
        $order->txn_id = time();
        $order->payment_frequency = $frequency;
        $order->payment_type = __('Iyzipay');
        $order->payment_status = 'success';
        $order->txn_id = '';
        $order->receipt = '';
        $order->user_id = $user->id;
        $order->save();
        $user = User::find($user->id);
        $coupons = Coupon::where('code', $coupanCode)->where('is_active', '1')->first();
        if (!empty($coupons)) {
            $userCoupon         = new UserCoupon();
            $userCoupon->user   = $user->id;
            $userCoupon->coupon = $coupons->id;
            $userCoupon->order  = $order->order_id;
            $userCoupon->save();
            $usedCoupun = $coupons->used_coupon();
            if ($coupons->limit <= $usedCoupun) {
                $coupons->is_active = 0;
                $coupons->save();
            }
        }
        $assignPlan = $user->assignPlan($plan->id,$frequency);


        if ($assignPlan['is_success']) {
            return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
        } else {
            return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
        }
    }

    public function invoicepaywithiyzipay(Request $request)
    {
        // $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice = Invoice::find($request->invoice_id);
        $this->invoiceData = $invoice;
        $authuser      = User::find($invoice->created_by);
        $companyPaymentSettings = Utility::invoice_payment_settings($authuser->id);

        $iyzipay_mode = $companyPaymentSettings['iyzipay_mode'];
        $iyzipay_public_key = $companyPaymentSettings['iyzipay_public_key'];
        $iyzipay_secret_key = $companyPaymentSettings['iyzipay_secret_key'];
        $settings  = DB::table('settings')->where('created_by', '=',$invoice->created_by)->get()->pluck('value', 'name');
        $get_amount = $request->amount;
        $currency= Utility::getValByName('site_currency');
        if ($invoice)
        {
            if ($get_amount > $invoice->getDue())
            {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else
            {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            }
            $res_data['total_price'] = $get_amount;
            try {
                $setBaseUrl = ($iyzipay_mode == 'sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
                $options = new \Iyzipay\Options();
                $options->setApiKey($iyzipay_public_key);
                $options->setSecretKey($iyzipay_secret_key);
                $options->setBaseUrl($setBaseUrl); // or "https://api.iyzipay.com" for production
                $ipAddress = Http::get('https://ipinfo.io/?callback=')->json();
                $address = ($authuser->address) ? $authuser->address : 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1';
                // create a new payment request
                $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
                $request->setLocale('en');
                $request->setPrice($res_data['total_price']);
                $request->setPaidPrice($res_data['total_price']);
                $request->setCurrency($currency);
                $request->setCallbackUrl(route('iyzipay.invoicepayment.callback',[$invoice->id,$get_amount]));
                $request->setEnabledInstallments(array(1));
                $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                $buyer = new \Iyzipay\Model\Buyer();
                $buyer->setId($authuser->id);
                $buyer->setName(explode(' ', $authuser->name)[0]);
                $buyer->setSurname(explode(' ', $authuser->name)[0]);
                $buyer->setGsmNumber("+" . $authuser->dial_code . $authuser->phone);
                $buyer->setEmail($authuser->email);
                $buyer->setIdentityNumber(rand(0, 999999));
                $buyer->setLastLoginDate("2023-03-05 12:43:35");
                $buyer->setRegistrationDate("2023-04-21 15:12:09");
                $buyer->setRegistrationAddress($address);
                $buyer->setIp($ipAddress['ip']);
                $buyer->setCity($ipAddress['city']);
                $buyer->setCountry($ipAddress['country']);
                $buyer->setZipCode($ipAddress['postal']);
                $request->setBuyer($buyer);
                $shippingAddress = new \Iyzipay\Model\Address();
                $shippingAddress->setContactName($authuser->name);
                $shippingAddress->setCity($ipAddress['city']);
                $shippingAddress->setCountry($ipAddress['country']);
                $shippingAddress->setAddress($address);
                $shippingAddress->setZipCode($ipAddress['postal']);
                $request->setShippingAddress($shippingAddress);
                $billingAddress = new \Iyzipay\Model\Address();
                $billingAddress->setContactName($authuser->name);
                $billingAddress->setCity($ipAddress['city']);
                $billingAddress->setCountry($ipAddress['country']);
                $billingAddress->setAddress($address);
                $billingAddress->setZipCode($ipAddress['postal']);
                $request->setBillingAddress($billingAddress);
                $basketItems = array();
                $firstBasketItem = new \Iyzipay\Model\BasketItem();
                $firstBasketItem->setId("BI101");
                $firstBasketItem->setName("Binocular");
                $firstBasketItem->setCategory1("Collectibles");
                $firstBasketItem->setCategory2("Accessories");
                $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $firstBasketItem->setPrice($res_data['total_price']);
                $basketItems[0] = $firstBasketItem;
                $request->setBasketItems($basketItems);

                $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, $options);
                return redirect()->to($checkoutFormInitialize->getpaymentPageUrl());
            } catch (\Exception $e) {
                return redirect()->route('invoices.show')->with('errors', $e->getMessage());
            }
        }
        else{
            return redirect()
                ->route('invoices.show', \Crypt::encrypt($invoice->id))
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }

        return redirect()->back()->with('error', __('Unknown error occurred'));
    }



    public function getInvoiceiyzipayCallback(Request $request, $invoice_id, $amount)
    {
        $invoice = Invoice::find($invoice_id);
        // $this->invoiceData = $invoice;
        if(\Auth::check())
        {
            $user = Auth::user();
        }
        else
        {
           $user=User::where('id',$invoice->created_by)->first();

        }
        $settings  = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        if($invoice){
            try {
                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->invoice_id     = $invoice->id;
                $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber( $user);
                $invoice_payment->client_id      = $user->id;
                $invoice_payment->amount         = $amount;
                $invoice_payment->date           = date('Y-m-d');
                $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = "";
                $invoice_payment->payment_type   = 'Iyzipay';
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
        }
        else {
            if(Auth::check()){
                return redirect()->route('invoices.show', $invoice->id)->with('error',__('Invoice not found.'));
            }else{
                return redirect()->route('pay.invoice', encrypt($invoice_id))->with('success', __('Invoice not found.'));

            }
        }
    }



}
