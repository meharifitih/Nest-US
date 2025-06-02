<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\TenantExcelUpload;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TenantsImport;

class TenantExcelUploadController extends Controller
{
    public function selectProperty()
    {
        $properties = Property::where('parent_id', parentId())->get();
        return view('tenant_excel_upload.select_property', compact('properties'));
    }

    public function uploadForm(Property $property)
    {
        return view('tenant_excel_upload.upload_form', compact('property'));
    }

    public function upload(Request $request, Property $property)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

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

            Excel::import(new TenantsImport($property->id), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                'batchSize' => 100,
                'chunkSize' => 100,
            ]);

            $excelUpload->status = 'completed';
            $excelUpload->save();

            return redirect()->back()->with('success', __('Tenant information successfully imported.'));
        } catch (\Exception $e) {
            if (isset($excelUpload)) {
                $excelUpload->status = 'failed';
                $excelUpload->error_log = $e->getMessage();
                $excelUpload->save();
            }
            return redirect()->back()->with('error', __('Error importing tenant information: ') . $e->getMessage());
        }
    }
} 