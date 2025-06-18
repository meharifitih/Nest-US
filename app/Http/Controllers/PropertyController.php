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
                    'location' => 'required',
                    'house_number' => 'required',
                    'woreda' => 'required',
                    'sub_city' => 'required',
                    'city' => 'required',
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
            $property->location = $request->location;
            $property->description = $request->description;
            $property->house_number = $request->house_number;
            $property->woreda = $request->woreda;
            $property->sub_city = $request->sub_city;
            $property->city = $request->city;
            $property->type = $request->type;
            $property->parent_id = parentId();
            $property->save();

            if ($request->thumbnail != 'undefined') {
                $thumbnailFilenameWithExt = $request->file('thumbnail')->getClientOriginalName();
                $thumbnailFilename = pathinfo($thumbnailFilenameWithExt, PATHINFO_FILENAME);
                $thumbnailExtension = $request->file('thumbnail')->getClientOriginalExtension();
                $thumbnailFileName = $thumbnailFilename . '_' . time() . '.' . $thumbnailExtension;
                $request->file('thumbnail')->storeAs('upload/thumbnail', $thumbnailFileName, 'public');
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
                    $file->storeAs('upload/property', $propertyFileName, 'public');

                    $propertyImage = new PropertyImage();
                    $propertyImage->property_id = $property->id;
                    $propertyImage->image = $propertyFileName;
                    $propertyImage->type = 'extra';
                    $propertyImage->save();
                }
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
                    'location' => 'required',
                    'house_number' => 'required',
                    'woreda' => 'required',
                    'sub_city' => 'required',
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
            $property->location = $request->location;
            $property->description = $request->description;
            $property->house_number = $request->house_number;
            $property->woreda = $request->woreda;
            $property->sub_city = $request->sub_city;
            $property->type = $request->type;
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
                $request->file('thumbnail')->storeAs('upload/thumbnail', $thumbnailFileName, 'public');
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
                    $file->storeAs('upload/property', $propertyFileName, 'public');

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


    public function units(Request $request)
    {
        if (\Auth::user()->can('manage unit')) {
            $properties = \App\Models\Property::where('parent_id', parentId())->get();
            $tenants = \App\Models\Tenant::with('user')->where('parent_id', parentId())->get();
            $units = \App\Models\PropertyUnit::where('parent_id', parentId())
                ->when($request->property_id, function($query) use ($request) {
                    $query->where('property_id', $request->property_id);
                })
                ->when($request->rent_type, function($query) use ($request) {
                    $query->where('rent_type', $request->rent_type);
                })
                ->when($request->rent, function($query) use ($request) {
                    $query->where('rent', $request->rent);
                })
                ->when($request->start_date, function($query) use ($request) {
                    $query->whereDate('start_date', $request->start_date);
                })
                ->when($request->end_date, function($query) use ($request) {
                    $query->whereDate('end_date', $request->end_date);
                })
                ->when($request->tenant, function($query) use ($request) {
                    $query->whereHas('tenants', function($q) use ($request) {
                        $q->where('user_id', $request->tenant);
                    });
                })
                ->get();
            return view('unit.index', compact('units', 'properties', 'tenants'));
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
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
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
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            $unit->start_date = $request->start_date;
            $unit->end_date = $request->end_date;
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
        $name = Property::where('parent_id', parentId())->pluck('name', 'id');
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
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
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
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            $unit->start_date = $request->start_date;
            $unit->end_date = $request->end_date;
            $unit->notes = $request->notes;
            $unit->parent_id = parentId();
            $unit->save();
            return redirect()->route('unit.index')->with('success', __('Unit successfully created.'));
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
                    'baths' => 'required',
                    'rent' => 'required',
                    'rent_type' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $unit = PropertyUnit::find($unit_id);
            $unit->name = $request->name;
            $unit->bedroom = $request->bedroom;
            $unit->baths = $request->baths;
            $unit->rent = $request->rent;
            $unit->rent_type = $request->rent_type;
            $unit->start_date = $request->start_date;
            $unit->end_date = $request->end_date;
            $unit->notes = $request->notes;
            $unit->save();
            return redirect()->route('unit.index')->with('success', __('Unit successfully updated.'));
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
        $units = PropertyUnit::where('property_id', $property_id)->get();
        $result = [];
        foreach ($units as $unit) {
            $result[$unit->id] = [
                'name' => $unit->name,
                'rent' => $unit->rent
            ];
        }
        return response()->json($result);
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

                // Import with batch size and chunking
                Excel::import(new TenantsImport($property->id), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                    'batchSize' => 100,
                    'chunkSize' => 100,
                ]);

                $excelUpload->status = 'completed';
                $excelUpload->save();

                return response()->json([
                    'status' => 'success',
                    'msg' => __('Tenant information successfully imported.')
                ]);
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $errors = [];
                foreach ($failures as $failure) {
                    $errors[] = "Row {$failure->row()}: {$failure->errors()[0]}";
                }
                
                if (isset($excelUpload)) {
                    $excelUpload->status = 'failed';
                    $excelUpload->error_log = implode("\n", $errors);
                    $excelUpload->save();
                }

                return response()->json([
                    'status' => 'error',
                    'msg' => __('Validation errors: ') . implode(', ', $errors)
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

    public function downloadUnitExcelTemplate()
    {
        if (\Auth::user()->can('edit property')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $data = [
                [
                    'name' => 'Unit 101',
                    'bedroom' => 2,
                    'kitchen' => 1,
                    'baths' => 1,
                    'rent' => 5000,
                    'rent_type' => 'monthly',
                    'deposit_type' => 'fixed',
                    'deposit_amount' => 1000,
                    'late_fee_type' => 'fixed',
                    'late_fee_amount' => 100,
                    'incident_receipt_amount' => 0,
                    'notes' => 'Sample unit',
                ]
            ];
            $headers = array_keys($data[0]);
            foreach ($headers as $i => $header) {
                $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
            }
            foreach ($data as $rowIndex => $row) {
                foreach ($headers as $colIndex => $header) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
                }
            }
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'unit_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
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
                    'email' => 'john@email.com',
                    'phone_number' => '251912345678',
                    'family_member' => 3,
                    'sub_city' => 'Bole',
                    'woreda' => '01',
                    'house_number' => '123',
                    'location' => 'Main Road',
                    'city' => 'Addis Ababa',
                    'unit_name' => '',
                    'lease_start_date' => '2024-01-01',
                    'lease_end_date' => '2025-01-01',
                    'notes' => 'Test tenant',
                ]
            ];
            $headers = array_keys($data[0]);
            foreach ($headers as $i => $header) {
                $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
            }
            foreach ($data as $rowIndex => $row) {
                foreach ($headers as $colIndex => $header) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
                }
            }
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, 'tenant_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);
        }
    }

    public function getUnits(Request $request)
    {
        $propertyId = $request->input('property_id');
        $units = PropertyUnit::where('property_id', $propertyId)->get();
        return response()->json($units);
    }

    // Autofill property address details for tenant create
    public function getAddress($id)
    {
        $property = \App\Models\Property::find($id);
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }
        return response()->json([
            'sub_city' => $property->sub_city,
            'woreda' => $property->woreda,
            'house_number' => $property->house_number,
            'location' => $property->location,
            'city' => $property->city,
        ]);
    }

    public function uploadUnitExcel(Request $request, Property $property)
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
                $file->storeAs('upload/unit_excel', $fileName);

                // Import units only
                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UnitImport($property->id), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                    'batchSize' => 100,
                    'chunkSize' => 100,
                ]);

                return response()->json([
                    'status' => 'success',
                    'msg' => __('Unit information successfully imported.')
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'msg' => __('Error importing unit information: ') . $e->getMessage()
                ]);
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function unitExcelUploadForm()
    {
        $properties = \App\Models\Property::where('parent_id', parentId())->get();
        $uploads = \App\Models\UnitExcelUpload::orderBy('created_at', 'desc')->get();
        return view('unit.upload_form', compact('properties', 'uploads'));
    }

    public function unitExcelUpload(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
        try {
            $file = $request->file('excel_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('upload/unit_excel', $fileName);
            $excelUpload = new \App\Models\UnitExcelUpload();
            $excelUpload->property_id = $request->property_id;
            $excelUpload->file_name = $fileName;
            $excelUpload->original_name = $file->getClientOriginalName();
            $excelUpload->parent_id = parentId();
            $excelUpload->status = 'pending';
            $excelUpload->save();
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UnitImport($request->property_id), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                'batchSize' => 100,
                'chunkSize' => 100,
            ]);
            $excelUpload->status = 'completed';
            $excelUpload->save();
            return redirect()->back()->with('success', __('Unit information successfully imported.'));
        } catch (\Exception $e) {
            if (isset($excelUpload)) {
                $excelUpload->status = 'failed';
                $excelUpload->error_log = $e->getMessage();
                $excelUpload->save();
            }
            return redirect()->back()->with('error', __('Error importing unit information: ') . $e->getMessage());
        }
    }
}
