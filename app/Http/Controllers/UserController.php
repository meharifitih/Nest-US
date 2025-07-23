<?php

namespace App\Http\Controllers;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\PackageTransaction;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\PhoneNumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use PhoneNumberFormatter;

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage user')) {
            $query = null;
            if (\Auth::user()->type == 'super admin') {
                $query = User::where('parent_id', parentId())->where('type', 'owner');
            } else {
                $query = User::where('parent_id', '=', parentId())->whereNotIn('type', ['tenant', 'maintainer']);
            }

            // Filtering
            if ($request->filled('name')) {
                $query = $query->where(function($q) use ($request) {
                    $q->where('name', 'like', "%{$request->name}%")
                      ->orWhere('first_name', 'like', "%{$request->name}%")
                      ->orWhere('last_name', 'like', "%{$request->name}%");
                });
            }
            if ($request->filled('email')) {
                $query = $query->where('email', 'like', "%{$request->email}%");
            }
            if ($request->filled('approval_status')) {
                $query = $query->where('approval_status', $request->approval_status);
            }
            if ($request->filled('active_package')) {
                $query = $query->whereHas('subscriptions', function($q) use ($request) {
                    $q->where('title', 'like', "%{$request->active_package}%");
                });
            }
            if ($request->filled('package_due_date')) {
                $query = $query->whereDate('subscription_expire_date', $request->package_due_date);
            }

            $users = $query->paginate(10);
            return view('user.index', compact('users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        $userRoles = Role::where('parent_id', parentId())->whereNotIn('name', ['tenant', 'maintainer'])->get()->pluck('name', 'id');
        return view('user.create', compact('userRoles'));
    }


    public function store(Request $request)
    {
        // Merge first_name and last_name into name if present (for non-super-admin forms)
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $request->merge(['name' => $request->first_name . ' ' . $request->last_name]);
        }
        if (\Auth::user()->can('create user')) {
            if (\Auth::user()->type == 'super admin') {
                try {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'name' => 'required',
                            'email' => 'required|email|unique:users',
                            'password' => 'required|min:6',
                            'phone_number' => 'nullable|regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/|unique:users,phone_number',
                            'fayda_id' => 'required|unique:users',
                        ]
                    );
                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $user = new User();
                    $user->first_name = $request->name;
                    $user->email = $request->email;
                    $user->password = \Hash::make($request->password);
                    $user->phone_number = $request->phone_number;
                    $user->fayda_id = $request->fayda_id;
                    $user->type = 'owner';
                    $user->profile = 'avatar.png';
                    $user->lang = 'english';
                    $user->subscription = 1;
                    $user->parent_id = parentId();
                    $user->email_verified_at = now();
                    
                    // Set approval status based on user type
                    if ($user->type === 'tenant' || $user->type === 'maintainer') {
                        $user->approval_status = 'approved';
                        $user->is_active = 1;
                    } else {
                        $user->approval_status = 'pending';
                        $user->is_active = 0;
                    }
                    
                    $user->save();
                    $userRole = Role::findByName('owner');
                    $user->assignRole($userRole);
                    defaultTenantCreate($user->id);
                    defaultMaintainerCreate($user->id);
                    defaultTemplate($user->id);

                    if (!empty($request->profile)) {
                        $tenantFilenameWithExt = $request->file('profile')->getClientOriginalName();
                        $tenantFilename = pathinfo($tenantFilenameWithExt, PATHINFO_FILENAME);
                        $tenantExtension = $request->file('profile')->getClientOriginalExtension();
                        $tenantFileName = $tenantFilename . '_' . time() . '.' . $tenantExtension;
                        $dir = storage_path('upload/profile');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $request->file('profile')->storeAs('upload/profile/', $tenantFileName);
                        $user->profile = $tenantFileName;
                        $user->save();
                    }


                    $module = 'owner_create';
                    $setting = settings();
                    $errorMessage = '';
                    if (!empty($user)) {
                        $data['subject'] = 'New User Created';
                        $data['module'] = $module;
                        $data['password'] = $request->password;
                        $data['name'] = $request->name;
                        $data['email'] = $request->email;
                        $data['url'] = env('APP_URL');
                        $data['logo'] = $setting['company_logo'];
                        $to = $user->email;
                        $response = commonEmailSend($to, $data);
                        if ($response['status'] == 'error') {
                            $errorMessage=$response['message'];
                        }
                    }

                    // Save email settings for this user
                    $smtpSettings = [
                        ['name' => 'FROM_EMAIL', 'value' => config('mail.from.address')],
                        ['name' => 'FROM_NAME', 'value' => config('mail.from.name')],
                        ['name' => 'SERVER_DRIVER', 'value' => config('mail.default')],
                        ['name' => 'SERVER_HOST', 'value' => config('mail.mailers.smtp.host')],
                        ['name' => 'SERVER_PORT', 'value' => config('mail.mailers.smtp.port')],
                        ['name' => 'SERVER_USERNAME', 'value' => config('mail.mailers.smtp.username')],
                        ['name' => 'SERVER_PASSWORD', 'value' => config('mail.mailers.smtp.password')],
                        ['name' => 'SERVER_ENCRYPTION', 'value' => config('mail.mailers.smtp.encryption')],
                    ];
                    foreach ($smtpSettings as $setting) {
                        \DB::table('settings')->updateOrInsert([
                            'name' => $setting['name'],
                            'type' => 'smtp',
                            'parent_id' => $user->id,
                        ], [
                            'value' => $setting['value'],
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]);
                    }

                    return redirect()->route('users.index')->with('success', __('User successfully created.') . $errorMessage);
                } catch (\Exception $e) {
                    \Log::error('Error creating super admin user: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to create user. Please try again.');
                }
            } else {
                try {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'name' => 'required',
                            'email' => 'required|email|unique:users',
                            'password' => 'required|min:6',
                            'role' => 'required',
                        ]
                    );
                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->back()->with('error', $messages->first());
                    }

                    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
                    if ($pricing_feature_settings == 'on') {
                        $ids = parentId();
                        $authUser = \App\Models\User::find($ids);
                        $totalUser = $authUser->totalUser();
                        $subscription = Subscription::find($authUser->subscription);
                        
                        // Check if subscription exists and has user_limit
                        if ($subscription && $subscription->user_limit != 0 && $totalUser >= $subscription->user_limit) {
                            return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
                        }
                    }
                    $userRole = Role::findById($request->role);
                    $user = new User();
                    $user->first_name = $request->first_name;
                    $user->last_name = $request->last_name;
                    $user->email = $request->email;
                    $user->phone_number = $request->phone_number;
                    $user->fayda_id = $request->fayda_id;
                    $user->password = \Hash::make($request->password);
                    $user->type = $userRole->name;
                    $user->email_verified_at = now();
                    $user->profile = 'avatar.png';
                    $user->lang = 'english';
                    $user->parent_id = parentId();
                    
                    // Set approval status based on user type
                    if ($userRole->name === 'tenant' || $userRole->name === 'maintainer') {
                        $user->approval_status = 'approved';
                        $user->is_active = 1;
                    } else {
                        $user->approval_status = 'pending';
                        $user->is_active = 0;
                    }
                    
                    $user->save();
                    $user->assignRole($userRole);

                    if (!empty($request->profile)) {
                        $tenantFilenameWithExt = $request->file('profile')->getClientOriginalName();
                        $tenantFilename = pathinfo($tenantFilenameWithExt, PATHINFO_FILENAME);
                        $tenantExtension = $request->file('profile')->getClientOriginalExtension();
                        $tenantFileName = $tenantFilename . '_' . time() . '.' . $tenantExtension;
                        $dir = storage_path('upload/profile');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $request->file('profile')->storeAs('upload/profile/', $tenantFileName);
                        $user->profile = $tenantFileName;
                        $user->save();
                    }

                    $module = 'user_create';
                    $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                    $setting = settings();
                    $errorMessage = '';
                    if (!empty($notification) && $notification->enabled_email == 1) {
                        $notification->password = $request->password;
                        $notification_responce = MessageReplace($notification, $user->id);
                        $data['subject'] = $notification_responce['subject'];
                        $data['message'] = $notification_responce['message'];
                        $data['module'] = $module;
                        $data['password'] = $request->password;
                        $data['logo'] = $setting['company_logo'];
                        $to = $user->email;

                        $response = commonEmailSend($to, $data);
                        if ($response['status'] == 'error') {
                            $errorMessage=$response['message'];
                        }
                    }

                    return redirect()->route('users.index')->with('success', __('User successfully created.'). '</br>'.$errorMessage);
                } catch (\Exception $e) {
                    \Log::error('Error creating user: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to create user. Please try again.');
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(User $user)
    {
        if (!\Auth::user()->can('show user')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        } else {
            $settings = settings();
            $transactions = PackageTransaction::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
            $subscriptions = Subscription::get();
            return view('user.show', compact('user', 'transactions','settings', 'subscriptions'));
        }
    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userRoles = Role::where('parent_id', '=', parentId())->whereNotIn('name', ['tenant', 'maintainer'])->get()->pluck('name', 'id');

        return view('user.edit', compact('user', 'userRoles'));
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit user')) {
            if (\Auth::user()->type == 'super admin') {
                $user = User::findOrFail($id);

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                        'phone_number' => 'nullable|regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/|unique:users,phone_number,' . $id,
                        'fayda_id' => 'nullable|unique:users,fayda_id,' . $id,
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }
                $userData = $request->all();
                $userData['first_name'] = $userData['name'];
                $user->fill($userData)->save();
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users,email,' . $id,
                        'role' => 'required',
                        'phone_number' => 'nullable|regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/|unique:users,phone_number,' . $id,
                        'fayda_id' => 'nullable|unique:users,fayda_id,' . $id,
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $userRole = Role::findById($request->role);
                $user = User::findOrFail($id);
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->fayda_id = $request->fayda_id;
                $user->type = $userRole->name;
                $user->save();
                $user->roles()->sync($userRole);
                return redirect()->route('users.index')->with('success', 'User successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function destroy($id)
    {

        if (\Auth::user()->can('delete user')) {
            $user = User::find($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loggedHistory()
    {
        $ids = parentId();
        $authUser = \App\Models\User::find($ids);
        $subscription = \App\Models\Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage logged history') && $subscription && $subscription->enabled_logged_history == 1) {
            $histories = LoggedHistory::where('parent_id', parentId())->get();
            return view('logged_history.index', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryShow($id)
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::find($id);
            return view('logged_history.show', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryDestroy($id)
    {
        if (\Auth::user()->can('delete logged history')) {
            $histories = LoggedHistory::find($id);
            $histories->delete();
            return redirect()->back()->with('success', 'Logged history succefully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function tutorialVideos()
    {
        // Always fetch from super admin (parent_id=1)
        $settings = settingsById(1);
        $tutorialVideos = isset($settings['tutorial_videos']) ? json_decode($settings['tutorial_videos'], true) : [];
        // Normalize and convert YouTube links to embed format
        $tutorialVideos = array_map(function($item) {
            $url = is_array($item) && isset($item['url']) ? $item['url'] : (is_string($item) ? $item : null);
            if ($url) {
                // Convert YouTube watch links to embed links
                if (preg_match('/youtube\\.com\\/watch\\?v=([\\w-]+)/', $url, $matches)) {
                    $url = 'https://www.youtube.com/embed/' . $matches[1];
                }
                return $url;
            }
            return null;
        }, $tutorialVideos);
        $tutorialVideos = array_filter($tutorialVideos);
        return view('owner.tutorial_videos', compact('tutorialVideos'));
    }
}
