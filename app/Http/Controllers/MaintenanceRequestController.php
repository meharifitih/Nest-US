<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceRequestController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage maintenance request')) {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)->where('unit_id', !empty($tenant) ? $tenant->unit : 0)->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->get();
            }
            return view('maintenance_request.index', compact('maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create maintenance request')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $maintainers = User::where('parent_id', parentId())->where('type', 'maintainer')->get()->pluck("name", 'id');
            $maintainers->prepend(__('Select Maintainer'), '');

            $types = Type::where('parent_id', parentId())->where('type', 'issue')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $status = MaintenanceRequest::$status;
            return view('maintenance_request.create', compact('property', 'types', 'maintainers', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create maintenance request')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'issue_type' => 'required',
                    'maintainer_id' => 'required',
                    'request_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $MaintenanceRequest = new MaintenanceRequest();
            $MaintenanceRequest->property_id = $request->property_id;
            $MaintenanceRequest->unit_id = $request->unit_id;
            $MaintenanceRequest->issue_type = $request->issue_type;
            $MaintenanceRequest->maintainer_id = $request->maintainer_id;
            $MaintenanceRequest->status = $request->status;
            $MaintenanceRequest->notes = $request->notes;
            $MaintenanceRequest->request_date = $request->request_date;
            $MaintenanceRequest->parent_id = parentId();
            $MaintenanceRequest->save();

            if (!empty($request->issue_attachment)) {
                $requestFilenameWithExt = $request->file('issue_attachment')->getClientOriginalName();
                $requestFilename = pathinfo($requestFilenameWithExt, PATHINFO_FILENAME);
                $requestExtension = $request->file('issue_attachment')->getClientOriginalExtension();
                $requestFileName = $requestFilename . '_' . time() . '.' . $requestExtension;
                $dir = storage_path('upload/issue_attachment');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('issue_attachment')->storeAs('upload/issue_attachment/', $requestFileName);
                $MaintenanceRequest->issue_attachment = $requestFileName;
                $MaintenanceRequest->save();
            }

            $module = 'maintenance_request_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $notification['user_id'] = \Auth::user()->id;
            $setting = settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $user = User::where('id', $MaintenanceRequest->maintainer_id)->first();
                $notification_responce = MessageReplace($notification, $MaintenanceRequest->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo'] =  $setting['company_logo'];
                $to = $user->email;
                $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
            }

            return redirect()->back()->with('success', __('Maintenance request successfully created.').'</br>'.$errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(MaintenanceRequest $maintenanceRequest)
    {
        return view('maintenance_request.show', compact('maintenanceRequest'));
    }


    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('edit maintenance request')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $maintainers = User::where('parent_id', parentId())->where('type', 'maintainer')->get()->pluck("name", 'id');
            $maintainers->prepend(__('Select Maintainer'), 0);

            $types = Type::where('parent_id', parentId())->where('type', 'issue')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $status = MaintenanceRequest::$status;

            return view('maintenance_request.edit', compact('property', 'types', 'maintainers', 'maintenanceRequest', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('edit maintenance request')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'issue_type' => 'required',
                    'maintainer_id' => 'required',
                    'request_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $maintenanceRequest->property_id = $request->property_id;
            $maintenanceRequest->unit_id = $request->unit_id;
            $maintenanceRequest->issue_type = $request->issue_type;
            $maintenanceRequest->maintainer_id = $request->maintainer_id;
            $maintenanceRequest->status = $request->status;
            $maintenanceRequest->notes = $request->notes;
            $maintenanceRequest->request_date = $request->request_date;
            $maintenanceRequest->save();

            if (!empty($request->issue_attachment)) {
                $requestFilenameWithExt = $request->file('issue_attachment')->getClientOriginalName();
                $requestFilename = pathinfo($requestFilenameWithExt, PATHINFO_FILENAME);
                $requestExtension = $request->file('issue_attachment')->getClientOriginalExtension();
                $requestFileName = $requestFilename . '_' . time() . '.' . $requestExtension;
                $dir = storage_path('upload/issue_attachment');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('issue_attachment')->storeAs('upload/issue_attachment/', $requestFileName);
                $maintenanceRequest->issue_attachment = $requestFileName;
                $maintenanceRequest->save();
            }

            return redirect()->back()->with('success', __('Maintenance request successfully update.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        if (\Auth::user()->can('delete maintenance request')) {
            $maintenanceRequest->delete();
            return redirect()->back()->with('success', __('Maintenance request successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function action($id)
    {
        $maintenanceRequest = MaintenanceRequest::find($id);
        $status = MaintenanceRequest::$status;
        return view('maintenance_request.action', compact('maintenanceRequest', 'status'));
    }

    public function actionData(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'fixed_date' => 'required',
                'status' => 'required',
                'amount' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        $setting = settings();

        $maintenanceRequest = MaintenanceRequest::find($id);
        $maintenanceRequest->fixed_date = $request->fixed_date;
        $maintenanceRequest->status = $request->status;
        $maintenanceRequest->amount = $request->amount;
        $maintenanceRequest->save();

        if (!empty($request->invoice)) {
            $requestFilenameWithExt = $request->file('invoice')->getClientOriginalName();
            $requestFilename = pathinfo($requestFilenameWithExt, PATHINFO_FILENAME);
            $requestExtension = $request->file('invoice')->getClientOriginalExtension();
            $requestFileName = $requestFilename . '_' . time() . '.' . $requestExtension;
            $dir = storage_path('upload/invoice');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $request->file('invoice')->storeAs('upload/invoice/', $requestFileName);
            $maintenanceRequest->invoice = $requestFileName;
            $maintenanceRequest->save();
        }


        $tenants = Tenant::where('property', $maintenanceRequest->property_id)
            ->where('unit', $maintenanceRequest->unit_id)
            ->get();

        if ($tenants->isNotEmpty()) {

            $userIds = [];
            foreach ($tenants as $tenant) {
                $userIds[] = $tenant->user_id;
                $userIds[] = $tenant->parent_id;
            }

            $email = User::whereIn('id', array_unique($userIds))
                ->pluck('email')
                ->toArray();

        } else {
        
            $email = User::where('id', $maintenanceRequest->parent_id)
                ->pluck('email')
                ->toArray();
        }

        $module = 'maintenance_request_complete';
        $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
        $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {
            $user_email = $maintenanceRequest->maintainers->email;
            $notification_responce = MessageReplace($notification, $id);
            $datas['subject'] = $notification_responce['subject'];
            $datas['message'] = $notification_responce['message'];
            $datas['module'] = $module;
            $datas['logo'] =  $setting['company_logo'];
            $to = $email;
            $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
        }

        return redirect()->back()->with('success', __('Maintenance request successfully update.'). '</br>'.$errorMessage);
    }

    public function pendingRequest()
    {
        if (\Auth::user()->can('manage maintenance request')) {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->where('status', 'pending')->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)->where('unit_id', !empty($tenant) ? $tenant->unit : 0)->where('status', 'pending')->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->where('status', 'pending')->get();
            }
            return view('maintenance_request.type', compact('maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function inProgressRequest()
    {
        if (\Auth::user()->can('manage maintenance request')) {
            if (\Auth::user()->type == 'maintainer') {
                $maintenanceRequests = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->where('status', 'in_progress')->get();
            } elseif (\Auth::user()->type == 'tenant') {
                $user = \Auth::user();
                $tenant = $user->tenants;
                $maintenanceRequests = MaintenanceRequest::where('property_id', !empty($tenant) ? $tenant->property : 0)->where('unit_id', !empty($tenant) ? $tenant->unit : 0)->where('status', 'in_progress')->get();
            } else {
                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->where('status', 'in_progress')->get();
            }
            return view('maintenance_request.type', compact('maintenanceRequests'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
