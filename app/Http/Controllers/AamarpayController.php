<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Utility;
use App\Models\InvoicePayment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class AamarpayController extends Controller
{




    function redirect_to_merchant($url)
    {

        $token = csrf_token();
?>
        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>
            <script type="text/javascript">
                function closethisasap() {
                    document.forms["redirectpost"].submit();
                }
            </script>
        </head>

        <body onLoad="closethisasap();">

            <form name="redirectpost" method="post" action="<?php echo 'https://sandbox.aamarpay.com/' . $url; ?>">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </body>

        </html>
<?php
        exit;
    }




    public function invoicepaywithaamarpay(Request $request)
    {
        $invoice = Invoice::find($request->invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $url = 'https://sandbox.aamarpay.com/request.php';
        $paymentSetting = Utility::non_auth_payment_settings($user->id);
        $aamarpay_store_id = $paymentSetting['aamarpay_store_id'];
        $aamarpay_signature_key = $paymentSetting['aamarpay_signature_key'];
        $aamarpay_description = $paymentSetting['aamarpay_description'];
        $currency = !empty($paymentSetting['currency']) ?  $paymentSetting['currency'] : 'USD';
        $get_amount = $request->amount;
        if ($invoice && $get_amount != 0) {

            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            try {

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $fields = array(
                    'store_id' => $aamarpay_store_id,
                    //store id will be aamarpay,  contact integration@aamarpay.com for test/live id
                    'amount' => $get_amount,
                    //transaction amount
                    'payment_type' => '',
                    //no need to change
                    'currency' => $currency,
                    //currenct will be USD/BDT
                    'tran_id' => $orderID,
                    //transaction id must be unique from your end
                    'cus_name' => $user->name,
                    //customer name
                    'cus_email' => $user->email,
                    //customer email address
                    'cus_add1' => '',
                    //customer address
                    'cus_add2' => '',
                    //customer address
                    'cus_city' => '',
                    //customer city
                    'cus_state' => '',
                    //state
                    'cus_postcode' => '',
                    //postcode or zipcode
                    'cus_country' => '',
                    //country
                    'cus_phone' => '1234567890',
                    //customer phone number
                    'success_url' => route('customer.aamarpay', Crypt::encrypt(['response' => 'success', 'invoice_id' => $invoice->id, 'price' => $get_amount, 'order_id' => $orderID])),
                    //your success route
                    'fail_url' => route('customer.aamarpay', Crypt::encrypt(['response' => 'failure', 'invoice_id' => $invoice->id, 'price' => $get_amount, 'order_id' => $orderID])),
                    //your fail route
                    'cancel_url' => route('customer.aamarpay', Crypt::encrypt(['response' => 'cancel'])),
                    //your cancel url
                    'signature_key' => $aamarpay_signature_key,
                    'desc' => $aamarpay_description,
                ); //signature key will provided aamarpay, contact integration@aamarpay.com for test/live signature key
                $fields_string = http_build_query($fields);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_URL, $url);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $url_forward = str_replace('"', '', stripslashes(curl_exec($ch)));
                curl_close($ch);
                $this->redirect_to_merchant($url_forward);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e);
            }
        }
    }

    public function getInvoicePaymentStatus( $data)
    {
            try {

                $data = \Crypt::decrypt($data);
                $getAmount = $data['price'];
                $invoice    = Invoice::find($data['invoice_id']);

                // $user = User::where('id', $invoice->created_by)->first();
                if(Auth::check())
                {
                    $user = Auth::user();
                }
                else
                {
                    $user=User::where('id',$invoice->created_by)->first();
                }

                if ($data['response'] == "success")
                {
                    $invoice_payment                 = new InvoicePayment();
                    $invoice_payment->invoice_id     = $data['invoice_id'];
                    $invoice_payment->transaction_id = app('App\Http\Controllers\InvoiceController')->transactionNumber( $user);
                    $invoice_payment->client_id      = $user->id;
                    $invoice_payment->amount         = $getAmount;
                    $invoice_payment->date           = date('Y-m-d');
                    $invoice_payment->payment_id     = 0;
                    $invoice_payment->notes          = "";
                    $invoice_payment->payment_type   = 'Aamarpay';
                    $invoice_payment->save();
                    if ($invoice->getDue() == 0) {
                        $invoice->status = 3;
                        $invoice->save();
                    } else {
                        $invoice->status = 2;
                        $invoice->save();
                    }
                //Notification
                $setting  = Utility::settings($invoice->created_by);
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
                if(Auth::user())
                {
                    return redirect()->route('invoices.show', $data['invoice_id'])->with('success', __('Invoice paid Successfully!'). ((isset($msg) ? '<br> <span class="text-danger">' . $msg . '</span>' : '')));
                }
                else
                {
                    return redirect()->route('pay.invoice', encrypt($data['invoice_id']))->with('success', __('Invoice paid Successfully!'). ((isset($msg) ? '<br> <span class="text-danger">' . $msg . '</span>' : '')));
                }

            }
            elseif($data['response'] == "cancel")
            {
                return redirect()->back()->with('error', __('Your payment is cancel'));
            }
            else{
                return redirect()->back()->with('error', __('Your Transaction is fail please try again'));
            }
        }
        catch (\Throwable $th)
        {
            return redirect()->back()->with('error',$th);
        }

    }
}
