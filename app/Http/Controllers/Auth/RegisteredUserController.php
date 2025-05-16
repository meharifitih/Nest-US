<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user=\App\Models\User::find(1);
        \App::setLocale($user->lang);
        $registerPage=getSettingsValByName('register_page');

        if($registerPage =='on'){
            $menu = Page::where('slug', 'terms_conditions')->first();
            return view('auth.register',compact('menu'));
        }else{
            return view('auth.login');
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $google_recaptcha = getSettingsValByName('google_recaptcha');
        if($google_recaptcha == 'on')
        {
            $validation['g-recaptcha-response'] = 'required|captcha';
        }else{
            $validation = [];
        }
        $this->validate($request, $validation);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number' => ['required', 'string', 'regex:/^[79]\d{8}$/'],
            'fayda_id' => ['required', 'string', 'max:255', 'unique:users'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'parent_id' => parentId(),
            'approval_status' => in_array($request->type, ['tenant', 'maintainer']) ? 'approved' : 'pending',
            'is_active' => in_array($request->type, ['tenant', 'maintainer']) ? 1 : 0,
        ]);

        $user->assignRole($request->type);

        if (in_array($request->type, ['tenant', 'maintainer'])) {
            Auth::login($user);
            return redirect()->route('dashboard')
                ->with('success', __('Registration successful. You can now login.'));
        }

        // For regular users, send verification email if enabled
        $owner_email_verification = getSettingsValByName('owner_email_verification');
        if ($owner_email_verification == 'on') {
            $token = sha1($user->email);
            $url = route('email-verification', $token);

            $user->email_verification_token = $token;
            $user->save();

            $data = [
                'module' => 'email_verification',
                'subject' => 'Email Verification',
                'email' => $user->email,
                'name' => $user->name,
                'url' => $url,
            ];
            $to = $user->email;
            $response = sendEmailVerification($to, $data);
            if ($response['status'] == 'success') {
                return redirect()->route('login')->with('error', __('We have sent an account verification email to your registered email inbox. Please check your email and follow the instructions to verify your account.'));
            } else {
                $user->delete();
                return redirect()->back()->with('error', $response['message']);
            }
        }

        // Send welcome email
        $module = 'owner_create';
        $setting = settings();
        if (!empty($user)) {
            $data['subject'] = 'New User Created';
            $data['module'] = $module;
            $data['password'] = $request->password;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['url'] = env('APP_URL');
            $data['logo'] = $setting['company_logo'];
            $to = $user->email;
            commonEmailSend($to, $data);
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        // Login the user and redirect to subscription selection
        Auth::login($user);
        return redirect()->route('subscriptions.index')
            ->with('success', __('Registration successful. Please select a subscription package and upload payment proof for approval.'));
    }
}
