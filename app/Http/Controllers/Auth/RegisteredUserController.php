<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Traits\PhoneNumberFormatter;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    use PhoneNumberFormatter;

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = \App\Models\User::find(1);
        $lang = $user ? $user->lang : 'english';
        \App::setLocale($lang);
        $registerPage = getSettingsValByName('register_page');

        if($registerPage == 'on'){
            $menu = Page::where('slug', 'terms_conditions')->first();
            return view('auth.register', compact('menu'));
        } else {
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
            'password' => [
                'required', 
                'confirmed', 
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'phone_number' => ['required', 'string', 'regex:/^[2-9]\d{2}[-\s]?\d{3}[-\s]?\d{4}$|^[2-9]\d{2}\d{3}\d{4}$/'],
            'type' => ['required', 'string', 'in:tenant,maintainer,owner,super admin'],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
            'password.min' => 'Password must be at least 8 characters long.',
            'phone_number.regex' => 'Enter a valid US phone number.'
        ]);

        $parentId = parentId();
        if (is_null($parentId)) {
            $parentId = 1; // fallback to super admin or system user if needed
        }

        try {
            // Format phone number properly
            $phoneNumber = null;
            if (!empty($request->phone_number)) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
                if (strlen($phone) === 10) {
                    $phoneNumber = '+1' . $phone;
                } else {
                    $phoneNumber = $request->phone_number;
                }
            }
            
            $user = User::create([
                'first_name' => $request->name,
                'last_name' => '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => $request->type,
                'phone_number' => $phoneNumber,
                'profile' => 'avatar.png',
                'lang' => 'english',
                'subscription' => null,
                'subscription_expire_date' => null,
                'parent_id' => $parentId,
                'is_active' => in_array($request->type, ['tenant', 'maintainer']) ? 1 : 0,
                'approval_status' => in_array($request->type, ['tenant', 'maintainer']) ? 'approved' : 'pending',
            ]);
        } catch (\Exception $e) {
            \Log::error('User registration error: ' . $e->getMessage());
            
            // Check for specific database errors
            if (strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
                if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                    return redirect()->back()->with('error', 'This email address is already registered. Please use a different email or try logging in.');
                } elseif (strpos($e->getMessage(), 'users_phone_number_unique') !== false) {
                    return redirect()->back()->with('error', 'This phone number is already registered. Please use a different phone number.');
                }
            }
            
            return redirect()->back()->with('error', 'Registration failed. Please try again.');
        }

        // Assign role only if it exists
        $role = \Spatie\Permission\Models\Role::where('name', $request->type)->first();
        if ($user && $role) {
            $user->assignRole($role);
        }

        // Send welcome email (no password)
        if (!empty($user)) {
            if ($request->type === 'owner') {
                $module = 'owner_create';
                $setting = settings();
                $data['subject'] = 'Welcome to Nest!';
                $data['module'] = $module;
                $data['name'] = $user->first_name;
                $data['email'] = $user->email;
                $data['url'] = env('APP_URL');
                $data['logo'] = isset($setting['company_logo']) ? $setting['company_logo'] : '';
                $data['parent_id'] = $user->parent_id ?? 1;
                $data['password'] = $request->password;
                $to = $user->email;
                commonEmailSend($to, $data);
            } else {
                $module = 'welcome';
                $setting = settings();
                $data['subject'] = 'Welcome to Nest!';
                $data['module'] = $module;
                $data['name'] = $user->first_name;
                $data['email'] = $user->email;
                $data['url'] = env('APP_URL');
                $data['logo'] = isset($setting['company_logo']) ? $setting['company_logo'] : '';
                $data['parent_id'] = $user->parent_id ?? 1;
                $to = $user->email;
                commonEmailSend($to, $data);
            }
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

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        Auth::login($user);
        return redirect()->route('subscriptions.index')
            ->with('success', __('Registration successful. Please select a subscription package and upload payment proof for approval.'));
    }
}
