<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\UnifiedExcelUpload;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UnifiedImport;

class UnifiedExcelUploadController extends Controller
{
    public function index()
    {
        $properties = Property::where('parent_id', parentId())->get();
        $uploads = UnifiedExcelUpload::where('parent_id', parentId())
            ->orderBy('created_at', 'desc')
            ->get();
        return view('unified_excel_upload.index', compact('properties', 'uploads'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $file = $request->file('excel_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('upload/unified_excel', $fileName);

            $excelUpload = new UnifiedExcelUpload();
            $excelUpload->property_id = $request->property_id;
            $excelUpload->file_name = $fileName;
            $excelUpload->original_name = $file->getClientOriginalName();
            $excelUpload->parent_id = parentId();
            $excelUpload->status = 'pending';
            $excelUpload->save();

            // Import with batch size and chunking
            Excel::import(new UnifiedImport($request->property_id), $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                'batchSize' => 100,
                'chunkSize' => 100,
            ]);

            $excelUpload->status = 'completed';
            $excelUpload->save();

            return redirect()->back()->with('success', __('Tenant and Unit information successfully imported.'));
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

            return redirect()->back()->with('error', __('Validation errors: ') . implode(', ', $errors));
        } catch (\Exception $e) {
            if (isset($excelUpload)) {
                $excelUpload->status = 'failed';
                $excelUpload->error_log = $e->getMessage();
                $excelUpload->save();
            }
            return redirect()->back()->with('error', __('Error importing data: ') . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Create Units sheet
        $unitsSheet = $spreadsheet->getActiveSheet();
        $unitsSheet->setTitle('Units');
        
        $unitData = [
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
            ],
            [
                'name' => 'Unit 102',
                'bedroom' => 1,
                'kitchen' => 1,
                'baths' => 1,
                'rent' => 3500,
                'rent_type' => 'monthly',
                'deposit_type' => 'fixed',
                'deposit_amount' => 700,
                'late_fee_type' => 'fixed',
                'late_fee_amount' => 100,
                'incident_receipt_amount' => 0,
                'notes' => 'Studio apartment',
            ]
        ];
        
        $unitHeaders = array_keys($unitData[0]);
        foreach ($unitHeaders as $i => $header) {
            $unitsSheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }
        foreach ($unitData as $rowIndex => $row) {
            foreach ($unitHeaders as $colIndex => $header) {
                $unitsSheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
            }
        }

        // Create Tenants sheet
        $tenantsSheet = $spreadsheet->createSheet();
        $tenantsSheet->setTitle('Tenants');
        
        $tenantData = [
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
                'unit_name' => 'Unit 101',
                'lease_start_date' => '2024-01-01',
                'lease_end_date' => '2025-01-01',
                'notes' => 'Test tenant',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane@email.com',
                'phone_number' => '251987654321',
                'family_member' => 2,
                'sub_city' => 'Kazanchis',
                'woreda' => '02',
                'house_number' => '456',
                'location' => 'Side Street',
                'city' => 'Addis Ababa',
                'unit_name' => 'Unit 102',
                'lease_start_date' => '2024-02-01',
                'lease_end_date' => '2025-02-01',
                'notes' => 'Another test tenant',
            ]
        ];
        
        $tenantHeaders = array_keys($tenantData[0]);
        foreach ($tenantHeaders as $i => $header) {
            $tenantsSheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }
        foreach ($tenantData as $rowIndex => $row) {
            foreach ($tenantHeaders as $colIndex => $header) {
                $tenantsSheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
            }
        }

        // Set active sheet back to Units
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'unified_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
} 