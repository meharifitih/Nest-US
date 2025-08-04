<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MaintainerController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage maintainer')) {
            $maintainers = Maintainer::where('parent_id', parentId())->get();
            return view('maintainer.index', compact('maintainers'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create maintainer')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');

            $types = Type::where('parent_id', parentId())->where('type', 'maintainer_type')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            return view('maintainer.create', compact('property', 'types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {

        // dd($request->all());

        if (\Auth::user()->can('create maintainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'phone_number' => ['nullable', 'regex:/^[2-9]\d{2}[-\s]?\d{3}[-\s]?\d{4}$|^[2-9]\d{2}\d{3}\d{4}$/'],
                    'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $userRole = Role::where('parent_id', parentId())->where('name', 'maintainer')->first();
            if (!$userRole) {
                $userRole = new Role();
                $userRole->name = 'maintainer';
                $userRole->guard_name = 'web';
                $userRole->parent_id = parentId();
                $userRole->save();
            }
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = \Hash::make($request->password);
            
            // Format phone number properly
            if (!empty($request->phone_number)) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
                if (strlen($phone) === 10) {
                    $user->phone_number = '+1' . $phone;
                } else {
                    $user->phone_number = $request->phone_number;
                }
            } else {
                $user->phone_number = null;
            }
            
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = parentId();
            $user->save();
            $user->assignRole($userRole);

            if (!empty($request->profile)) {
                $maintainerFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $maintainerFilename = pathinfo($maintainerFilenameWithExt, PATHINFO_FILENAME);
                $maintainerExtension = $request->file('profile')->getClientOriginalExtension();
                $maintainerFileName = $maintainerFilename . '_' . time() . '.' . $maintainerExtension;
                $request->file('profile')->storeAs('upload/profile', $maintainerFileName, 'public');
                $user->profile = $maintainerFileName;
                $user->save();
            }


            // Send email from super admin side before creating maintainer
            $emailSent = false;
            try {
                // Configure email settings for super admin (parent_id = 1)
                emailSettings(1);
                
                // Create simple email data
                $settings = settings();
                $emailData = [
                    'subject' => 'Welcome to ' . ($settings['company_name'] ?? 'Property Management System'),
                    'message' => '
                        <p><strong>Dear ' . $request->first_name . ' ' . $request->last_name . ',</strong></p>
                        <p>Welcome to our Property Management System!</p>
                        <p>Your account has been created successfully.</p>
                        <p><strong>Login Details:</strong></p>
                        <ul>
                            <li><strong>Email:</strong> ' . $request->email . '</li>
                            <li><strong>Password:</strong> ' . $request->password . '</li>
                        </ul>
                        <p><strong>App Link:</strong> <a href="' . env('APP_URL') . '">' . env('APP_URL') . '</a></p>
                        <p>Thank you for joining us!</p>
                    ',
                    'module' => 'maintainer_create',
                    'logo' => $settings['company_logo'] ?? 'logo.png',
                    'parent_id' => 1
                ];
                
                // Send email
                $response = commonEmailSend($request->email, $emailData);
                
                if ($response['status'] == 'success') {
                    $emailSent = true;
                } else {
                    // Email failed - delete the user and return error
                    $user->delete();
                    return redirect()->back()->with('error', 'Failed to send welcome email. Maintainer creation cancelled. Error: ' . $response['message']);
                }
            } catch (\Exception $e) {
                // Email failed - delete the user and return error
                $user->delete();
                return redirect()->back()->with('error', 'Failed to send welcome email. Maintainer creation cancelled. Error: ' . $e->getMessage());
            }
            
            // Only create maintainer if email was sent successfully
            if ($emailSent) {
                $maintainer = new Maintainer();
                $maintainer->user_id = $user->id;
                $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : '';
                $maintainer->type_id = $request->type_id;
                $maintainer->parent_id = parentId();
                $maintainer->save();
                
                return redirect()->back()->with('success', __('Maintainer successfully created and welcome email sent.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Maintainer $maintainer)
    {
        //
    }


    public function edit(Maintainer $maintainer)
    {
        if (\Auth::user()->can('edit maintainer')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');

            $types = Type::where('parent_id', parentId())->where('type', 'maintainer_type')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');
            $user = User::find($maintainer->user_id);
            return view('maintainer.edit', compact('property', 'maintainer', 'types', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Maintainer $maintainer)
    {
        if (\Auth::user()->can('edit maintainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required',
                    'phone_number' => ['nullable', 'regex:/^[2-9]\d{2}[-\s]?\d{3}[-\s]?\d{4}$|^[2-9]\d{2}\d{3}\d{4}$/'],
                    'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $user = User::find($maintainer->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            
            // Format phone number properly
            if (!empty($request->phone_number)) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
                if (strlen($phone) === 10) {
                    $user->phone_number = '+1' . $phone;
                } else {
                    $user->phone_number = $request->phone_number;
                }
            } else {
                $user->phone_number = null;
            }
            
            $user->save();

            if (!empty($request->profile)) {
                $maintainerFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $maintainerFilename = pathinfo($maintainerFilenameWithExt, PATHINFO_FILENAME);
                $maintainerExtension = $request->file('profile')->getClientOriginalExtension();
                $maintainerFileName = $maintainerFilename . '_' . time() . '.' . $maintainerExtension;
                $request->file('profile')->storeAs('upload/profile', $maintainerFileName, 'public');
                $user->profile = $maintainerFileName;
                $user->save();
            }

            $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : 0;
            $maintainer->type_id = $request->type_id;
            $maintainer->save();



            return redirect()->back()->with('success', __('Maintainer successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Maintainer $maintainer)
    {
        if (\Auth::user()->can('delete maintainer')) {
            User::where('id',$maintainer->user_id)->delete();
            $maintainer->delete();
            return redirect()->back()->with('success', __('Maintainer successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
