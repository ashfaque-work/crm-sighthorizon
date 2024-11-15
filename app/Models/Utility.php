<?php

namespace App\Models;

use App\Mail\CommonEmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Tax;
use Pusher\Pusher;
use GuzzleHttp;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;

class Utility extends Model
{
    private static $fetchSetting = null;
    private static $storageSetting = null;
    private static $languages = null;
    private static $colorset = null;
    public static function settings()
    {
        if(is_null(self::$fetchSetting)){

            $data = DB::table('settings');

            if (Auth::check()) {
                $data->where('created_by', '=', Auth::user()->ownerId())->orWhere('created_by', '=', 1);
            } else {
                $data->where('created_by', '=', 1)->orWhere('created_by', '=', 2);
            }

            $data = $data->get();

            self::$fetchSetting = $data;
        }

        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_enable_stripe" => "off",
            "site_stripe_key" => "",
            "site_stripe_secret" => "",
            "site_enable_paypal" => "off",
            "site_paypal_mode" => "sandbox",
            "site_paypal_client_id" => "Ad7xfQTPWKZAcZNDgKCYEL1W7NDfrV7JzV23Os_kqTVSy5_zzIPcL1-h3YRtfAJTkLraUwZwB77f4Dln",
            "site_paypal_secret_key" => "EMGQOacnVfLAYsL6Fdfhtkp_ci5xnvQVZxxsBCBBTIOq6G_aqo9oVQvSFEYDIm5S5_z69Rb7Tl4UGIAu",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "Sight Horizon LLP",
            "company_address" => "5ES6, EAST TOWER, Mani Casadona, Action Area I, Newtown, Chakpachuria",
            "company_city" => "Kolkata",
            "company_state" => "West Bengal",
            "company_zipcode" => "700160",
            "company_country" => "India",
            "company_telephone" => "+91 7004325512",
            "company_email" => "info@sighthorizon.com",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INV",
            "contract_prefix" => "#CON",
            "estimation_prefix" => "#EST",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "invoice_logo" => "",
            "contract_template" => "template1",
            "contract_color" => "ffffff",
            "estimation_template" => "template1",
            "estimation_color" => "ffffff",
            "estimation_logo" => "",
            "mdf_prefix" => "#MDF",
            "mdf_template" => "template1",
            "mdf_color" => "ffffff",
            "mdf_logo" => "",
            "default_language" => "en",
            "enable_landing" => "no",
            "footer_title" => "Payment Information",
            "footer_note" => "Thank you for your business.",
            // "gdpr_cookie" => "",
            "Email_verify" => "",
            "cookie_text" => "",
            "zoom_account_id" => "",
            "zoom_client_id" => "",
            "zoom_client_secret " => "",
            "slack_webhook" => "",
            "telegrambot" => "",
            "telegramchatid" => "",
            "signup_button" => "on",
            "color" => "theme-4",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "SITE_RTL" => "off",
            "enable_google_calendar" => "off",


            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            'contactus_url' => '#',
            'is_payfast_enabled' => '',
            'payfast_merchant_id' => '',
            'payfast_merchant_key' => '',
            'payfast_signature' => '',
            'chatgpt_key' => '',
            'enable_chatgpt' => 'off',
            'disable_lang' => '',
            'enable_chat' => '',
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster'=> '',
            'recaptcha_module'=>'',
            'google_recaptcha_key'=> '',
            'google_recaptcha_secret'=>'',
            'mail_driver'=>'',
            'mail_host'=>'',
            'mail_port' =>'',
            'mail_username'=>'',
            'mail_password'=>'',
            'mail_encryption'=>'',
            'mail_from_address'=>'',
            'mail_from_name' =>'',


        ];

        foreach (self::$fetchSetting as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }
    public static function settingsById($id)
    {
        // $id = \Auth::user()->creatorId();
        if(is_null(self::$storageSetting)){

            $data = DB::table('settings');
            $data = $data->where('created_by', '=', 1);
            $data     = $data->get();

            self::$storageSetting = $data;
        }
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "Sight Horizon LLP",
            "company_address" => "5ES6, EAST TOWER, Mani Casadona, Action Area I, Newtown, Chakpachuria",
            "company_city" => "Kolkata",
            "company_state" => "West Bengal",
            "company_zipcode" => "700160",
            "company_country" => "India",
            "company_telephone" => "+91 7004325512",
            "company_email" => "info@sighthorizon.com",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INVO",
            "journal_prefix" => "#JUR",
            "invoice_color" => "ffffff",
            "proposal_prefix" => "#PROP",
            "proposal_color" => "ffffff",
            "proposal_logo" => "2_proposal_logo.png",
            "retainer_logo" => "2_retainer_logo.png",
            "invoice_logo" => "2_invoice_logo.png",
            "bill_logo" => "2_bill_logo.png",
            "retainer_color" => "ffffff",
            "bill_prefix" => "#BILL",
            "bill_color" => "ffffff",
            "customer_prefix" => "#CUST",
            "vender_prefix" => "#VEND",
            "contract_prefix" => "#CON",
            "retainer_prefix" => "#RET",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "bill_template" => "template1",
            "proposal_template" => "template1",
            "retainer_template" => "template1",
            "contract_template" => "template1",
            "registration_number" => "",
            "vat_number" => "",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "decimal_number" => "2",
            "tax_number" => "on",
            "tax_type" => "",
            "shipping_display" => "on",
            "journal_prefix" => "#JUR",
            "display_landing_page" => "on",
            "title_text" => "",
            // 'gdpr_cookie' => "off",
            'cookie_text' => "",
            "twilio_sid" => "",
            "twilio_token" => "",
            "twilio_from" => "",
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "company_logo_light" => "logo-light.png",
            "company_logo_dark" => "logo-dark.png",
            "company_favicon" => "",
            "SITE_RTL" => "off",
            "owner_signature" => "",
            "cust_darklayout" => "off",
            "payment_notification" => 1,
            "chatgpt_key" => '',
            "disable_lang" => '',
        ];
        foreach (self::$storageSetting as $row) {
            $settings[$row->name] = $row->value;
        }
        return $settings;
    }

    public static function settingById($id)
    {
        $data = DB::table('settings')->where('created_by', '=', $id)->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_enable_stripe" => "off",
            "site_stripe_key" => "",
            "site_stripe_secret" => "",
            "site_enable_paypal" => "off",
            "site_paypal_mode" => "sandbox",
            "site_paypal_client_id" => "Ad7xfQTPWKZAcZNDgKCYEL1W7NDfrV7JzV23Os_kqTVSy5_zzIPcL1-h3YRtfAJTkLraUwZwB77f4Dln",
            "site_paypal_secret_key" => "EMGQOacnVfLAYsL6Fdfhtkp_ci5xnvQVZxxsBCBBTIOq6G_aqo9oVQvSFEYDIm5S5_z69Rb7Tl4UGIAu",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "company_name" => "Sight Horizon LLP",
            "company_address" => "5ES6, EAST TOWER, Mani Casadona, Action Area I, Newtown, Chakpachuria",
            "company_city" => "Kolkata",
            "company_state" => "West Bengal",
            "company_zipcode" => "700160",
            "company_country" => "India",
            "company_telephone" => "+91 7004325512",
            "company_email" => "info@sighthorizon.com",
            "company_email_from_name" => "",
            "invoice_prefix" => "#INV",
            "contract_prefix" => "#CON",
            "estimation_prefix" => "#EST",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "invoice_logo" => "",
            "contract_template" => "template1",
            "contract_color" => "ffffff",
            "estimation_template" => "template1",
            "estimation_color" => "ffffff",
            "estimation_logo" => "",
            "mdf_prefix" => "#MDF",
            "mdf_template" => "template1",
            "mdf_color" => "ffffff",
            "mdf_logo" => "",
            "default_language" => "en",
            "enable_landing" => "no",
            "footer_title" => "Payment Information",
            "footer_note" => "Thank you for your business.",
            // "gdpr_cookie" => "",
            "Email_verify" => "",
            "cookie_text" => "",
            "zoom_account_id" => "",
            "zoom_client_id" => "",
            "zoom_client_secret" => "",
            "slack_webhook" => "",
            "telegrambot" => "",
            "telegramchatid" => "",
            "signup_button" => "on",
            "color" => "theme-4",
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "dark_logo" => "logo-dark.png",
            "light_logo" => "logo-light.png",
            "SITE_RTL" => "off",
            "enable_google_calendar" => "off",
            "meta_keyword" => "",
            "meta_description" => "",
            "meta_image" => "meta_image.png",
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
            'enable_cookie' => 'on',
            'necessary_cookies' => 'on',
            'cookie_logging' => 'on',
            'cookie_title' => 'We use cookies!',
            'cookie_description' => 'Hi, this website uses essential cookies to ensure its proper operation and tracking cookies to understand how you interact with it',
            'strictly_cookie_title' => 'Strictly necessary cookies',
            'strictly_cookie_description' => 'These cookies are essential for the proper functioning of my website. Without these cookies, the website would not work properly',
            'more_information_description' => 'For any queries in relation to our policy on cookies and your choices, please contact us',
            'contactus_url' => '#',
            'is_payfast_enabled' => '',
            'payfast_merchant_id' => '',
            'payfast_merchant_key' => '',
            'payfast_signature' => '',
            'chatgpt_key' => '',
            'enable_chatgpt' => 'off',
            'iyzipay_public_key' => '',
            'iyzipay_secret_key' => '',
            'iyzipay_mode' => '',
            'is_iyzipay_enabled' => '',
            'disable_lang' => '',
        ];

        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function colorCodeData($type)
    {
        if ($type == 'event') {
            return 1;
        } elseif ($type == 'zoom_meeting') {
            return 2;
        } elseif ($type == 'task') {
            return 3;
        } elseif ($type == 'appointment') {
            return 11;
        } elseif ($type == 'rotas') {
            return 3;
        } elseif ($type == 'holiday') {
            return 4;
        } elseif ($type == 'call') {
            return 10;
        } elseif ($type == 'meeting') {
            return 5;
        } elseif ($type == 'leave') {
            return 6;
        } elseif ($type == 'work_order') {
            return 7;
        } elseif ($type == 'lead') {
            return 7;
        } elseif ($type == 'deal') {
            return 8;
        } elseif ($type == 'interview_schedule') {
            return 9;
        } else {
            return 11;
        }
    }

    public static $colorCode = [
        1 => 'event-warning',
        2 => 'bg-secondary',
        3 => 'event-success',
        4 => 'event-warning',
        5 => 'event-danger',
        6 => 'event-dark',
        7 => 'event-black',
        8 => 'event-info',
        9 => 'event-secondary',
        10 => 'event-success',
        11 => 'event-warning',

    ];

    public static function googleCalendarConfig()
    {
        $setting = Utility::settings();
        // $path = storage_path('googlecalender/'.$setting['google_calender_json_file']);
        $path = storage_path($setting['google_calender_json_file']);
        config([
            'google-calendar.default_auth_profile' => 'service_account',
            'google-calendar.auth_profiles.service_account.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.credentials_json' => $path,
            'google-calendar.auth_profiles.oauth.token_json' => $path,
            'google-calendar.calendar_id' => isset($setting['google_clender_id']) ? $setting['google_clender_id'] : '',
            'google-calendar.user_to_impersonate' => '',

        ]);
    }

    public static function addCalendarData($request, $type)
    {

        Self::googleCalendarConfig();
        $event = new Event;
        $event->name = $request->title;
        $event->startDateTime = Carbon::parse($request->get('start_date'));
        $event->endDateTime = Carbon::parse($request->get('start_date'))->addMinutes($request->duration);
        $event->colorId = Self::colorCodeData($type);

        $event->save();
    }

    public static function getCalendarData($type)
    {

        Self::googleCalendarConfig();

        $data = Event::get();

        $type = Self::colorCodeData($type);
        $arrayJson = [];
        foreach ($data as $val) {
            $end_date = date_create($val->endDateTime);
            date_add($end_date, date_interval_create_from_date_string("1 days"));

            if ($val->colorId == "$type") {

                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->summary,
                    "start" => $val->startDateTime,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => Self::$colorCode[$type],
                    "allDay" => true,
                ];
            }
        }

        return $arrayJson;
    }

    public static function payment_settings()
    {
        $data = \DB::table('admin_payment_settings');
        $user=\Auth::user();
        if(Auth::check())
        {
            $data->where('created_by', '=', $user->createdId());
        }
        else
        {
            $data->where('created_by', '=', 1);
        }
        $data = $data->get();
        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }


        return $res;
    }
    public static function non_auth_payment_settings($id)
    {
        $data = \DB::table('admin_payment_settings');
        $data = $data->where('created_by', '=', $id);
        $data = $data->get();
        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }

    public function paymentSetting()
    {

        $admin_payment_setting = Utility::payment_settings();

        $this->currancy_symbol = isset($admin_payment_setting['currency_symbol']) ? $admin_payment_setting['currency_symbol'] : '';
        $this->currancy = isset($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : '';
        $this->paypal_client_id = isset($admin_payment_setting['paypal_client_id']) ? $admin_payment_setting['paypal_client_id'] : '';
        $this->paypal_mode = isset($admin_payment_setting['paypal_mode']) ? $admin_payment_setting['paypal_mode'] : '';
        $this->paypal_secret_key = isset($admin_payment_setting['paypal_secret_key']) ? $admin_payment_setting['paypal_secret_key'] : '';

        return;
    }


    public static function set_payment_settings()
    {
        $data = DB::table('admin_payment_settings');

        if (Auth::check()) {
            $data->where('created_by', '=', Auth::user()->ownerId());
        } else {
            $data->where('created_by', '=', 1);
        }
        $data = $data->get();
        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }

    public static function getValByName($key)
    {

        $setting = self::settings();

        if (!isset($setting[$key]) || empty($setting[$key])) {
            $setting[$key] = '';
        }

        return $setting[$key];
    }

    public static function languages()
    {
        if (is_null(self::$languages)) {
            $languages = Utility::langList();

            if (\Schema::hasTable('languages')) {
                $settings = Utility::langSetting();
                if (!empty($settings['disable_lang'])) {
                    $disabledlang = explode(',', $settings['disable_lang']);
                    $languages = Languages::whereNotIn('code', $disabledlang)->pluck('fullname', 'code');
                } else {
                    $languages = Languages::pluck('fullname', 'code');
                }
            }

            self::$languages = $languages;
        }

        return self::$languages;
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}='{$envValue}'\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        $str .= "\n";

        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        return true;
    }

    public static function sendNotification($type, $user_id, $obj)
    {
        if (!Auth::check() || $user_id != \Auth::user()->id) {

            $notification = Notification::create(
                [
                    'user_id' => $user_id,
                    'type' => $type,
                    'data' => json_encode($obj),
                    'is_read' => 0,
                ]
            );

            // Push Notification
            $options = array(
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => false,
            );

            $pusher          = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                $options
            );
            $data            = [];
            $data['html']    = $notification->toHtml();
            $data['user_id'] = $notification->user_id;

            try {
                $pusher->trigger('send_notification', 'notification', $data);
            } catch (\Exception $e) {
            }


            // End Push Notification
        }
    }
    public static function webhookSetting($module, $user_id = null)
    {
        if (\Auth::check()) {
            $webhook = Webhook::where('module', $module)->where('created_by', '=', \Auth::user()->creatorId())->first();
        } else {
            $webhook = Webhook::where('module', $module)->where('created_by', '=', $user_id)->first();
        }
        if (!empty($webhook)) {
            $url = $webhook->url;
            $method = $webhook->method;
            $reference_url  = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $data['method'] = $method;
            $data['reference_url'] = $reference_url;
            $data['url'] = $url;
            return $data;
        }
        return false;
    }
    public static function WebhookCall($url = null, $parameter = null, $method = 'POST')
    {

        if (!empty($url) && !empty($parameter)) {
            try {

                $curlHandle = curl_init($url);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $parameter);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                $curlResponse = curl_exec($curlHandle);
                curl_close($curlHandle);
                if (empty($curlResponse)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            '6777f0',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000',
        ];
        $arr['templates'] = [
            "template1" => "New York",
            "template2" => "Toronto",
            "template3" => "Rio",
            "template4" => "London",
            "template5" => "Istanbul",
            "template6" => "Mumbai",
            "template7" => "Hong Kong",
            "template8" => "Tokyo",
            "template9" => "Sydney",
            "template10" => "Paris",
        ];

        return $arr;
    }

    // Email Template Modules Function START
    // Common Function That used to send mail with check all cases

    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj)
    {

        $usr = \Auth::user();
        //Remove Current Login user Email don't send mail to them+
        if($usr){

            unset($mailTo[$usr->id]);
        }

        $mailTo = array_values($mailTo);

        // find template is exist or not in our record
        $template = EmailTemplate::where('name', 'LIKE', $emailTemplate)->first();

        if (isset($template) && !empty($template)) {
            // check template is active or not by company
            $is_active = UserEmailTemplate::where('template_id', '=', $template->id)->first();

            if ($is_active->is_active == 1) {

                $settings = self::settings();


                // get email content language base
                if($usr){

                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();
                }
                else{
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE','en')->first();

                }

                $content->from = $template->from;
                if (!empty($content->content)) {
                    $content->content = self::replaceVariable($content->content, $obj);
                    // send email
                    try {
                        $config=config([
                            'mail.driver'       => !empty($settings['mail_driver']) ? $settings['mail_driver'] : '',
                            'mail.host'         => !empty($settings['mail_host']) ? $settings['mail_host'] : '',
                            'mail.port'         => !empty($settings['mail_port']) ? $settings['mail_port'] : '',
                            'mail.username'     => !empty($settings['mail_username']) ? $settings['mail_username'] : '',
                            'mail.password'     => !empty($settings['mail_password']) ? $settings['mail_password'] : '',
                            'mail.encryption'   => !empty($settings['mail_encryption']) ? $settings['mail_encryption'] : '',
                            'mail.from.address' => !empty($settings['mail_from_address']) ? $settings['mail_from_address'] : '',
                            'mail.from.name'    => !empty($settings['mail_from_name']) ? $settings['mail_from_name'] : '',
                        ]);
                        \Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                    } catch (\Exception $e) {
                        // $error = __('E-Mail has been not sent due to SMTP configuration');
                        $error = $e->getMessage();

                    }

                    if (isset($error)) {
                        $arReturn = [
                            'is_success' => false,
                            'error' => $error,
                        ];
                    } else {
                        $arReturn = [
                            'is_success' => true,
                            'error' => false,
                        ];
                    }
                } else {
                    $arReturn = [
                        'is_success' => false,
                        'error' => __('Mail not send, email is empty'),
                    ];
                }

                return $arReturn;
            } else {
                return [
                    'is_success' => true,
                    'error' => false,
                ];
            }
        } else {
            return [
                'is_success' => false,
                'error' => __('Mail not send, email not found'),
            ];
        }
    }

    // used for replace email variable (parameter 'template_name','id(get particular record by id for data)')
    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{deal_old_stage}',
            '{deal_new_stage}',
            '{task_name}',
            '{task_priority}',
            '{task_status}',
            '{lead_name}',
            '{lead_email}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{lead_old_stage}',
            '{lead_new_stage}',
            '{estimation_name}',
            '{estimation_client}',
            '{estimation_status}',
            '{contract_subject}',
            '{contract_client}',
            '{contract_start_date}',
            '{contract_end_date}',
            '{app_name}',
            '{company_name}',
            '{email}',
            '{password}',
            '{app_url}',
            '{subject}',
            '{client_name}',
            '{estimation}',
            '{issue_date}',
            '{contract}',
            '{contract_value}',
            '{contract_name}',
            '{invoice}',
            '{amount}',
            '{payment_type}',
            '{payer_name}',







        ];
        $arrValue    = [
            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'deal_old_stage' => '-',
            'deal_new_stage' => '-',
            'task_name' => '-',
            'task_priority' => '-',
            'task_status' => '-',
            'lead_name' => '-',
            'lead_email' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'lead_old_stage' => '-',
            'lead_new_stage' => '-',
            'estimation_name' => '-',
            'estimation_client' => '-',
            'estimation_status' => '-',
            'contract_subject' => '-',
            'contract_client' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',
            'app_name' => '-',
            'company_name' => '-',
            'email' => '-',
            'password' => '-',
            'app_url' => '-',
            'subject' => '-',
            'client_name' => '-',
            'estimation' => '-',
            'issue_date' => '-',
            'contract' => '-',
            'contract_value' => '-',
            'contract_name' => '-',
            'invoice' => '-',
            'amount' => '-',
            'payment_type' => '-',
            'payer_name' => '-',










        ];

        foreach ($obj as $key => $val) {
            $arrValue[$key] = $val;
        }

        $arrValue['app_name']     = env('APP_NAME');
        $arrValue['company_name'] = self::settings()['company_name'];
        $arrValue['app_url']      = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    // Make Entry in email_tempalte_lang table when create new language
    public static function makeEmailLang($lang)
    {
        $template = EmailTemplate::all();
        foreach ($template as $t) {
            $default_lang                 = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang            = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang      = $lang;
            $emailTemplateLang->subject   = $default_lang->subject;
            $emailTemplateLang->content   = $default_lang->content;
            $emailTemplateLang->save();
        }
    }
    // Email Template Modules Function END

    // get font-color code accourding to bg-color
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }

    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R   = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for ($i = 0; $i < count($C); ++$i) {
            if ($C[$i] <= 0.03928) {
                $C[$i] = $C[$i] / 12.92;
            } else {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if ($L > 0.179) {
            $color = 'black';
        } else {
            $color = 'white';
        }

        return $color;
    }

    public static function delete_directory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    public static function addNewData()
    {
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('cache:clear');

        $usr            = Auth::user();

        $arrPermissions = [
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'Manage MDF Types',
            'Create MDF Type',
            'Edit MDF Type',
            'Delete MDF Type',
            'Manage MDF Sub Types',
            'Create MDF Sub Type',
            'Edit MDF Sub Type',
            'Delete MDF Sub Type',
            'Manage MDF Status',
            'Create MDF Status',
            'Edit MDF Status',
            'Delete MDF Status',
            'Create MDF Payment',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',
            'Manage Form Builder',
            'Create Form Builder',
            'Edit Form Builder',
            'Delete Form Builder',
            'Manage Form Field',
            'Create Form Field',
            'Edit Form Field',
            'Delete Form Field',
            'View Form Response',
            'Invoice Report',
            'Expense Report',
            'Income vs Expense Report',
        ];

        foreach ($arrPermissions as $ap) {
            // check if permission is not created then create it.
            $permission = Permission::where('name', 'LIKE', $ap)->first();
            if (empty($permission)) {
                Permission::create(['name' => $ap]);
            }
        }
if(\Auth::user()->type == 'Owner'){
    $ownerRole        = Role::where('name', 'LIKE', 'Owner')->where('created_by', '=', 0)->first();
}
else{
        $ownerRole        = Role::where('name', 'LIKE', 'Owner')->where('created_by', '=', $usr->ownerId())->first();
}
        $ownerPermissions = $ownerRole->getPermissionNames()->toArray();

        $ownerNewPermission = [
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'Manage MDF Types',
            'Create MDF Type',
            'Edit MDF Type',
            'Delete MDF Type',
            'Manage MDF Sub Types',
            'Create MDF Sub Type',
            'Edit MDF Sub Type',
            'Delete MDF Sub Type',
            'Manage MDF Status',
            'Create MDF Status',
            'Edit MDF Status',
            'Delete MDF Status',
            'Create MDF Payment',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',
            'Manage Form Builder',
            'Create Form Builder',
            'Edit Form Builder',
            'Delete Form Builder',
            'Manage Form Field',
            'Create Form Field',
            'Edit Form Field',
            'Delete Form Field',
            'View Form Response',
            'Invoice Report',
            'Expense Report',
            'Income vs Expense Report',
        ];

        foreach ($ownerNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $ownerPermissions)) {
                $permission = Permission::findByName($op);
                $ownerRole->givePermissionTo($permission);
            }
        }

        $userRole        = Role::where('name', 'LIKE', 'Manager')->first();
        $userPermissions = $userRole->getPermissionNames()->toArray();

        $userNewPermission = [
            'Manage MDFs',
            'Request MDF',
            'Edit MDF',
            'Delete MDF',
            'View MDF',
            'MDF Add Expense',
            'MDF Edit Expense',
            'MDF Delete Expense',
        ];

        foreach ($userNewPermission as $op) {
            // check if permission is not assign to owner then assign.
            if (!in_array($op, $userPermissions)) {
                $permission = Permission::findByName($op);
                $userRole->givePermissionTo($permission);
            }
        }
    }

    public static function get_messenger_packages_migration()
    {
        $totalMigration = 0;
        $messengerPath  = glob(base_path() . '/vendor/munafio/chatify/src/database/migrations' . DIRECTORY_SEPARATOR . '*.php');

        if (!empty($messengerPath)) {
            $messengerMigration = str_replace('.php', '', $messengerPath);
            $totalMigration     = count($messengerMigration);
        }

        return $totalMigration;
    }

    // Used to check permission is exist or not in database
    public static function checkPermissionExist($permission)
    {
        $permission = Permission::where('name', 'LIKE', $permission)->count();

        return $permission;
    }



    public static function getselectedThemeColor()
    {
        $color = env('THEME_COLOR');
        if ($color == "" || $color == null) {
            $color = 'blue';
        }
        return $color;
    }

    public static function getAllThemeColors()
    {
        $colors = [
            'blue', 'denim', 'sapphire', 'olympic', 'violet', 'black', 'cyan', 'dark-blue-natural', 'gray-dark', 'light-blue', 'light-purple', 'magenta', 'orange-mute', 'pale-green', 'rich-magenta', 'rich-red', 'sky-gray'
        ];
        return $colors;
    }

    public static function checkImgTransparent($img)
    {
        try {
            $im = imagecreatefrompng($img);
            $rgba = imagecolorat($im, 1, 1);
            $alpha = ($rgba & 0x7F000000) >> 24;
            if ($alpha > 0) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getDateFormated($date, $time = false)
    {
        if (!empty($date) && $date != '0000-00-00') {
            if ($time == true) {
                return date("d M Y H:i A", strtotime($date));
            } else {
                return date("d M Y", strtotime($date));
            }
        } else {
            return '';
        }
    }



    public static function ownerIdforInvoice($id)
    {
        $user = User::where(['id' => $id])->first();
        if (!is_null($user)) {
            if ($user->type == "Owner") {
                return $user->id;
            } else {
                $user1 = User::where(['id' => $user->created_by, $user->type => 'Owner'])->first();
                if (!is_null($user1)) {
                    return $user->id;
                }
            }
        }
        return 0;
    }
    public static function invoice_payment_settings($id)
    {
        $data = [];
        $user = User::where(['id' => $id])->first();

        if (!is_null($user)) {
            $data = DB::table('admin_payment_settings');
            $data->where('created_by', '=', $user->id);
            $data = $data->get();
        }

        $res = [];
        foreach ($data as $key => $value) {
            $res[$value->name] = $value->value;
        }

        return $res;
    }
    public static function tax($taxes)
    {

        $taxArr = explode(',', $taxes);

        $taxes  = [];
        foreach ($taxArr as $tax) {
            $taxes[] = Tax::find($tax);
        }

        return $taxes;
    }
    public static function taxRate($taxRate, $price)
    {


        return ($taxRate / 100) * ($price);
    }

    public static function send_slack_msg($slug, $obj, $user_id = null)
    {
        if (\Auth::check()) {
            $user = \Auth::user();
        } else {
            $user = User::where('id', $user_id)->first();
        }
        $notification_template = NotificationTemplates::where('slug', $slug)->first();


        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', $user->lang)->where('created_by', '=', $user->id)->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', $user->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }


        }
        if (isset($msg)) {
            $settings = Utility::settingById($user->id);
            try {
                if (isset($settings['slack_webhook']) && !empty($settings['slack_webhook'])) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $settings['slack_webhook']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $msg]));

                    $headers = array();
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);
                }
            } catch (\Exception $e) {
            }
        }
    }
    public static function send_telegram_msg($slug, $obj, $user_id = null)
    {
        if (\Auth::check()) {
            $user = \Auth::user();
        } else {
            $user = User::where('id', $user_id)->first();
        }

        $notification_template = NotificationTemplates::where('slug', $slug)->first();
        if (!empty($notification_template) && !empty($obj)) {
            $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', $user->lang)->where('created_by', '=', $user->id)->first();
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', $user->lang)->first();
            }
            if (empty($curr_noti_tempLang)) {
                $curr_noti_tempLang       = NotificationTemplateLangs::where('parent_id', '=', $notification_template->id)->where('lang', 'en')->first();
            }
            if (!empty($curr_noti_tempLang) && !empty($curr_noti_tempLang->content)) {
                $msg = self::replaceVariable($curr_noti_tempLang->content, $obj);
            }
        }
        try {
            $settings = Utility::settingById($user->id);

            // $msg = $resp;
            // Set your Bot ID and Chat ID.
            $telegrambot = $settings['telegrambot'];
            $telegramchatid = $settings['telegramchatid'];
            // Function call with your own text or variable
            $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
            $data = array(
                'chat_id' => $telegramchatid,
                'text' => $msg,
            );
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($data),
                ),
            );
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            $url = $url;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public static function colorset()
    {
        if (is_null(self::$colorset)) {
            if (\Auth::user()) {

                if (\Auth::user()->type == 'Super Admin') {
                    $user = \Auth::user();
                    $setting = DB::table('settings')->where('created_by', $user->id)->pluck('value', 'name')->toArray();
                } else {
                    $setting = DB::table('settings')->where('created_by', \Auth::user()->ownerId())->pluck('value', 'name')->toArray();
                }
            } else {
                $setting = DB::table('settings')->pluck('value', 'name')->toArray();


            }

            if (!isset($setting['color'])) {
                $setting = Utility::settings();
            }
            self::$colorset = $setting;
        }


        return self::$colorset;

    }


    public static function get_superadmin_logo()
    {
        $is_dark_mode = self::getValByName('cust_darklayout');
        if ($is_dark_mode == 'on') {
            return 'logo-light.png';
        } else {
            return 'logo-dark.png';
        }
    }

    public static function get_company_logo()
    {
        $is_dark_mode = self::getValByName('cust_darklayout');

        if ($is_dark_mode == 'on') {
            $logo = self::getValByName('cust_darklayout');
            return Utility::getValByName('light_logo');
        } else {
            return Utility::getValByName('dark_logo');
        }
    }




    public static function getStorageSetting()
    {

         if(is_null(self::$storageSetting)){

            $data = DB::table('settings');
            $data = $data->where('created_by', '=', 1);
            $data     = $data->get();

            self::$storageSetting = $data;
        }
        $settings = [
            "storage_setting" => "local",
            "local_storage_validation" => "jpg,jpeg,png,xlsx,xls,csv,pdf",
            "local_storage_max_upload_size" => "2048000",
            "s3_key" => "",
            "s3_secret" => "",
            "s3_region" => "",
            "s3_bucket" => "",
            "s3_url"    => "",
            "s3_endpoint" => "",
            "s3_max_upload_size" => "",
            "s3_storage_validation" => "",
            "wasabi_key" => "",
            "wasabi_secret" => "",
            "wasabi_region" => "",
            "wasabi_bucket" => "",
            "wasabi_url" => "",
            "wasabi_root" => "",
            "wasabi_max_upload_size" => "",
            "wasabi_storage_validation" => "",
        ];

        foreach (self::$storageSetting as $row) {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }


    public static function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $settings = Utility::getStorageSetting();

            if (!empty($settings['storage_setting'])) {

                if ($settings['storage_setting'] == 'wasabi') {

                    config(
                        [
                            'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                            'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                            'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                            'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                            'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                        ]
                    );

                    $max_size = !empty($settings['wasabi_max_upload_size']) ? $settings['wasabi_max_upload_size'] : '2048';
                    $mimes =  !empty($settings['wasabi_storage_validation']) ? $settings['wasabi_storage_validation'] : '';
                } else if ($settings['storage_setting'] == 's3') {
                    config(
                        [
                            'filesystems.disks.s3.key' => $settings['s3_key'],
                            'filesystems.disks.s3.secret' => $settings['s3_secret'],
                            'filesystems.disks.s3.region' => $settings['s3_region'],
                            'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                            'filesystems.disks.s3.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings['s3_max_upload_size']) ? $settings['s3_max_upload_size'] : '2048';
                    $mimes =  !empty($settings['s3_storage_validation']) ? $settings['s3_storage_validation'] : '';
                } else {
                    $max_size = !empty($settings['local_storage_max_upload_size']) ? $settings['local_storage_max_upload_size'] : '2048';

                    $mimes =  !empty($settings['local_storage_validation']) ? $settings['local_storage_validation'] : '';
                }


                $file = $request->$key_name;


                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {

                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = \Validator::make($request->all(), [
                    $key_name => $validation
                ]);

                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {

                    $name = $name;

                    if ($settings['storage_setting'] == 'local') {
                        $request->$key_name->move(storage_path($path), $name);
                        $path = $path . $name;
                    } else if ($settings['storage_setting'] == 'wasabi') {

                        $path = \Storage::disk('wasabi')->putFileAs(
                            $path,
                            $file,
                            $name
                        );

                        // $path = $path.$name;

                    } else if ($settings['storage_setting'] == 's3') {

                        $path = \Storage::disk('s3')->putFileAs(
                            $path,
                            $file,
                            $name
                        );

                    }


                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $path
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }


    public static function get_file($path)
    {
        $settings = Utility::getStorageSetting();

        try {
            if ($settings['storage_setting'] == 'wasabi') {
                config(
                    [
                        'filesystems.disks.wasabi.key' => $settings['wasabi_key'],
                        'filesystems.disks.wasabi.secret' => $settings['wasabi_secret'],
                        'filesystems.disks.wasabi.region' => $settings['wasabi_region'],
                        'filesystems.disks.wasabi.bucket' => $settings['wasabi_bucket'],
                        'filesystems.disks.wasabi.endpoint' => 'https://s3.' . $settings['wasabi_region'] . '.wasabisys.com'
                    ]
                );
            } elseif ($settings['storage_setting'] == 's3') {
                config(
                    [
                        'filesystems.disks.s3.key' => $settings['s3_key'],
                        'filesystems.disks.s3.secret' => $settings['s3_secret'],
                        'filesystems.disks.s3.region' => $settings['s3_region'],
                        'filesystems.disks.s3.bucket' => $settings['s3_bucket'],
                        'filesystems.disks.s3.use_path_style_endpoint' => false,
                    ]
                );
            }

            return \Storage::disk($settings['storage_setting'])->url($path);
        } catch (\Throwable $th) {
            return '';
        }
    }
    public static function flagOfCountry()
    {
        $arr = [
            'ar' => '🇦🇪 ar',
            'da' => '🇩🇰 da',
            'de' => '🇩🇪 de',
            'es' => '🇪🇸 es',
            'fr' => '🇫🇷 fr',
            'it' =>  '🇮🇹 it',
            'ja' => '🇯🇵 ja',
            'nl' => '🇳🇱 nl',
            'pl' => '🇵🇱 pl',
            'ru' => '🇷🇺 ru',
            'pt' => '🇵🇹 pt',
            'en' => '🇮🇳 en',
            'tr' => '🇹🇷 tr',
            'pt-br' => '🇵🇹 pt-br',
        ];
        return $arr;
    }
    public static function languagecreate()
    {
        $languages = Utility::langList();
        foreach ($languages as $key => $lang) {
            $languageExist = Languages::where('code', $key)->first();
            if (empty($languageExist)) {
                $language = new Languages();
                $language->code = $key;
                $language->fullname = $lang;
                $language->save();
            }
        }
    }
    public static function langList()
    {
        $languages = [
            "ar" => "Arabic",
            "zh" => "Chinese",
            "da" => "Danish",
            "de" => "German",
            "en" => "English",
            "es" => "Spanish",
            "fr" => "French",
            "he" => "Hebrew",
            "it" => "Italian",
            "ja" => "Japanese",
            "nl" => "Dutch",
            "pl" => "Polish",
            "pt" => "Portuguese",
            "ru" => "Russian",
            "tr" => "Turkish",
            "pt-br" => "Portuguese(Brazil)",
        ];
        return $languages;
    }
    public static function langSetting()
    {

        if(is_null(self::$storageSetting)){

            $data = DB::table('settings');
            $data = $data->where('created_by', '=', 1);
            $data     = $data->get();

            self::$storageSetting = $data;
        }

        // Initialize an empty array for settings
        $settings = [];

        // Iterate through the retrieved data and populate the $settings array
        foreach (self::$storageSetting as $row) {
        $settings[$row->name] = $row->value;
        }

        return self::$storageSetting;

     }
     public static function mode_layout()
     {
         $settings = [
             "cust_darklayout" => "off",
             "cust_theme_bg" => "off",
             "SITE_RTL" => "off",
         ];

         if (\Auth::check()) {
             $user = \Auth::user();
             // $settingTable = $user->user_type === 'company' ? 'settings' : 'location_settings';
             // $user_id = $user->user_type === 'company' ? $user->id : User::userCurrentLocation();
             // $setting = DB::table($settingTable)->where('created_by', $user_id)->pluck('value', 'name')->toArray();
             $setting = $user->user_type === 'company' ? self::LocationSetting() : self::settings();

             return array_merge($settings, $setting);
         }

         return $settings;
     }

}
