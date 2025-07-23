<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Property;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantDocument;
use App\Models\User;
use App\Traits\PhoneNumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Notifications\PasswordChangeNotification;

class TenantController extends Controller
{
    use PhoneNumberFormatter;

    public function index(Request $request)
    {
        if (!\Auth::check() || parentId() === null) {
            return redirect()->route('login');
        }
        
        if (\Auth::user()->can('manage tenant')) {
            $query = Tenant::where('parent_id', parentId())
                ->with(['user', 'properties', 'units']);

            // Filter by property
            if ($request->property_id) {
                $query->where('property', $request->property_id);
            }

            // Filter by unit
            if ($request->unit_id) {
                $query->where('unit', $request->unit_id);
            }

            // Filter by lease status
            if ($request->lease_status) {
                $today = now()->format('Y-m-d');
                if ($request->lease_status === 'active') {
                    $query->where('lease_end_date', '>=', $today);
                } elseif ($request->lease_status === 'expired') {
                    $query->where('lease_end_date', '<', $today);
                }
            }

            // Search by name
            if ($request->name) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('first_name', 'like', "%{$request->name}%")
                      ->orWhere('last_name', 'like', "%{$request->name}%");
                });
            }
            // Search by email
            if ($request->email) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('email', 'like', "%{$request->email}%");
                });
            }
            // Search by phone
            if ($request->phone) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('phone_number', 'like', "%{$request->phone}%");
                });
            }

            $tenants = $query->get();

            // Get properties and units for filter dropdowns
            $properties = \App\Models\Property::where('parent_id', parentId())->get();
            $units = \App\Models\PropertyUnit::where('parent_id', parentId())->get();

            return view('tenant.index', compact('tenants', 'properties', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create tenant')) {
            $properties = \App\Models\Property::where('parent_id', parentId())->get();
            return view('tenant.create', compact('properties'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create tenant')) {
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'email' => 'required|email|unique:users',
                        'phone_number' => 'nullable|regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/',
                        'property' => 'required',
                        'unit' => 'required',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return response()->json([
                        'status' => 'error',
                        'msg' => $messages->first(),
                        'old_input' => $request->all()
                    ]);
                }

                // Check if unit is already occupied by an active tenant
                $existingTenant = Tenant::where('unit', $request->unit)
                    ->where('lease_end_date', '>=', now()->format('Y-m-d'))
                    ->first();

                if ($existingTenant) {
                    return response()->json([
                        'status' => 'error',
                        'msg' => 'This unit is already occupied by an active tenant. Please select a different unit.',
                        'old_input' => $request->all()
                    ]);
                }

                // Generate a random password
                $password = \Illuminate\Support\Str::random(8);

                $user = new User();
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->email = $request->email;
                $user->password = Hash::make($password);
                $user->phone_number = $request->phone_number;
                $user->type = 'tenant';
                $user->email_verified_at = now();
                $user->profile = 'avatar.png';
                $user->lang = 'english';
                $user->parent_id = parentId();
                $user->save();

                $userRole = Role::where('name', 'tenant')->first();
                $user->assignRole($userRole);

                // Send password notification
                $user->notify(new PasswordChangeNotification($password));

                $tenant = new Tenant();
                $tenant->user_id = $user->id;
                $tenant->family_member = $request->family_member;
                $tenant->country = $request->country;
                $tenant->state = $request->state;
                $tenant->city = $request->city;
                $tenant->zip_code = $request->zip_code;
                $tenant->address = $request->address;
                $tenant->location = $request->location;
                $tenant->property = $request->property;
                $tenant->unit = $request->unit;
                $tenant->lease_start_date = $request->lease_start_date;
                $tenant->lease_end_date = $request->lease_end_date;
                $tenant->parent_id = parentId();
                $tenant->save();
            } catch (\Exception $e) {
                \Log::error('Error creating tenant: ' . $e->getMessage());
                
                // Check for specific database errors
                if (strpos($e->getMessage(), 'SQLSTATE[23505]') !== false) {
                    if (strpos($e->getMessage(), 'users_email_unique') !== false) {
                        return response()->json([
                            'status' => 'error',
                            'msg' => 'This email address is already registered. Please use a different email.',
                            'old_input' => $request->all()
                        ]);
                    } elseif (strpos($e->getMessage(), 'users_phone_number_unique') !== false) {
                        return response()->json([
                            'status' => 'error',
                            'msg' => 'This phone number is already registered. Please use a different phone number.',
                            'old_input' => $request->all()
                        ]);
                    }
                }
                
                return response()->json([
                    'status' => 'error',
                    'msg' => 'Failed to create tenant. Please try again.',
                    'old_input' => $request->all()
                ]);
            }

            if (!empty($request->tenant_images)) {
                foreach ($request->tenant_images as $file) {
                    $tenantFilenameWithExt = $file->getClientOriginalName();
                    $tenantFilename = pathinfo($tenantFilenameWithExt, PATHINFO_FILENAME);
                    $tenantExtension = $file->getClientOriginalExtension();
                    $tenantFileName = $tenantFilename . '_' . time() . '.' . $tenantExtension;
                    $file->storeAs('upload/tenant', $tenantFileName, 'public');

                    $tenantImage = new TenantDocument();
                    $tenantImage->property_id = $request->property;
                    $tenantImage->tenant_id = $tenant->id;
                    $tenantImage->document = $tenantFileName;
                    $tenantImage->parent_id = parentId();
                    $tenantImage->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'msg' => __('Tenant successfully created.'),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Tenant $tenant)
    {
        if (\Auth::user()->can('show tenant')) {
            $tenant->load(['user', 'units.property']);
            return view('tenant.show', compact('tenant'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit(Tenant $tenant)
    {
        if (\Auth::user()->can('edit tenant')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), 0);

            $user = User::find($tenant->user_id);
            return view('tenant.edit', compact('property', 'tenant', 'user'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Tenant $tenant)
    {
        if (\Auth::user()->can('edit tenant')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users,email,' . $tenant->user_id,
                    'phone_number' => 'nullable|regex:/^(\+1|1)?[2-9]\d{2}[2-9]\d{2}\d{4}$|^(\+1\s?)?(\([2-9]\d{2}\)|[2-9]\d{2})[-.\s]?[2-9]\d{2}[-.\s]?\d{4}$/',
                    'family_member' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'zip_code' => 'required',
                    'address' => 'required',
                    'location' => 'required',
                    'property' => 'required',
                    'unit' => 'required',
                    'lease_start_date' => 'required',
                    'lease_end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return response()->json([
                    'status' => 'error',
                    'msg' => $messages->first(),
                ]);
            }

            // Check if unit is already occupied by another active tenant (excluding current tenant)
            $existingTenant = Tenant::where('unit', $request->unit)
                ->where('id', '!=', $tenant->id)
                ->where('lease_end_date', '>=', now()->format('Y-m-d'))
                ->first();

            if ($existingTenant) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'This unit is already occupied by an active tenant. Please select a different unit.',
                ]);
            }

            $user = User::find($tenant->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->save();

            if ($request->profile != '') {
                $tenantFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $tenantFilename = pathinfo($tenantFilenameWithExt, PATHINFO_FILENAME);
                $tenantExtension = $request->file('profile')->getClientOriginalExtension();
                $tenantFileName = $tenantFilename . '_' . time() . '.' . $tenantExtension;
                $request->file('profile')->storeAs('upload/profile', $tenantFileName, 'public');
                $user->profile = $tenantFileName;
                $user->save();
            }

            $tenant->family_member = $request->family_member;
            $tenant->country = $request->country;
            $tenant->state = $request->state;
            $tenant->city = $request->city;
            $tenant->zip_code = $request->zip_code;
            $tenant->address = $request->address;
            $tenant->location = $request->location;
            $tenant->property = $request->property;
            $tenant->unit = $request->unit;
            $tenant->lease_start_date = $request->lease_start_date;
            $tenant->lease_end_date = $request->lease_end_date;
            $tenant->save();



            if (!empty($request->tenant_images)) {
                foreach ($request->tenant_images as $file) {
                    $tenantFilenameWithExt = $file->getClientOriginalName();
                    $tenantFilename = pathinfo($tenantFilenameWithExt, PATHINFO_FILENAME);
                    $tenantExtension = $file->getClientOriginalExtension();
                    $tenantFileName = $tenantFilename . '_' . time() . '.' . $tenantExtension;
                    $file->storeAs('upload/tenant', $tenantFileName, 'public');

                    $tenantImage = new TenantDocument();
                    $tenantImage->property_id = $request->property;
                    $tenantImage->tenant_id = $tenant->id;
                    $tenantImage->document = $tenantFileName;
                    $tenantImage->parent_id = parentId();
                    $tenantImage->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'msg' => __('Tenant successfully updated.'),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Tenant $tenant)
    {
        if (\Auth::user()->can('delete tenant')) {
            User::where('id',$tenant->user_id)->delete();
            $tenant->delete();
            return redirect()->back()->with('success', 'Tenant successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
