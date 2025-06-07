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

    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('parent_id', parentId())->where('type', 'owner')->get();
                return view('user.index', compact('users'));
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['tenant', 'maintainer'])->get();
                return view('user.index', compact('users'));
            }
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
        if (\Auth::user()->can('create user')) {
            if (\Auth::user()->type == 'super admin') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:6',
                        'phone_number' => 'required|regex:/^[79][0-9]{8}$/|unique:users,phone_number',
                        'fayda_id' => 'required|unique:users',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $phone = preg_replace('/\D/', '', $request->phone_number);
                if (strlen($phone) === 9 && ($phone[0] === '9' || $phone[0] === '7')) {
                    $phone = '+251' . $phone;
                } else {
                    return redirect()->back()->with('error', 'Phone number must be 9 digits starting with 9 or 7');
                }

                $user = new User();
                $user->first_name = $request->name;
                $user->email = $request->email;
                $user->password = \Hash::make($request->password);
                $user->phone_number = $phone;
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

                return redirect()->route('users.index')->with('success', __('User successfully created.') . $errorMessage);
            } else {

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
                    if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
                        return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
                    }
                }
                $userRole = Role::findById($request->role);
                $user = new User();
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $phone = preg_replace('/\D/', '', $request->phone_number);
                if (strlen($phone) === 9 && ($phone[0] === '9' || $phone[0] === '7')) {
                    $phone = '+251' . $phone;
                } else {
                    return redirect()->back()->with('error', 'Phone number must be 9 digits starting with 9 or 7');
                }
                $user->fayda_id = $request->fayda_id;
                $user->password = \Hash::make($request->password);
                $user->type = $userRole->name;
                $user->email_verified_at = now();
                $user->profile = 'avatar.png';
                $user->lang = 'english';
                $user->parent_id = parentId();
                $user->phone_number = $phone;
                
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
                $notification->password=$request->password;
                $setting = settings();
                $errorMessage = '';
                if (!empty($notification) && $notification->enabled_email == 1) {
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
                        'phone_number' => 'required|regex:/^[79][0-9]{8}$/|unique:users,phone_number,' . $id,
                        'fayda_id' => 'required|unique:users,fayda_id,' . $id,
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
                        'phone_number' => 'required|regex:/^[79][0-9]{8}$/|unique:users,phone_number,' . $id,
                        'fayda_id' => 'required|unique:users,fayda_id,' . $id,
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
                $phone = preg_replace('/\D/', '', $request->phone_number);
                if (strlen($phone) === 9 && ($phone[0] === '9' || $phone[0] === '7')) {
                    $phone = '+251' . $phone;
                } else {
                    return redirect()->back()->with('error', 'Phone number must be 9 digits starting with 9 or 7');
                }
                $user->fayda_id = $request->fayda_id;
                $user->type = $userRole->name;
                $user->phone_number = $phone;
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

        if (\Auth::user()->can('manage logged history') && $subscription->enabled_logged_history == 1) {
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
