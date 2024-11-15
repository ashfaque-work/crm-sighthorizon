<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceBankTransfer;
use Illuminate\Support\Facades\Validator;

class BankTransferController extends Controller
{
    public function banktransferStatus(Request $request,$plan_id)
    {
        $user = Auth::user();
        $plan = Plan::find($plan_id);
        $frequency = $request->banktransfer_payment_frequency;
        $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

        $check = Order::where('plan_id' , $plan->id)->where('payment_status' , 'pending')->where('user_id', \Auth::user()->id)->first();

        if(!empty($check))
        {
            return redirect()->route('plans.index')->with('error', __('You already send Payment request to this plan.'));
        }

        if($user->type == 'Owner')
        {
            $validator = \Validator::make(
                $request->all(), [
                                'payment_receipt' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
             $validation =[
                // 'mimes:'.'png',
                'max:'.'20480',
            ];
            $favicon = time() . '_' . 'receipt_image.png';
            $dir = 'uploads/payment_receipt/';
            $path = Utility::upload_file($request,'payment_receipt',$favicon,$dir,$validation);
            if($path['flag'] == 1){
                $favicon = $path['url'];
            }else{
                return redirect()->back()->with('error', __($path['msg']));
            }
        }

        if ($plan) {
            $plan_amount = $plan->{$frequency. '_price'};
            // $status  = ucwords(str_replace('_', ' ', $result['state']));
            if (!empty($request->coupon) && $request->coupon != '') {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
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
        $payment_setting = Utility::payment_settings();

            Order::create(
                [
                    'order_id' => $order_id,
                    'name' => null,
                    'email' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan_amount==null?0:$plan_amount,
                    'price_currency' => !empty($payment_setting['currency']) ? $payment_setting['currency'] : 'USD',
                    "payment_frequency" => $frequency,
                    'txn_id' => '',
                    'payment_type' => 'Bank Transfer',
                    'payment_status' => 'Pending',
                    'receipt' => Utility::get_file($favicon),
                    'user_id' => $user->id,
                ]
            );


                if ($plan) {
                    return redirect()->route('plans.index')->with('success', __('Plan Approval Successfully Sent.'));
                } else {
                    return redirect()->route('plans.index')->with('error', __($plan['error']));
                }

        } else {
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function PaymentStatus($id)
    {
        $admin_payment_setting = Utility::payment_settings();
        $order = Order::find($id);
        $frequency=$order->payment_frequency;

        return view('order.payment_status',compact('order','admin_payment_setting','frequency'));
    }
    public function PaymentApproval(Request $request, $id,$frequency)
    {
        if($request->payment_approval == "1"){
        $frequency =$frequency;
        $order = Order::find($id);
        $user= User::find($order->user_id);
        $plan_id=$order->plan_id;
        $plan = Plan::find($plan_id);
        $order->update(
            [
                'payment_status' => 'Success',
            ]
        );
        $assignPlan = $user->assignPlan($plan->id, $frequency);
        if ($assignPlan['is_success']) {
            return redirect()->route('order.index')->with('success', __('Plan Successfully Approved.'));
        } else {
            return redirect()->route('order.index')->with('error', __($assignPlan['error']));
        }
    }else{
        $order = Order::find($id);
        $order->update(
            [
                'payment_status' => 'Rejected',
            ]
        );
        return redirect()->route('order.index')->with('error', __('Plan Successfully Rejected.'));


    }


    }
    public function invoicePayWithBanktransfer(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
        if (\Auth::check()) {
            $user     = \Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
        }
        $check = InvoiceBankTransfer::where('status' , 'Pending')->where('invoice_id',$invoice->invoice_id)->where('created_by',$user->id)->first();
            if(!empty($check))
            {
                if(\Auth::check())
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('error', __('You already send Payment request to this Invoice.'));
                        }
                        else
                        {
                             return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('You already send Payment request to this Invoice.'));
                        }
            }
        $get_amount = $request->amount;
            if ($get_amount > $invoice->getdue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            $validator = \Validator::make(
                $request->all(), [
                                'payment_receipt' => 'required',
                                'amount' => 'required',
                                'invoice_id' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
             $validation =[
                // 'mimes:'.'png',
                'max:'.'20480',
            ];
            $favicon = time() . '_' . 'invoice_receipt_image.png';
            $dir = 'uploads/invoice_payment_receipt/';
            $path = Utility::upload_file($request,'payment_receipt',$favicon,$dir,$validation);
            if($path['flag'] == 1){
                $favicon = $path['url'];
            }else{
                return redirect()->back()->with('error', __($path['msg']));
            }


        if($invoice)
        {
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

                $invoice_banktransfer                 = new InvoiceBankTransfer();
                $invoice_banktransfer->invoice_id     = $invoice->invoice_id;
                $invoice_banktransfer->order_id       = $order_id;
                $invoice_banktransfer->amount         = isset($get_amount) ? $get_amount : 0;
                $invoice_banktransfer->status         = __('Pending');
                $invoice_banktransfer->receipt        = Utility::get_file($favicon);
                $invoice_banktransfer->date           = date('Y-m-d');
                $invoice_banktransfer->created_by     = $user->id;
                $invoice_banktransfer->save();

                if(\Auth::check())
                        {
                            return redirect()->route('invoices.show', $invoice_id)->with('success', __('Invoice Approval Successfully Sent'));
                        }
                        else
                        {
                             return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('success', __('Invoice Approval Successfully Sent'));
                        }
            } else
            {
                if(\Auth::check())
                {
                     return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice Approval not Sent!'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Crypt::encrypt($invoice_id))->with('error', __('Invoice Approval not Sent!'));
                }
            }

    }
    public function invoicepaymentdestroy($id)
    {
        if(\Auth::user()->type == 'Owner')
        {
         $payment = InvoicePayment::find($id);
         $payment->delete();
         return redirect()->back()->with('success', 'Payment Deleted Successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function banktransferdestroy($id)
    {
        if(\Auth::user()->type == 'Owner')
        {
         $payment = InvoiceBankTransfer::find($id);
         $payment->delete();
         return redirect()->back()->with('success', 'Payment Deleted Successfully.');
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
    public function InvoicePaymentStatus(Request $request, $id)
    {
        $payment  = Utility::set_payment_settings();
        $invoice_banktransfer = InvoiceBankTransfer::where('id',$id)->first();
        $user= User::find($invoice_banktransfer->created_by);
        return view('invoices.payment_status',compact('payment','invoice_banktransfer','user'));
    }
    public function InvoicePaymentApproval(Request $request, $id)
    {
        $invoice_banktransfer = InvoiceBankTransfer::where('id',$id)->first();
        $invoice = invoice::where('invoice_id',$invoice_banktransfer->invoice_id)->first();
        $amount=$invoice_banktransfer->amount;
        $payment = InvoicePayment::where('invoice_id',$invoice->invoice_id)->first();
        $user= User::find($invoice_banktransfer->created_by);
        if($request->payment_approval == "1"){
        $invoice_banktransfer->update(
            [
                'status' => 'Approve',
            ]
        );
    if($invoice){
        $invoice_payment                 = new InvoicePayment();
        $invoice_payment->invoice_id     = $invoice->id;
        $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber( $user);
        $invoice_payment->client_id      = $user->id;
        $invoice_payment->amount         = $amount;
        $invoice_payment->date           = date('Y-m-d');
        $invoice_payment->payment_id     = 0;
        $invoice_payment->notes          = "";
        $invoice_payment->payment_type   = 'Bank Transfer';
        $invoice_payment->save();
        if ($invoice->getDue() == 0) {
            $invoice->status = 3;
            $invoice->save();
        } else {
            $invoice->status = 2;
            $invoice->save();
        }
        return redirect()->route('invoices.show', $invoice->id)->with('success', __('Payment Approved Successfully'));


    }
}else{
        $invoice_banktransfer->update(
            [
                'status' => 'Rejected',
            ]
        );
        return redirect()->route('invoices.show', $invoice->id)->with('error', __('Payment Rejected successfully !'));
}
    }


}
