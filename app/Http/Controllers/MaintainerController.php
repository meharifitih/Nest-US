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
                    'phone_number' => 'required',
                    'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $userRole = Role::where('parent_id', parentId())->where('name', 'maintainer')->first();
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = \Hash::make($request->password);
            $user->phone_number = $request->phone_number;
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
                $dir = storage_path('upload/profile');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('profile')->storeAs('upload/profile/', $maintainerFileName);
                $user->profile = $maintainerFileName;
                $user->save();
            }


            $maintainer = new Maintainer();
            $maintainer->user_id = $user->id;
            $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : '';
            $maintainer->type_id = $request->type_id;
            $maintainer->parent_id = parentId();

            // dd($maintainer);
            $maintainer->save();

            $module = 'maintainer_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $notification->password=$request->password;
            $setting=settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $user->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo']=  $setting['company_logo'];
                $to = $user->email;
                $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
            }


            return redirect()->back()->with('success', __('Maintainer successfully created.'). '</br>' . $errorMessage);
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
                    'phone_number' => 'required',
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
            $user->phone_number = $request->phone_number;
            $user->save();

            if (!empty($request->profile)) {
                $maintainerFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $maintainerFilename = pathinfo($maintainerFilenameWithExt, PATHINFO_FILENAME);
                $maintainerExtension = $request->file('profile')->getClientOriginalExtension();
                $maintainerFileName = $maintainerFilename . '_' . time() . '.' . $maintainerExtension;
                $dir = storage_path('upload/profile');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('profile')->storeAs('upload/profile/', $maintainerFileName);
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
