<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\UnifiedExcelUpload;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UnifiedSingleSheetImport;

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
        \Log::info('Upload request received', [
            'property_id' => $request->property_id,
            'file' => $request->file('excel_file') ? $request->file('excel_file')->getClientOriginalName() : 'no file',
            'file_size' => $request->file('excel_file') ? $request->file('excel_file')->getSize() : 0,
            'user_id' => \Auth::id(),
            'parent_id' => parentId(),
            'all_data' => $request->all()
        ]);
        
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $excelUpload = null;
        $importedCount = 0;
        $errorCount = 0;
        $errors = [];

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
            $import = new UnifiedSingleSheetImport($request->property_id);
            Excel::import($import, $file, null, \Maatwebsite\Excel\Excel::XLSX, [
                'batchSize' => 100,
                'chunkSize' => 100,
            ]);

            // Get import statistics
            $importedCount = $import->getImportedCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $excelUpload->status = 'completed';
            $excelUpload->imported_count = $importedCount;
            $excelUpload->error_count = $errorCount;
            $excelUpload->save();

            $message = "Successfully imported {$importedCount} records.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} records had errors.";
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'imported_count' => $importedCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            if (isset($excelUpload)) {
                $excelUpload->status = 'failed';
                $excelUpload->error_log = implode("\n", $errors);
                $excelUpload->error_count = count($errors);
                $excelUpload->save();
            }

            \Log::error('Excel validation error: ' . implode(', ', $errors));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check your Excel file format and data. Some rows contain invalid information.',
                    'errors' => $errors,
                    'error_count' => count($errors)
                ], 422);
            }

            return redirect()->back()->with('error', __('Please check your Excel file format and data. Some rows contain invalid information.'));

        } catch (\Exception $e) {
            if (isset($excelUpload)) {
                $excelUpload->status = 'failed';
                $excelUpload->error_log = $e->getMessage();
                $excelUpload->error_count = 1;
                $excelUpload->save();
            }
            
            \Log::error('Excel import error: ' . $e->getMessage());
            
            // Provide user-friendly error messages
            $userMessage = 'An error occurred while processing your file.';
            
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $userMessage = 'Some records already exist in the system. Please check for duplicates.';
            } elseif (strpos($e->getMessage(), 'column') !== false && strpos($e->getMessage(), 'does not exist') !== false) {
                $userMessage = 'System configuration error. Please contact support.';
            } elseif (strpos($e->getMessage(), 'validation') !== false) {
                $userMessage = 'Please check your data format and ensure all required fields are filled.';
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $userMessage,
                    'errors' => [$e->getMessage()],
                    'error_count' => 1
                ], 500);
            }

            return redirect()->back()->with('error', $userMessage);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Create single sheet with combined unit and tenant data
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Units & Tenants');
        
        // Combined data structure with both unit and tenant fields
        $combinedData = [
            [
                // Unit fields
                'unit_name' => 'Unit 101',
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
                'unit_notes' => 'Sample unit',
                // Tenant fields
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
                'lease_start_date' => '2024-01-01',
                'lease_end_date' => '2025-01-01',
                'tenant_notes' => 'Test tenant',
            ],
            [
                // Unit fields
                'unit_name' => 'Unit 102',
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
                'unit_notes' => 'Studio apartment',
                // Tenant fields
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
                'lease_start_date' => '2024-02-01',
                'lease_end_date' => '2025-02-01',
                'tenant_notes' => 'Another test tenant',
            ]
        ];
        
        $headers = array_keys($combinedData[0]);
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }
        foreach ($combinedData as $rowIndex => $row) {
            foreach ($headers as $colIndex => $header) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $row[$header]);
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'unified_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
} 