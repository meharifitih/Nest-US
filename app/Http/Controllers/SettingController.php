<?php

namespace App\Http\Controllers;

use App\Models\Custom;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class SettingController extends Controller
{

    //    ---------------------- Account --------------------------------------------------------
    public function index()
    {
        $loginUser = \Auth::user();
        $settings = settings();
        $timezones = config('timezones');
        return view('settings.index', compact('loginUser', 'settings', 'timezones'));
    }

    public function accountData(Request $request)
    {
        $loginUser = \Auth::user();
        $user = User::find($loginUser->id);
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'business_license' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'phone_number' => ['nullable', 'regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/'],
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }


        if ($request->hasFile('profile')) {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir = storage_path('uploads/profile/');
            $image_path = $dir . $loginUser->avatar;

            if (\File::exists($image_path)) {
                \File::delete($image_path);
            }

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $request->file('profile')->storeAs('upload/profile', $fileNameToStore, 'public');
        }

        if (!empty($request->profile)) {
            $user->profile = $fileNameToStore;
        }
        $user->first_name = $request->name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;

        if ($request->hasFile('business_license') && $loginUser->type == 'owner') {
            $filenameWithExt = $request->file('business_license')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('business_license')->getClientOriginalExtension();
            $fileNameToStoreLicense = $filename . '_' . time() . '.' . $extension;

            $dir = storage_path('uploads/business_license/');
            $license_path = $dir . $loginUser->business_license;

            if (\File::exists($license_path)) {
                \File::delete($license_path);
            }

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $request->file('business_license')->storeAs('upload/business_license', $fileNameToStoreLicense, 'public');
            $user->business_license = $fileNameToStoreLicense;
        }

        $user->save();

        return redirect()->back()->with('success', 'User profile settings successfully updated.')->with('tab', 'user_profile_settings');
    }

    public function accountDelete(Request $request)
    {
        $loginUser = \Auth::user();
        $loginUser->delete();

        return redirect()->back()->with('success', 'Your account successfully deleted.');
    }

    //    ---------------------- Password --------------------------------------------------------



    public function passwordData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'current_password' => 'required',
                    'new_password' => [
                        'required',
                        'min:8',
                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
                    ],
                    'confirm_password' => 'required|same:new_password',
                ],
                [
                    'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
                    'new_password.min' => 'Password must be at least 8 characters long.',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $loginUser = \Auth::user();
            $data = $request->All();

            $current_password = $loginUser->password;
            if (Hash::check($data['current_password'], $current_password)) {
                $user_id = $loginUser->id;
                $user = User::find($user_id);
                $user->password = Hash::make($data['new_password']);;
                $user->save();

                return redirect()->back()->with('success', __('Password successfully updated.'))->with('tab', 'password_settings');
            } else {
                return redirect()->back()->with('error', __('Please enter valid current password.'))->with('tab', 'password_settings');
            }
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'password_settings');
        }
    }

    //    ---------------------- General --------------------------------------------------------


    public function generalData(Request $request)
    {

        if (\Auth::user()->type == 'super admin') {

            $validator = \Validator::make(
                $request->all(),
                [
                    'application_name' => 'required',
                ]
            );

            if ($request->logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->landing_logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'landing_logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->favicon) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'favicon' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->light_logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'light_logo' => 'required|mimes:png',
                    ]
                );
            }


            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->application_name)) {
                $array = [
                    'APP_NAME' => $request->application_name,
                ];
                Custom::setCommon($array);
            }

            if (!empty($request->copyright)) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $request->copyright,
                        'copyright',
                        'common',
                        parentId(),
                    ]
                );
            }


            if ($request->logo) {
                $superadminLogoName = 'logo.png';
                $request->file('logo')->storeAs('upload/logo', $superadminLogoName, 'public');
            }

            if ($request->landing_logo) {
                $superadminLandLogoName = 'landing_logo.png';
                $request->file('landing_logo')->storeAs('upload/logo', $superadminLandLogoName, 'public');
            }

            if ($request->favicon) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo', $superadminFavicon, 'public');
            }
            if ($request->light_logo) {
                $superadminLightLogo = 'light_logo.png';
                $request->file('light_logo')->storeAs('upload/logo', $superadminLightLogo, 'public');
            }

            if (isset($request->landing_page)) {
                $themeSettings['landing_page'] = $request->landing_page;
            } else {
                $themeSettings['landing_page'] = 'off';
            }

            if (isset($request->register_page)) {
                $themeSettings['register_page'] = $request->register_page;
            } else {
                $themeSettings['register_page'] = 'off';
            }

            if (isset($request->owner_email_verification)) {
                $themeSettings['owner_email_verification'] = $request->owner_email_verification;
            } else {
                $themeSettings['owner_email_verification'] = 'off';
            }
            $themeSettings['pricing_feature'] = $request->pricing_feature;


            foreach ($themeSettings as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $val,
                        $key,
                        'common',
                        parentId(),
                    ]
                );
            }
        } elseif (\Auth::user()->type == 'owner') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'application_name' => 'required',
                ]
            );

            if ($request->logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->favicon) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'favicon' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->light_logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'light_logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            if (!empty($request->application_name) || !empty($request->copyright)) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $request->application_name,
                        'app_name',
                        'common',
                        parentId(),
                    ]
                );
            }
            if (!empty($request->copyright)) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $request->copyright,
                        'copyright',
                        'common',
                        parentId(),
                    ]
                );
            }


            if ($request->logo) {
                $ownerLogoName = parentId() . '_logo.png';
                $request->file('logo')->storeAs('upload/logo', $ownerLogoName, 'public');

                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $ownerLogoName,
                        'company_logo',
                        'common',
                        parentId(),
                    ]
                );
            }

            if ($request->favicon) {
                $ownerFaviconName = parentId() . '_favicon.png';
                $request->file('favicon')->storeAs('upload/logo', $ownerFaviconName, 'public');

                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $ownerFaviconName,
                        'company_favicon',
                        'common',
                        parentId(),
                    ]
                );
            }

            if ($request->light_logo) {
                $ownerLightLogoName = parentId() . '_light_logo.png';
                $request->file('light_logo')->storeAs('upload/logo', $ownerLightLogoName, 'public');

                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $ownerLightLogoName,
                        'light_logo',
                        'common',
                        parentId(),
                    ]
                );
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'))->with('tab', 'general_settings');
        }


        return redirect()->back()->with('success', __('General setting successfully saved.'))->with('tab', 'general_settings');
    }

    //    ---------------------- SMTP --------------------------------------------------------



    public function smtpData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'sender_name' => 'required',
                    'sender_email' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $smtpArray = [
                'FROM_NAME' => $request->sender_name,
                'FROM_EMAIL' => $request->sender_email,
                'SERVER_DRIVER' => config('mail.default'),
                'SERVER_HOST' => config('mail.mailers.smtp.host'),
                'SERVER_PORT' => config('mail.mailers.smtp.port'),
                'SERVER_USERNAME' => config('mail.mailers.smtp.username'),
                'SERVER_PASSWORD' => config('mail.mailers.smtp.password'),
                'SERVER_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            ];
            foreach ($smtpArray as $key => $val) {
                \DB::table('settings')->updateOrInsert(
                    [
                        'name' => $key,
                        'type' => 'smtp',
                        'parent_id' => parentId(),
                    ],
                    [
                        'value' => $val,
                    ]
                );
            }

            return redirect()->back()->with('success', __('SMTP settings successfully saved.'))->with('tab', 'email_SMTP_settings');
        } else {
            return redirect()->back()->with('error', __('Invalid user.'))->with('tab', 'email_SMTP_settings');
        }
    }

    public function smtpTest(Request $request)
    {
        return view('settings.testmail');
    }

    public function smtpTestMailSend(Request $request)
    {
        if (\Auth::check()) {
            $to = $request->email;
            $errorMessage = '';
            // Data for email
            $data = [
                'module' => 'test_mail',
                'subject' => 'Test Mail',
                'message' => __('This is a test mail.'),
            ];

            // Send email
            $response = sendEmail($to, $data);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
                return redirect()->back()->with('error', $errorMessage)->with('tab', 'email_SMTP_settings');;
            } else {
                $errorMessage = $response['message'];
                return redirect()->back()->with('success', $errorMessage)->with('tab', 'email_SMTP_settings');;
            }
        }
    }

    //    ---------------------- Payment --------------------------------------------------------



    public function paymentData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'CURRENCY' => 'required',
                'CURRENCY_SYMBOL' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $currencyArray = [
            'CURRENCY' => $request->CURRENCY,
            'CURRENCY_SYMBOL' => $request->CURRENCY_SYMBOL,
            'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
            'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
            'paypal_payment' => $request->paypal_payment ?? 'off',
            'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
        ];
        foreach ($currencyArray as $key => $val) {
            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $val,
                    $key,
                    'payment',
                    parentId(),
                ]
            );
        }

        //        For Bank Transfer Settings
        if (isset($request->bank_transfer_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_name' => 'required',
                    'bank_holder_name' => 'required',
                    'bank_account_number' => 'required',
                    'bank_ifsc_code' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $bankArray = [
                'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
                'bank_name' => $request->bank_name,
                'bank_holder_name' => $request->bank_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_ifsc_code' => $request->bank_ifsc_code,
                'bank_other_details' => !empty($request->bank_other_details) ? $request->bank_other_details : '',
            ];

            foreach ($bankArray as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        // For Strip Settings
        if (isset($request->stripe_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required',
                    'stripe_secret' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $stripeArray = [
                'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
            ];

            foreach ($stripeArray as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }


        // For Paypal Settings
        if (isset($request->paypal_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paypalArray = [
                'paypal_payment' => $request->paypal_payment ?? 'off',
                'paypal_mode' => $request->paypal_mode,
                'paypal_client_id' => $request->paypal_client_id,
                'paypal_secret_key' => $request->paypal_secret_key,
            ];

            foreach ($paypalArray as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }


        // For Flutterwave Settings
        if (isset($request->flutterwave_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required',
                    'flutterwave_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $flutterwaveArray = [
                'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
                'flutterwave_public_key' => $request->flutterwave_public_key,
                'flutterwave_secret_key' => $request->flutterwave_secret_key,
            ];

            foreach ($flutterwaveArray as $key => $val) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        // Save CBE and Telebirr account info
        $cbeTelebirrArray = [
            'cbe_account_name' => $request->cbe_account_name,
            'cbe_account_number' => $request->cbe_account_number,
            'telebirr_account_name' => $request->telebirr_account_name,
            'telebirr_account_number' => $request->telebirr_account_number,
        ];
        foreach ($cbeTelebirrArray as $key => $val) {
            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $val,
                    $key,
                    'payment',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Payment successfully saved.'))->with('tab', 'payment_settings');
    }

    //    ---------------------- Company  --------------------------------------------------------



    public function companyData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'company_name' => 'required',
                'company_email' => 'required',
                'company_phone' => 'required',
                'company_address' => 'required',
                'timezone' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        foreach ($settings as $key => $val) {
            \App\Models\Setting::updateOrInsert(
                [
                    'name' => $key,
                    'parent_id' => parentId(),
                ],
                [
                    'value' => $val
                ]
            );
        }
        return redirect()->back()->with('success', __('Company setting successfully saved.'))->with('tab', 'company_settings');
    }

    //    ---------------------- Language --------------------------------------------------------

    public function lanquageChange($lang)
    {
        $user = \Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language successfully changed.'));
    }

    public function themeSettings(Request $request)
    {

        $themeSettings = $request->all();
        unset($themeSettings['_token']);

        foreach ($themeSettings as $key => $val) {
            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $val,
                    $key,
                    'common',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Theme settings save successfully.'));
    }

    //    ---------------------- SEO Settings --------------------------------------------------------



    public function siteSEOData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'meta_seo_title' => 'required',
                'meta_seo_keyword' => 'required',
                'meta_seo_description' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);
        if ($request->meta_seo_image) {
            $seoFilenameWithExt = $request->file('meta_seo_image')->getClientOriginalName();
            $seoFilename = pathinfo($seoFilenameWithExt, PATHINFO_FILENAME);
            $supportExtension = $request->file('meta_seo_image')->getClientOriginalExtension();
            $seoFileName = $seoFilename . '_' . time() . '.' . $supportExtension;


            $request->file('meta_seo_image')->storeAs('upload/seo', $seoFileName, 'public');


            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $seoFileName,
                    'meta_seo_image',
                    'SEO',
                    parentId(),
                ]
            );
        }
        unset($settings['meta_seo_image']);
        foreach ($settings as $key => $val) {
            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $val,
                    $key,
                    'SEO',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Site SEO settings save successfully.'))->with('tab', 'site_SEO_settings');
    }

    // ---------------------- Google ReCaptcha Settings ---------------------------------------------
    public function googleRecaptchaData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'recaptcha_key' => 'required',
                'recaptcha_secret' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        $recaptchaArray = [
            'google_recaptcha' => $request->google_recaptcha ?? 'off',
            'recaptcha_key' => $request->recaptcha_key,
            'recaptcha_secret' => $request->recaptcha_secret,
        ];

        foreach ($recaptchaArray as $key => $val) {
            \DB::insert(
                'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                [
                    $val,
                    $key,
                    'recaptcha',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Google Recaptcha settings save successfully.'))->with('tab', 'google_recaptcha_settings');
    }

    // ---------------------- Footer Setting ---------------------------------------------
    public function footerSetting(Request $request)
    {
        if (!Auth::user()->can('manage footer')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $loginUser = Auth::user();
        $pages = Page::where('enabled', 1)->pluck('title', 'id');
        return view('home_pages.footerSetting', compact('loginUser', 'pages'));
    }

    public function footerData(Request $request)
    {
        $settings = $request->all();
        unset($settings['_token']);
        unset($settings['tab']);
        foreach ($settings as $s_key => $s_value) {
            if (in_array($s_key, ['footer_column_1_pages', 'footer_column_2_pages', 'footer_column_3_pages', 'footer_column_4_pages'])) {
                $s_value = json_encode($s_value);
            }
            if (!empty($s_value)) {
                \DB::insert(
                    'INSERT INTO settings (value, name, type, parent_id) VALUES (?, ?, ?, ?) ON CONFLICT (name, type, parent_id) DO UPDATE SET value = EXCLUDED.value',
                    [
                        $s_value,
                        $s_key,
                        'footer',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Footer settings save successfully.'))->with('tab', $request->tab);
    }


    // ---------------------- 2FA Setting --------------------------------
    public function twofaEnable(Request $request)
    {
        $google2fa = new Google2FA();

        // retrieve secret from the session
        $secret = session("2fa_secret");
        $user = Auth::user();
        if ($google2fa->verify($request->input('otp'), $secret)) {
            // store the secret in the user profile
            // this will enable 2FA for this user
            $user->twofa_secret = $secret;
            $user->save();

            // avoid double OTP check
            session(["2fa_checked" => true]);

            return redirect()->back()->with('success', __('2 FA successfully enabled.'));
        }

        throw ValidationException::withMessages(['otp' => 'Incorrect value. Please try again...']);
    }

    public function saveTutorialVideos(Request $request)
    {
        $this->validate($request, [
            'video_links' => 'array',
            'video_links.*' => 'url',
        ]);

        $videoLinks = $request->input('video_links', []);
        $jsonLinks = json_encode($videoLinks);

        // Manual update then insert to avoid ON CONFLICT error
        $updated = \DB::update(
            'UPDATE settings SET value = ? WHERE name = ? AND parent_id = ?',
            [
                $jsonLinks,
                'tutorial_videos',
                parentId(),
            ]
        );

        if (!$updated) {
            \DB::insert(
                'INSERT INTO settings (value, name, parent_id) VALUES (?, ?, ?)',
                [
                    $jsonLinks,
                    'tutorial_videos',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Tutorial video links updated!'))->with('tab', 'tutorial_videos');
    }
}
