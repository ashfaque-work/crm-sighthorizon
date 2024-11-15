<?php

namespace App\Http\Controllers;

use App\Mail\EmailTest;
use App\Models\Settings;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Artisan;
use App\Models\EmailTemplate;
use File;
use Illuminate\Support\Facades\Cache;
use App\Models\Webhook;
use Illuminate\Support\Facades\Validator;



class SettingsController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        $settings = Utility::settings();
        $webhook = Webhook::where('created_by', '=', $user->ownerId())->get();
        $file_size = 0;
        foreach (\File::allFiles(storage_path('/framework')) as $file) {
            $file_size += $file->getSize();
        }
        $file_size = number_format($file_size / 1000000, 6);
        if ($user->can('System Settings')) {
            $settings = Utility::settings();
            $payment = Utility::payment_settings();
            $EmailTemplates = EmailTemplate::all();
            return view('users.system_settings', compact('settings', 'payment', 'EmailTemplates', 'file_size', 'webhook'));
        } else {
            return redirect()->back()->with('error', __('Invalid User.'));
        }
    }

    public function store(Request $request)
    {

        if (\Auth::user()->can('Manage Company Settings')) {
            $post = $request->all();
            unset($post['_token']);

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->ownerId(),
                    ]
                );
            }



            return redirect()->back()->with('success', __('Setting updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Invalid User.'));
        }
    }

    public function saveBusinessSettings(Request $request)
    {
        $user = \Auth::user();
        if ($request->dark_logo) {
            $request->validate(
                [
                    'dark_logo' => 'image',
                ]
            );

            $logoName     = $user->id . '-logo-dark.png';
            $dir = 'logo/';

            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'dark_logo', $logoName, $dir, $validation);
            if ($path['flag'] == 1) {
                $dark_logo = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $logoName,
                    'dark_logo',
                    \Auth::user()->ownerId(),
                ]
            );
        }

        if ($request->light_logo) {
            $request->validate(
                [
                    'light_logo' => 'image',
                ]
            );
            // $lightlogo = 'light_logo' . '_' . time() . '.' .'png';
            // $request->file('light_logo')->storeAs('logo', $lightlogo);

            $logoName     = $user->id . '-logo-light.png';
            $dir = 'logo/';

            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'light_logo', $logoName, $dir, $validation);

            if ($path['flag'] == 1) {
                $light_logo = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $logoName,
                    'light_logo',
                    \Auth::user()->ownerId(),
                ]
            );
        }

        if ($request->company_favicon) {
            $request->validate(
                [
                    'company_favicon' => 'image',
                ]
            );
            $favicon = $user->id . '_favicon.png';

            $dir = 'logo/';

            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'company_favicon', $favicon, $dir, $validation);
            if ($path['flag'] == 1) {
                $company_favicon = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $favicon,
                    'company_favicon',
                    \Auth::user()->ownerId(),
                ]
            );
        }


        if (!empty($request->header_text) || !empty($request->SITE_RTL) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout) || !empty($request->enable_landing)) {

            $post = $request->all();
            $post['SITE_RTL'] = (!empty($request->SITE_RTL)) ? 'on' : 'off';
            $post['enable_landing'] = (!empty($request->enable_landing)) ? 'yes' : 'no';
            $post['color'] = (!isset($request->color) && empty($request->color)) ? 'theme-4' : $request->color;
            // $post['color'] =  $request->has('color') ? $request-> color : 'theme-3';
            $post['cust_theme_bg'] = (!empty($request->cust_theme_bg)) ? 'on' : 'off';
            $post['cust_darklayout'] = (!empty($request->cust_darklayout)) ? 'on' : 'off';



            unset($post['_token'], $post['dark_logo'], $post['light_logo'], $post['company_favicon']);

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->ownerId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Setting successfully updated.');
    }

    public function testEmail(Request $request)
    {

        $user = \Auth::user();

        $data                      = [];
        $data['mail_driver']       = $request->mail_driver;
        $data['mail_host']         = $request->mail_host;
        $data['mail_port']         = $request->mail_port;
        $data['mail_username']     = $request->mail_username;
        $data['mail_password']     = $request->mail_password;
        $data['mail_encryption']   = $request->mail_encryption;
        $data['mail_from_address'] = $request->mail_from_address;
        $data['mail_from_name']    = $request->mail_from_name;
        return view('users.test_email', compact('data'));
    }

    public function testEmailSend(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'mail_driver' => 'required',
                'mail_host' => 'required',
                'mail_port' => 'required',
                'mail_username' => 'required',
                'mail_encryption'=> 'required',
                'mail_password' => 'required',
                'mail_from_address' => 'required',
                'mail_from_name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return response()->json(
                [
                    'is_success' => false,
                    'message' => $messages->first(),
                ]
            );
        }

        try {
            config(
                [
                    'mail.driver' => $request->mail_driver,
                    'mail.host' => $request->mail_host,
                    'mail.port' => $request->mail_port,
                    'mail.encryption' => $request->mail_encryption,
                    'mail.username' => $request->mail_username,
                    'mail.password' => $request->mail_password,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]
            );
            Mail::to($request->email)->send(new EmailTest());
        } catch (\Exception $e) {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }

        return response()->json(
            [
                'is_success' => true,
                'message' => __('Email send Successfully'),
            ]
        );
    }
    // public function testEmailSend(Request $request)
    // {
    //     $validator = \Validator::make($request->all(), ['email' => 'required|email']);
    //     if($validator->fails())
    //     {
    //         $messages = $validator->getMessageBag();

    //         return redirect()->back()->with('error', $messages->first());
    //     }

    //     try
    //     {
    //         Mail::to($request->email)->send(new EmailTest());
    //     }
    //     catch(\Exception $e)
    //     {
    //         return redirect()->back()->with('error', $e->getMessage());
    //     }

    //     return redirect()->back()->with('success', __('Email send Successfully'));
    // }

    public function savePaymentSettings(Request $request)
    {
        $validatorArray = [];

        $created_at = date('Y-m-d H:i:s');
        $updated_at = date('Y-m-d H:i:s');

        $post = $request->all();
        unset($post['_token']);

        $stripe_status = $request->site_enable_stripe ?? 'off';
        $paypal_status = $request->site_enable_paypal ?? 'off';

        if ($stripe_status == 'on') {
            $validatorArray['site_stripe_key']    = 'required|string|max:255';
            $validatorArray['site_stripe_secret'] = 'required|string|max:255';
        }
        if ($paypal_status == 'on') {
            $validatorArray['site_paypal_client_id']  = 'required|string|max:255';
            $validatorArray['site_paypal_secret_key'] = 'required|string|max:255';
        }

        $validator = \Validator::make(
            $request->all(),
            $validatorArray
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $post['site_enable_stripe'] = $stripe_status;
        $post['site_enable_paypal'] = $paypal_status;

        foreach ($post as $key => $data) {
            \DB::insert(
                'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->ownerId(),
                    $created_at,
                    $updated_at,
                ]
            );
        }

        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }

    public function saveTemplateSettings(Request $request)
    {
        $user = \Auth::user();
        $post = $request->all();
        unset($post['_token']);

        if (isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color']))) {
            $post['invoice_color'] = "ffffff";
        }

        if (isset($post['estimation_template']) && (!isset($post['estimation_color']) || empty($post['estimation_color']))) {
            $post['estimation_color'] = "ffffff";
        }

        if (isset($post['mdf_template']) && (!isset($post['mdf_color']) || empty($post['mdf_color']))) {
            $post['mdf_color'] = "ffffff";
        }

        if ($request->invoice_logo) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'invoice_logo' => 'image|mimes:png|max:2048',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice_logo         = $user->id . '_invoice_logo.png';
            $path                 = $request->file('invoice_logo')->storeAs('invoice_logo', $invoice_logo);
            $post['invoice_logo'] = $invoice_logo;
        }

        if ($request->estimation_logo) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'estimation_logo' => 'image|mimes:png|max:2048',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $estimation_logo         = $user->id . '_estimation_logo.png';
            $path                    = $request->file('estimation_logo')->storeAs('estimation_logo', $estimation_logo);
            $post['estimation_logo'] = $estimation_logo;
        }

        if ($request->mdf_logo) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'mdf_logo' => 'image|mimes:png|max:2048',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $mdf_logo         = $user->id . '_mdf_logo.png';
            $path             = $request->file('mdf_logo')->storeAs('mdf_logo', $mdf_logo);
            $post['mdf_logo'] = $mdf_logo;
        }

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->ownerId(),
                ]
            );
        }

        if (isset($post['invoice_template'])) {
            return redirect()->back()->with('success', __('Invoice Setting updated successfully'));
        }

        if (isset($post['estimation_template'])) {
            return redirect()->back()->with('success', __('Estimation Setting updated successfully'));
        }

        if (isset($post['mdf_template'])) {
            return redirect()->back()->with('success', __('MDF Setting updated successfully'));
        }
    }

    public function adminPaymentSettings(Request $request)
    {
        $user = \Auth::user();


            $post['currency_symbol'] = $request->currency_symbol;
            $post['currency'] = $request->currency;
        if (isset($request->is_banktransfer_enabled) && $request->is_banktransfer_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_details' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_banktransfer_enabled']     = $request->is_banktransfer_enabled;
            $post['bank_details']                = $request->bank_details;
        } else {
            $post['is_banktransfer_enabled'] = 'off';
        }

        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required|string',
                    'stripe_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_stripe_enabled']     = $request->is_stripe_enabled;
            $post['stripe_secret']         = $request->stripe_secret;
            $post['stripe_key']            = $request->stripe_key;
            // $post['stripe_webhook_secret'] = $request->stripe_webhook_secret;
        } else {
            $post['is_stripe_enabled'] = 'off';
        }


        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = 'off';
        }

        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = 'off';
        }

        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = 'off';
        }

        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = 'off';
        }

        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = 'off';
        }

        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = 'off';
        }

        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {



            $validator = \Validator::make(
                $request->all(),
                [
                    'skrill_email' => 'required|email',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = 'off';
        }

        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = 'off';
        }

        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }
        // Toyyibpay
        if (isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'toyyibpay_secret_key' => 'required|string',
                    'category_code' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_toyyibpay_enabled'] = $request->is_paymentwall_enabled;
            $post['toyyibpay_secret_key'] = $request->toyyibpay_secret_key;
            $post['category_code'] = $request->category_code;
        } else {
            $post['is_toyyibpay_enabled'] = 'off';
        }

        if (isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'payfast_mode' => 'required',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_payfast_enabled']    = $request->is_payfast_enabled;
            $post['payfast_mode']          = $request->payfast_mode;
            $post['payfast_merchant_id']   = $request->payfast_merchant_id;
            $post['payfast_merchant_key']  = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
        } else {
            $post['is_payfast_enabled'] = 'off';
        }
        if (isset($request->is_iyzipay_enabled) && $request->is_iyzipay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'iyzipay_mode' => 'required',
                    'iyzipay_public_key' => 'required|string',
                    'iyzipay_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_iyzipay_enabled']    = $request->is_iyzipay_enabled;
            $post['iyzipay_mode']          = $request->iyzipay_mode;
            $post['iyzipay_public_key']   = $request->iyzipay_public_key;
            $post['iyzipay_secret_key']  = $request->iyzipay_secret_key;
        } else {
            $post['is_iyzipay_enabled'] = 'off';
        }
        if (isset($request->is_sspay_enabled) && $request->is_sspay_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'sspay_secret_key' => 'required|string',
                    'iyzipay_secret_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_sspay_enabled']    = $request->is_sspay_enabled;
            $post['sspay_secret_key']   = $request->sspay_secret_key;
            $post['sspay_category_code']  = $request->sspay_category_code;
        } else {
            $post['is_sspay_enabled'] = 'off';
        }

        if (isset($request->is_paytab_enabled) && $request->is_paytab_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paytab_profile_id' => 'required|string',
                    'paytab_server_key' => 'required|string',
                    'paytab_region' => 'required|string',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytab_enabled'] = $request->is_paytab_enabled;
            $post['paytab_profile_id'] = $request->paytab_profile_id;
            $post['paytab_server_key'] = $request->paytab_server_key;
            $post['paytab_region'] = $request->paytab_region;
        } else {
            $post['is_paytab_enabled'] = 'off';
        }

        if (isset($request->is_benefit_enabled) && $request->is_benefit_enabled == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'benefit_api_key' => 'required|string',
                    'benefit_secret_key' => 'required|string',

                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_benefit_enabled'] = $request->is_benefit_enabled;
            $post['benefit_api_key'] = $request->benefit_api_key;
            $post['benefit_secret_key'] = $request->benefit_secret_key;
        } else {
            $post['is_benefit_enabled'] = 'off';
        }

        if (isset($request->is_cashefree_enabled) && $request->is_cashefree_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'cashfree_key' => 'required|string',
                    'cashfree_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_cashefree_enabled'] = $request->is_cashefree_enabled;
            $post['cashfree_key']       = $request->cashfree_key;
            $post['cashfree_secret'] = $request->cashfree_secret;
        } else {
            $post['is_cashefree_enabled'] = 'off';
        }


        if (isset($request->is_aamarpay_enabled) && $request->is_aamarpay_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'aamarpay_store_id' => 'required|string',
                    'aamarpay_signature_key' => 'required|string',
                    'aamarpay_description' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_aamarpay_enabled'] = $request->is_aamarpay_enabled;
            $post['aamarpay_store_id']       = $request->aamarpay_store_id;
            $post['aamarpay_signature_key'] = $request->aamarpay_signature_key;
            $post['aamarpay_description'] = $request->aamarpay_description;
        } else {
            $post['is_aamarpay_enabled'] = 'off';
        }

        if (isset($request->is_paytr_enabled) && $request->is_paytr_enabled == 'on') {


            $validator = \Validator::make(
                $request->all(),
                [
                    'paytr_merchant_id' => 'required|string',
                    'paytr_merchant_key' => 'required|string',
                    'paytr_merchant_salt' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paytr_enabled'] = $request->is_paytr_enabled;
            $post['paytr_merchant_id'] = $request->paytr_merchant_id;
            $post['paytr_merchant_key'] = $request->paytr_merchant_key;
            $post['paytr_merchant_salt'] = $request->paytr_merchant_salt;

        } else {
            $post['is_paytr_enabled'] = 'off';
        }
        if (isset($request->is_yookassa_enabled) && $request->is_yookassa_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'yookassa_shop_id' => 'required|string',
                    'yookassa_secret' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_yookassa_enabled'] = $request->is_yookassa_enabled;
            $post['yookassa_shop_id'] = $request->yookassa_shop_id;
            $post['yookassa_secret'] = $request->yookassa_secret;

        } else {
            $post['is_yookassa_enabled'] = 'off';
        }
        if(isset($request->is_midtrans_enabled) && $request->is_midtrans_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'is_midtrans_enabled' => 'required',
                    'midtrans_secret' => 'required',
                    'midtrans_mode'=>'required',

                    ]
                );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_midtrans_enabled'] = $request->is_midtrans_enabled;

            $post['midtrans_secret'] = $request->midtrans_secret;
            $post['midtrans_mode'] = $request->midtrans_mode;
        } else {
            $post['is_midtrans_enabled'] = 'off';
        }
        if(isset($request->is_xendit_enabled) && $request->is_xendit_enabled == 'on'){
            $validator = Validator::make(
                $request->all(),
                [
                    'is_xendit_enabled' => 'required',
                    'xendit_api' => 'required',
                    'xendit_token' => 'required',

                    ]
                );
                // 'midtrans_mode' => 'required',

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_xendit_enabled'] = $request->is_xendit_enabled;
            // $post['midtrans_mode'] = $request->midtrans_mode;
            $post['xendit_token'] = $request->xendit_token;
            $post['xendit_api'] = $request->xendit_api;
        } else {
            $post['is_xendit_enabled'] = 'off';
        }

        foreach ($post as $key => $data) {
            $arr = [
                $data,
                $key,
                $user->ownerId(),
            ];

            $insert_payment_setting = \DB::insert(
                'insert into admin_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }

        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }

    public function saveZoomSettings(Request $request)
    {

        $post = $request->all();
        unset($post['_token']);
        $created_by = \Auth::user()->ownerId();
        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    $created_by,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ]
            );
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }

    public function slack(Request $request)
    {

        $post = [];
        $post['slack_webhook'] = $request->input('slack_webhook');
        $post['lead_notification'] = $request->has('lead_notification') ? $request->input('lead_notification') : 0;
        $post['deal_notification'] = $request->has('deal_notification') ? $request->input('deal_notification') : 0;
        $post['leadtodeal_notification'] = $request->has('leadtodeal_notification') ? $request->input('leadtodeal_notification') : 0;
        $post['estimate_notification'] = $request->has('estimate_notification') ? $request->input('estimate_notification') : 0;
        $post['invoice_notification'] = $request->has('invoice_notification') ? $request->input('invoice_notification') : 0;
        $post['payment_notification'] = $request->has('payment_notification') ? $request->input('payment_notification') : 0;
        $post['contract_notification'] = $request->has('contract_notification') ? $request->input('contract_notification') : 0;
        $post['invoice_status_update_notification'] = $request->has('invoice_status_update_notification') ? $request->input('invoice_status_update_notification') : 0;

        $created_by = \Auth::user()->ownerId();
        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        $created_by,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }

    public function telegram(Request $request)
    {

        $post = [];
        $post['telegrambot'] = $request->input('telegrambot');
        $post['telegramchatid'] = $request->input('telegramchatid');
        $post['telegram_lead_notification'] = $request->has('telegram_lead_notification') ? $request->input('telegram_lead_notification') : 0;
        $post['telegram_deal_notification'] = $request->has('telegram_deal_notification') ? $request->input('telegram_deal_notification') : 0;
        $post['telegram_leadtodeal_notification'] = $request->has('telegram_leadtodeal_notification') ? $request->input('telegram_leadtodeal_notification') : 0;
        $post['telegram_estimate_notification'] = $request->has('telegram_estimate_notification') ? $request->input('telegram_estimate_notification') : 0;
        $post['telegram_invoice_notification'] = $request->has('telegram_invoice_notification') ? $request->input('telegram_invoice_notification') : 0;
        $post['telegram_payment_notification'] = $request->has('telegram_payment_notification') ? $request->input('telegram_payment_notification') : 0;
        $post['telegram_contract_notification'] = $request->has('telegram_contract_notification') ? $request->input('telegram_contract_notification') : 0;
        $post['telegram_invoice_status_update_notification'] = $request->has('telegram_invoice_status_update_notification') ? $request->input('telegram_invoice_status_update_notification') : 0;

        $created_by = \Auth::user()->ownerId();
        if (isset($post) && !empty($post) && count($post) > 0) {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        $created_by,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Setting added successfully saved.'));
    }

    // public function recaptchaSettingStore(Request $request)
    // {
    //     //return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));
    //     $user = \Auth::user();
    //     $rules = [];
    //     if ($request->recaptcha_module == 'on') {
    //         $rules['google_recaptcha_key'] = 'required|string|max:50';
    //         $rules['google_recaptcha_secret'] = 'required|string|max:50';
    //     }
    //     $validator = \Validator::make(
    //         $request->all(),
    //         $rules
    //     );
    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();
    //         return redirect()->back()->with('error', $messages->first());
    //     }
    //     $arrEnv = [
    //         'RECAPTCHA_MODULE' => $request->recaptcha_module ?? 'off',
    //         'NOCAPTCHA_SITEKEY' => $request->google_recaptcha_key,
    //         'NOCAPTCHA_SECRET' => $request->google_recaptcha_secret,
    //     ];
    //     if (Utility::setEnvironmentValue($arrEnv)) {
    //         return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
    //     } else {
    //         return redirect()->back()->with('error', __('Something is wrong'));
    //     }
    // }

    public function recaptchaSettingStore(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'Owner') {

        $rules = [];
        if ($request->recaptcha_module == 'on') {
            $rules['google_recaptcha_key'] = 'required|string|max:50';
            $rules['google_recaptcha_secret'] = 'required|string|max:50';
        }
        $validator = \Validator::make(
            $request->all(),
            $rules
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }


        $post = [];
        $recaptcha_module = (!empty($request->recaptcha_module)) ? 'yes' : 'no';
        $post['recaptcha_module'] = $recaptcha_module;
        $google_recaptcha_key= $request->has('google_recaptcha_key') ? $request-> google_recaptcha_key : '';
        $post['google_recaptcha_key'] = $google_recaptcha_key;
        $google_recaptcha_secret = $request->has('google_recaptcha_secret') ? $request-> google_recaptcha_secret : '';
        $post['google_recaptcha_secret'] = $google_recaptcha_secret;

        if(isset($post) && !empty($post) && count($post) > 0)
        {
            $created_at = $updated_at = date('Y-m-d H:i:s');
            foreach($post as $key => $data)
            {
                \DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ', [$data, $key, $user->id, $created_at, $updated_at, ]
                );
            }
        }
        return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
    }
    else
    {
        return redirect()->back()->with('error', __('Something is wrong'));
    }
    }

    public function emailSettingStore(Request $request)
    {
        $user = \Auth::user();

            $rules = [
                'mail_driver' => 'required|string|max:50',
                'mail_host' => 'required|string|max:50',
                'mail_port' => 'required|string|max:50',
                'mail_username' => 'required|string|max:50',
                'mail_password' => 'required|string|max:255',
                'mail_encryption' => 'required|string|max:50',
                'mail_from_address' => 'required|string|max:50',
                'mail_from_name' => 'required|string|max:50',
            ];
            $validator = \Validator::make(
                $request->all(),
                $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $post = $request->all();
            unset($post['_token']);

            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->ownerId(),
                    ]
                );
            }
            return redirect()->back()->with('success', __('Email Settings updated successfully'));


    }


    public function pusherSettingStore(Request $request)
    {

        $user = \Auth::user();
        if ($user->type == 'Owner') {
            $rules = [];

            if ($request->enable_chat == 'yes') {
                $rules['pusher_app_id']      = 'required|string|max:50';
                $rules['pusher_app_key']     = 'required|string|max:50';
                $rules['pusher_app_secret']  = 'required|string|max:50';
                $rules['pusher_app_cluster'] = 'required|string|max:50';
            }

            $request->validate($rules);

            $post = [];
            $enable_chat = (!empty($request->enable_chat)) ? 'yes' : 'no';
            $post['enable_chat'] = $enable_chat;
            $pusher_app_id = $request->has('pusher_app_id') ? $request-> pusher_app_id : '';
            $post['pusher_app_id'] = $pusher_app_id;
            $pusher_app_key = $request->has('pusher_app_key') ? $request-> pusher_app_key : '';
            $post['pusher_app_key'] = $pusher_app_key;
            $pusher_app_secret = $request->has('pusher_app_secret') ? $request-> pusher_app_secret : '';
            $post['pusher_app_secret'] = $pusher_app_secret;
            $pusher_app_cluster = $request->has('pusher_app_cluster') ? $request-> pusher_app_cluster : '';
            $post['pusher_app_cluster'] = $pusher_app_cluster;
            if(isset($post) && !empty($post) && count($post) > 0)
            {
                $created_at = $updated_at = date('Y-m-d H:i:s');
                foreach($post as $key => $data)
                {
                    \DB::insert(
                        'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ', [$data, $key,  $user->id, $created_at, $updated_at, ]
                    );
                }
            }
            return redirect()->back()->with('success', __('Pusher Settings updated successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
        Artisan::call('config:cache');
        Artisan::call('config:clear');
    }

    public function storageSettingStore(Request $request)
    {
        if (isset($request->storage_setting) && $request->storage_setting == 'local') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'local_storage_validation' => 'required',
                    'local_storage_max_upload_size' => 'required',
                ]
            );
            // $request->validate(
            //     [

            //         'local_storage_validation' => 'required',
            //         'local_storage_max_upload_size' => 'required',
            //         ]
            //     );

            $post['storage_setting'] = $request->storage_setting;
            $local_storage_validation = implode(',', $request->local_storage_validation);
            $post['local_storage_validation'] = $local_storage_validation;
            $post['local_storage_max_upload_size'] = $request->local_storage_max_upload_size;
        }


        if (isset($request->storage_setting) && $request->storage_setting == 's3') {
            $request->validate(
                [
                    's3_key'                  => 'required',
                    's3_secret'               => 'required',
                    's3_region'               => 'required',
                    's3_bucket'               => 'required',
                    's3_url'                  => 'required',
                    's3_endpoint'             => 'required',
                    's3_max_upload_size'      => 'required',
                    's3_storage_validation'   => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['s3_key']                     = $request->s3_key;
            $post['s3_secret']                  = $request->s3_secret;
            $post['s3_region']                  = $request->s3_region;
            $post['s3_bucket']                  = $request->s3_bucket;
            $post['s3_url']                     = $request->s3_url;
            $post['s3_endpoint']                = $request->s3_endpoint;
            $post['s3_max_upload_size']         = $request->s3_max_upload_size;
            $s3_storage_validation              = implode(',', $request->s3_storage_validation);
            $post['s3_storage_validation']      = $s3_storage_validation;
        }

        if (isset($request->storage_setting) && $request->storage_setting == 'wasabi') {
            $request->validate(
                [
                    'wasabi_key'                    => 'required',
                    'wasabi_secret'                 => 'required',
                    'wasabi_region'                 => 'required',
                    'wasabi_bucket'                 => 'required',
                    'wasabi_url'                    => 'required',
                    'wasabi_root'                   => 'required',
                    'wasabi_max_upload_size'        => 'required',
                    'wasabi_storage_validation'     => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['wasabi_key']                 = $request->wasabi_key;
            $post['wasabi_secret']              = $request->wasabi_secret;
            $post['wasabi_region']              = $request->wasabi_region;
            $post['wasabi_bucket']              = $request->wasabi_bucket;
            $post['wasabi_url']                 = $request->wasabi_url;
            $post['wasabi_root']                = $request->wasabi_root;
            $post['wasabi_max_upload_size']     = $request->wasabi_max_upload_size;
            $wasabi_storage_validation          = implode(',', $request->wasabi_storage_validation);
            $post['wasabi_storage_validation']  = $wasabi_storage_validation;
        }

        foreach ($post as $key => $data) {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                $arr
            );
        }

        return redirect()->back()->with('success', 'Storage setting successfully updated.');
    }
    public function saveGoogleCalenderSettings(Request $request)
    {

        if ($request->enable_google_calendar == 'on') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'google_clender_id' => 'required',
                    'google_calender_json_file' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
        }
        if ($request->google_calender_json_file) {
            $dir       = storage_path() . '/' . md5(time());
            if (!is_dir($dir)) {
                File::makeDirectory($dir, $mode = 0777, true, true);
            }
            $file_name = $request->google_calender_json_file->getClientOriginalName();
            $file_path =  md5(time()) . '/' . md5(time()) . "." . $request->google_calender_json_file->getClientOriginalExtension();
            $file = $request->file('google_calender_json_file');
            $file->move($dir, $file_path);
            $post['google_calender_json_file']            = $file_path;
        }

        if ($request->google_clender_id) {
            $post['google_clender_id']            = $request->google_clender_id;
            $post['enable_google_calendar']       = $request->enable_google_calendar ?? 'off';
            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->id,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Google Calendar successfully updated.');
    }

    public function saveSeoSettings(Request $request)
    {
        $user = \Auth::user();
        if ($request->meta_image) {
            $request->validate(['meta_image' => 'required|image']);

            $metaImage = 'meta_image.png';
            $dir = 'logo/';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'meta_image', $metaImage, $dir, $validation);

            if ($path['flag'] == 1) {
                $meta_image = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }
        }

        $meta_keywords = ((!isset($request->meta_keywords) && empty($request->meta_keywords)) ? '' : $request->meta_keywords);
        $meta_description = ((!isset($request->meta_description) && empty($request->meta_description)) ? '' : $request->meta_description);

        $arrData = [
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
        ];
        $post = $request->all();

        unset($post['_token']);
        foreach ($post as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    \Auth::user()->ownerId(),
                ]
            );
        }


        return redirect()->back()->with('success', __('Setting updated successfully'));
    }

    public function ClearCache(Request $request)
    {
        $user = \Auth::user();
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success', __('Cache Clear successfully'));
    }

    public function saveCookieSettings(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'cookie_title' => 'required',
                'cookie_description' => 'required',
                'strictly_cookie_title' => 'required',
                'strictly_cookie_description' => 'required',
                'more_information_description' => 'required',
                'contactus_url' => 'required',
            ]
        );

        $post = $request->all();
        unset($post['_token']);

        if ($request->enable_cookie) {
            $post['enable_cookie'] = 'on';
        } else {
            $post['enable_cookie'] = 'off';
        }
        if ($request->cookie_logging) {
            $post['cookie_logging'] = 'on';
        } else {
            $post['cookie_logging'] = 'off';
        }

        $post['cookie_title']                  = $request->cookie_title;
        $post['cookie_description']            = $request->cookie_description;
        $post['strictly_cookie_title']         = $request->strictly_cookie_title;
        $post['strictly_cookie_description']   = $request->strictly_cookie_description;
        $post['more_information_title']        = $request->more_information_title;
        $post['contactus_url']                 = $request->contactus_url;

        $settings = Utility::settings();
        foreach ($post as $key => $data) {

            if (in_array($key, array_keys($settings))) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        \Auth::user()->creatorId(),
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', 'Cookie setting successfully saved.');
    }

    public function CookieConsent(Request $request)
    {

        $settings = Utility::settings();

        if ($settings['enable_cookie'] == "on" && $settings['cookie_logging'] == "on") {
            $allowed_levels = ['necessary', 'analytics', 'targeting'];
            $levels = array_filter($request['cookie'], function ($level) use ($allowed_levels) {
                return in_array($level, $allowed_levels);
            });
            $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            // Generate new CSV line
            $browser_name = $whichbrowser->browser->name ?? null;
            $os_name = $whichbrowser->os->name ?? null;
            $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $device_type = Self::get_device_type($_SERVER['HTTP_USER_AGENT']);

            $ip = $_SERVER['REMOTE_ADDR'];
            // $ip = '49.36.83.154';
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));


            $date = (new \DateTime())->format('Y-m-d');
            $time = (new \DateTime())->format('H:i:s') . ' UTC';


            $new_line = implode(',', [
                $ip, $date, $time, json_encode($request['cookie']), $device_type, $browser_language, $browser_name, $os_name,
                isset($query) ? $query['country'] : '', isset($query) ? $query['region'] : '', isset($query) ? $query['regionName'] : '', isset($query) ? $query['city'] : '', isset($query) ? $query['zip'] : '', isset($query) ? $query['lat'] : '', isset($query) ? $query['lon'] : ''
            ]);

            if (!file_exists(storage_path() . '/uploads/sample/data.csv')) {

                $first_line = 'IP,Date,Time,Accepted cookies,Device type,Browser language,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                file_put_contents(storage_path() . '/uploads/sample/data.csv', $first_line . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            file_put_contents(storage_path() . '/uploads/sample/data.csv', $new_line . PHP_EOL, FILE_APPEND | LOCK_EX);

            return response()->json('success');
        }
        return response()->json('error');
    }
    function get_device_type($user_agent)
    {
        $mobile_regex = '/(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/i';
        $tablet_regex = '/(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/i';

        if (preg_match_all($mobile_regex, $user_agent)) {
            return 'mobile';
        } else {

            if (preg_match_all($tablet_regex, $user_agent)) {
                return 'tablet';
            } else {
                return 'desktop';
            }
        }
    }
    public function chatgptkey(Request $request)
    {
        if (\Auth::user()->type == 'Owner') {
            $user = \Auth::user();
            if ($request->enable_chatgpt == 'on') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'chatgpt_key' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
            }
            if (!empty($request->chatgpt_key)) {
                $post = $request->all();
                $post['chatgpt_key'] = $request->chatgpt_key;
                $post['enable_chatgpt']       = $request->enable_chatgpt ?? 'off';
                unset($post['_token']);
                foreach ($post as $key => $data) {
                    $settings = Utility::settings();
                    if (in_array($key, array_keys($settings))) {
                        \DB::insert(
                            'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                            [
                                $data,
                                $key,
                                \Auth::user()->ownerId(),
                                date('Y-m-d H:i:s'),
                                date('Y-m-d H:i:s'),
                            ]
                        );
                    }
                }
            }
            return redirect()->back()->with('success', __('ChatGPT Key successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
