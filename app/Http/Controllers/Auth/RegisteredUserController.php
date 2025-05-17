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
        $validation = $google_recaptcha == 'on'
            ? ['g-recaptcha-response' => 'required|captcha']
            : [];
        $this->validate($request, $validation);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number' => ['required', 'string', 'regex:/^[79]\d{8}$/'],
            'fayda_id' => ['required', 'string', 'max:255', 'unique:users'],
            'type' => ['required', 'string', 'in:tenant,maintainer,owner,super admin'],
        ]);

        $parentId = parentId();
        if (is_null($parentId)) {
            $parentId = 1; // fallback to super admin or system user if needed
        }

        try {
            $user = User::create([
                'first_name' => $request->name,
                'last_name' => '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => $request->type,
                'phone_number' => $request->phone_number,
                'fayda_id' => $request->fayda_id,
                'profile' => 'avatar.png',
                'lang' => 'english',
                'subscription' => null,
                'subscription_expire_date' => null,
                'parent_id' => $parentId,
                'is_active' => in_array($request->type, ['tenant', 'maintainer']) ? 1 : 0,
                'approval_status' => in_array($request->type, ['tenant', 'maintainer']) ? 'approved' : 'pending',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User registration failed: ' . $e->getMessage());
        }

        // Assign role only if it exists
        $role = \Spatie\Permission\Models\Role::where('name', $request->type)->first();
        if ($user && $role) {
            $user->assignRole($role);
        }

        if (!$user) {
            return redirect()->back()->with('error', 'User registration failed.');
        }

        if (in_array($request->type, ['tenant', 'maintainer'])) {
            Auth::login($user);
            return redirect()->route('dashboard')
                ->with('success', __('Registration successful. You can now login.'));
        }

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
                'name' => $user->first_name,
                'url' => $url,
            ];
            $to = $user->email;
            $response = sendEmailVerification($to, $data);
            if (isset($response['status']) && $response['status'] == 'success') {
                return redirect()->route('login')->with('error', __('We have sent an account verification email to your registered email inbox. Please check your email and follow the instructions to verify your account.'));
            } else {
                $user->delete();
                $msg = isset($response['message']) ? $response['message'] : 'Unknown error sending verification email.';
                return redirect()->back()->with('error', $msg);
            }
        }

        // Send welcome email (no password)
        $module = 'owner_create';
        $setting = settings();
        if (!empty($user)) {
            $data['subject'] = 'New User Created';
            $data['module'] = $module;
            $data['name'] = $user->first_name;
            $data['email'] = $user->email;
            $data['url'] = env('APP_URL');
            $data['logo'] = isset($setting['company_logo']) ? $setting['company_logo'] : '';
            $to = $user->email;
            commonEmailSend($to, $data);
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        Auth::login($user);
        return redirect()->route('subscriptions.index')
            ->with('success', __('Registration successful. Please select a subscription package and upload payment proof for approval.'));
    }
}
