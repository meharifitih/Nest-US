<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TenantExcelUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TenantsImport;

class PropertyController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage property')) {
            $properties = Property::where('parent_id', parentId())->where('is_active', 1)->get();
            return view('property.index', compact('properties'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function create()
    {

        if (\Auth::user()->can('create property')) {
            $types = Property::$Type;
            $unitTypes = PropertyUnit::$Types;
            $rentTypes = PropertyUnit::$rentTypes;

            return view('property.create', compact('types', 'rentTypes', 'unitTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create property')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'required',
                    'type' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'zip_code' => 'required',
                    'address' => 'required',
                    'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status' => 'error',
                    'msg' => $messages->first(),

                ]);
            }

            $ids = parentId();
            $authUser = \App\Models\User::find($ids);
            $totalProperty = $authUser->totalProperty();
            $subscription = Subscription::find($authUser->subscription);
            if ($subscription && !$subscription->checkPropertyLimit($totalProperty + 1)) {
                return response()->json([
                    'status' => 'error',
                    'msg' => __('Your property limit is over, please upgrade your subscription.'),
                    'id' => 0,
                ]);
            }
            $property = new Property();
            $property->name = $request->name;
            $property->description = $request->description;
            $property->type = $request->type;
            $property->country = $request->country;
            $property->state = $request->state;
            $property->city = $request->city;
            $property->zip_code = $request->zip_code;
            $property->address = $request->address;
            $property->parent_id = parentId();
            $property->save();

            if ($request->thumbnail != 'undefined') {
                $thumbnailFilenameWithExt = $request->file('thumbnail')->getClientOriginalName();
                $thumbnailFilename = pathinfo($thumbnailFilenameWithExt, PATHINFO_FILENAME);
                $thumbnailExtension = $request->file('thumbnail')->getClientOriginalExtension();
                $thumbnailFileName = $thumbnailFilename . '_' . time() . '.' . $thumbnailExtension;
                $dir = storage_path('app/public/upload/thumbnail');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('thumbnail')->storeAs('public/upload/thumbnail/', $thumbnailFileName);
                $thumbnail = new PropertyImage();
                $thumbnail->property_id = $property->id;
                $thumbnail->image = $thumbnailFileName;
                $thumbnail->type = 'thumbnail';
                $thumbnail->save();
            }

            if (!empty($request->property_images)) {
                foreach ($request->property_images as $file) {
                    $propertyFilenameWithExt = $file->getClientOriginalName();
                    $propertyFilename = pathinfo($propertyFilenameWithExt, PATHINFO_FILENAME);
                    $propertyExtension = $file->getClientOriginalExtension();
                    $propertyFileName = $propertyFilename . '_' . time() . '.' . $propertyExtension;
                    $dir = storage_path('app/public/upload/property');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $file->storeAs('public/upload/property/', $propertyFileName);

                    $propertyImage = new PropertyImage();
                    $propertyImage->property_id = $property->id;
                    $propertyImage->image = $propertyFileName;
                    $propertyImage->type = 'extra';
                    $propertyImage->save();
                }
            }

            if (!empty($request->name) && !empty($request->bedroom) && !empty($request->kitchen)) {
                $totalUnits = PropertyUnit::where('parent_id', parentId())->count();
                if ($subscription && !$subscription->checkUnitLimit($totalUnits + 1)) {
                    return response()->json([
                        'status' => 'error',
                        'msg' => __('You have reached the maximum unit limit for your subscription. Please upgrade your package.'),
                        'id' => 0,
                    ]);
                }
                $unit = new PropertyUnit();
                $unit->name = $request->name;
                $unit->bedroom = $request->bedroom;
                $unit->kitchen = $request->kitchen;
                $unit->baths = !empty($request->baths) ? $request->baths : 0;
                $unit->rent = !empty($request->rent) ? $request->rent : 0;
                $unit->rent_type = $request->rent_type;
                if ($request->rent_type == 'custom') {
                    $unit->start_date = $request->start_date;
                    $unit->end_date = $request->end_date;
                    $unit->payment_due_date = $request->payment_due_date;
                } else {
                    $unit->rent_duration = $request->rent_duration;
                }

                $unit->deposit_type = !empty($request->deposit_type) ? $request->deposit_type : null;
                $unit->deposit_amount = !empty($request->deposit_amount) ? $request->deposit_amount : 0;
                $unit->late_fee_type = !empty($request->late_fee_type) ? $request->late_fee_type : null;
                $unit->late_fee_amount = !empty($request->late_fee_amount) ? $request->late_fee_amount : 0;
                $unit->incident_receipt_amount = !empty($request->incident_receipt_amount) ? $request->incident_receipt_amount : 0;
                $unit->notes = $request->notes;
                $unit->property_id = $property->id;
                $unit->parent_id = parentId();

                $unit->save();
            }


            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully created.'),
                'id' => $property->id,
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Property $property)
    {
        if (\Auth::user()->can('show property')) {
            $units = PropertyUnit::where('property_id', $property->id)->orderBy('id', 'desc')->get();
            return view('property.show', compact('property', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit(Property $property)
    {
        if (\Auth::user()->can('edit property')) {
            $types = Property::$Type;
            return view('property.edit', compact('types', 'property'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }



    public function update(Request $request, Property $property)
    {

        if (\Auth::user()->can('edit property')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'description' => 'required',
                    'type' => 'required',
                    'country' => 'required',
                    'state' => 'required',
                    'city' => 'required',
                    'zip_code' => 'required',
                    'address' => 'required',

                ]

            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json([
                    'status' => 'error',
                    'msg' => $messages->first(),

                ]);
            }



            $property->name = $request->name;
            $property->description = $request->description;
            $property->type = $request->type;
            $property->country = $request->country;
            $property->state = $request->state;
            $property->city = $request->city;
            $property->zip_code = $request->zip_code;
            $property->address = $request->address;
            $property->save();

            if (!empty($request->thumbnail)) {
                if (!empty($property->thumbnail) && isset($property->thumbnail->image)) {
                    $image_path = "storage/upload/thumbnail/" . $property->thumbnail->image;
                    if (\File::exists($image_path)) {
                        \File::delete($image_path);
                    }
                }
                $thumbnailFilenameWithExt = $request->file('thumbnail')->getClientOriginalName();
                $thumbnailFilename = pathinfo($thumbnailFilenameWithExt, PATHINFO_FILENAME);
                $thumbnailExtension = $request->file('thumbnail')->getClientOriginalExtension();
                $thumbnailFileName = $thumbnailFilename . '_' . time() . '.' . $thumbnailExtension;
                $dir = storage_path('app/public/upload/thumbnail');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('thumbnail')->storeAs('public/upload/thumbnail/', $thumbnailFileName);
                $thumbnail = PropertyImage::where('property_id', $property->id)->where('type', 'thumbnail')->first();
                if ($thumbnail) {
                    $thumbnail->image = $thumbnailFileName;
                    $thumbnail->save();
                } else {
                    $thumbnail = new PropertyImage();
                    $thumbnail->property_id = $property->id;
                    $thumbnail->image = $thumbnailFileName;
                    $thumbnail->type = 'thumbnail';
                    $thumbnail->save();
                }
            }

            if (!empty($request->property_images)) {
                foreach ($request->property_images as $file) {
                    $propertyFilenameWithExt = $file->getClientOriginalName();
                    $propertyFilename = pathinfo($propertyFilenameWithExt, PATHINFO_FILENAME);
                    $propertyExtension = $file->getClientOriginalExtension();
                    $propertyFileName = $propertyFilename . '_' . time() . '.' . $propertyExtension;
                    $dir = storage_path('app/public/upload/property');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $file->storeAs('public/upload/property/', $propertyFileName);

                    $propertyImage = new PropertyImage();
                    $propertyImage->property_id = $property->id;
                    $propertyImage->image = $propertyFileName;
                    $propertyImage->type = 'extra';
                    $propertyImage->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully updated.'),
                'id' => $property->id,
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Property $property)
    {
        if (\Auth::user()->can('delete property')) {

            $property->delete();
            return redirect()->back()->with('success', 'Property successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function units()
    {
        if (\Auth::user()->can('manage unit')) {
            $units = PropertyUnit::where('parent_id', parentId())->get();
            return view('unit.index', compact('units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitCreate($property_id)
    {

        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;
        return view('unit.create', compact('types', 'property_id', 'rentTypes'));
    }



    public function unitStore(Request $request, $property_id)
    {
        if (\Auth::user()->can('create unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Check subscription unit limit
            $user = \Auth::user();
            $subscription = Subscription::find($user->subscription);
            if ($subscription) {
                $totalUnits = PropertyUnit::where('parent_id', parentId())->count();
                if (!$subscription->checkUnitLimit($totalUnits + 1)) {
                    return redirect()->back()->with('error', __('You have reached the maximum unit limit for your subscription. Please upgrade your package.'));
                }
            }

            $unit = new PropertyUnit();
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->property_id = $property_id;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitdirectCreate()
    {
        $name = Property::all('name', 'id')->pluck('name', 'id');
        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;
        return view('unit.directcreate', compact('types', 'rentTypes', 'name'));
    }

    public function unitdirectStore(Request $request)
    {
        if (\Auth::user()->can('create unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'property_id' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user = \Auth::user();
            $subscription = Subscription::find($user->subscription);
            if ($subscription) {
                $totalUnits = PropertyUnit::where('parent_id', parentId())->count();
                if (!$subscription->checkUnitLimit($totalUnits + 1)) {
                    return redirect()->back()->with('error', __('You have reached the maximum unit limit for your subscription. Please upgrade your package.'));
                }
            }
            $unit = new PropertyUnit();
            $unit->name = $request->name;
            $unit->property_id = $request->property_id;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function unitEdit($property_id, $unit_id)
    {
        $unit = PropertyUnit::find($unit_id);
        $types = PropertyUnit::$Types;
        $rentTypes = PropertyUnit::$rentTypes;
        return view('unit.edit', compact('types', 'property_id', 'rentTypes', 'unit'));
    }

    public function unitUpdate(Request $request, $property_id, $unit_id)
    {
        if (\Auth::user()->can('edit unit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'bedroom' => 'required',
                    'kitchen' => 'required',
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'deposit_type' => 'required',
                    'deposit_amount' => 'required',
                    'late_fee_type' => 'required',
                    'late_fee_amount' => 'required',
                    'incident_receipt_amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $unit = PropertyUnit::find($unit_id);
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->kitchen = $request->kitchen;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            if ($request->rent_type == 'custom') {
                $unit->start_date = $request->start_date;
                $unit->end_date = $request->end_date;
                $unit->payment_due_date = $request->payment_due_date;
            } else {
                $unit->rent_duration = $request->rent_duration;
            }

            $unit->deposit_type = $request->deposit_type;
            $unit->deposit_amount = $request->deposit_amount;
            $unit->late_fee_type = $request->late_fee_type;
            $unit->late_fee_amount = $request->late_fee_amount;
            $unit->incident_receipt_amount = $request->incident_receipt_amount;
            $unit->notes = $request->notes;
            $unit->save();
            return redirect()->back()->with('success', __('Unit successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitDestroy($property_id, $unit_id)
    {
        if (\Auth::user()->can('delete unit')) {
            $unit = PropertyUnit::find($unit_id);
            $unit->delete();
            return redirect()->back()->with('success', 'Unit successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function getPropertyUnit($property_id)
    {
        $units = PropertyUnit::where('property_id', $property_id)->get()->pluck('name', 'id');
        return response()->json($units);
    }

    public function uploadTenantExcel(Request $request, Property $property)
    {
        if (\Auth::user()->can('edit property')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048'
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'msg' => $validator->errors()->first()
                ]);
            }

            try {
                $file = $request->file('excel_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('upload/tenant_excel', $fileName);

                $excelUpload = new TenantExcelUpload();
                $excelUpload->property_id = $property->id;
                $excelUpload->file_name = $fileName;
                $excelUpload->original_name = $file->getClientOriginalName();
                $excelUpload->parent_id = parentId();
                $excelUpload->save();

                Excel::import(new TenantsImport($property->id), $file);

                $excelUpload->status = 'completed';
                $excelUpload->save();

                return response()->json([
                    'status' => 'success',
                    'msg' => __('Tenant information successfully imported.')
                ]);
            } catch (\Exception $e) {
                if (isset($excelUpload)) {
                    $excelUpload->status = 'failed';
                    $excelUpload->error_log = $e->getMessage();
                    $excelUpload->save();
                }

                return response()->json([
                    'status' => 'error',
                    'msg' => __('Error importing tenant information: ') . $e->getMessage()
                ]);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function getTenantExcelUploads(Property $property)
    {
        if (\Auth::user()->can('show property')) {
            $uploads = TenantExcelUpload::where('property_id', $property->id)
                ->where('parent_id', parentId())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'uploads' => $uploads
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function downloadTenantExcelTemplate()
    {
        if (\Auth::user()->can('edit property')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $data = [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@example.com',
                    'password' => 'password123',
                    'phone_number' => '+1234567890',
                    'family_member' => 2,
                    'country' => 'USA',
                    'state' => 'California',
                    'city' => 'Los Angeles',
                    'zip_code' => '90001',
                    'address' => '123 Main St',
                    'unit_name' => 'Unit 101',
                    'lease_start_date' => '2024-01-01',
                    'lease_end_date' => '2024-12-31'
                ]
            ];

            // Add headers
            $headers = array_keys($data[0]);
            foreach ($headers as $i => $header) {
                $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
            }

            // Add sample data
            foreach ($data as $rowIndex => $row) {
                foreach ($headers as $colIndex => $header) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
                }
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Output to browser
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'tenant_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
