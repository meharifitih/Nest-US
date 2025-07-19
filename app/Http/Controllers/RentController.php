<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Type;
use App\Models\User;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;

class RentController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::whereHas('types', function($q) {
            $q->whereHas('types', function($q2) {
                $q2->where('type', 'rent');
            });
        });

        if (\Auth::user()->type == 'tenant') {
            // For tenants, only show rent invoices for their unit
            $tenant = \App\Models\Tenant::where('user_id', \Auth::user()->id)->first();
            $query->where('unit_id', $tenant->unit);
            
            // Filter properties and units for tenant
            $properties = \App\Models\Property::where('id', $tenant->property)->get();
            $units = \App\Models\PropertyUnit::where('id', $tenant->unit)->get();
            $tenants = \App\Models\Tenant::where('id', $tenant->id)->with('user')->get();
        } else {
            // For owners, show all rent invoices for their properties
            $query->whereHas('properties', function($q) {
                $q->where('parent_id', parentId());
            });
            
            // Get all properties and units for owner
            $properties = \App\Models\Property::where('parent_id', parentId())->get();
            $units = \App\Models\PropertyUnit::where('parent_id', parentId())->get();
            $tenants = \App\Models\Tenant::with('user')->where('parent_id', parentId())->get();
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->tenant) {
            $query->whereHas('tenants', function($q) use ($request) {
                $q->where('user_id', $request->tenant);
            });
        }
        if ($request->property_id) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->invoice_month) {
            $query->whereMonth('invoice_month', date('m', strtotime($request->invoice_month)));
            $query->whereYear('invoice_month', date('Y', strtotime($request->invoice_month)));
        }
        if ($request->end_date) {
            $query->whereDate('end_date', $request->end_date);
        }

        $invoices = $query->get();
        $statusOptions = \App\Models\Invoice::$status;
        
        return view('rent.index', compact('invoices', 'statusOptions', 'tenants', 'properties', 'units'));
    }

    public function create()
    {
        // Only allow non-tenants to create
        if (auth()->user()->type === 'tenant') {
            abort(403);
        }
        $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
        $property->prepend(__('Select Property'), '');
        // Only fetch rent types
        $types = Type::where('parent_id', parentId())->where('type', 'rent')->get()->pluck('title', 'id');
        $types->prepend(__('Rent'), '');
        $invoiceNumber = $this->invoiceNumber();
        return view('rent.create', compact('types', 'property', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'property_id' => 'required',
                'unit_id' => 'required',
                'invoice_month' => 'required',
                'end_date' => 'required',
                'types' => 'required|array|min:1',
                'types.*.amount' => 'required|numeric|min:1',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }
        // Only allow invoice if rent is > 0
        $amount = $request->types[0]['amount'] ?? 0;
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Unit rent must be greater than zero.');
        }
        // Ensure invoice number is unique
        $invoiceNumber = $request->invoice_id ? intval($request->invoice_id) : $this->generateUniqueInvoiceNumber();
        
        // If user provided a custom number, ensure it's unique
        if ($request->invoice_id && Invoice::where('invoice_id', $invoiceNumber)->exists()) {
            $invoiceNumber = $this->generateUniqueInvoiceNumber();
        }
        
        $invoice = new Invoice();
        $invoice->invoice_id = $invoiceNumber;
        $invoice->property_id = $request->property_id;
        $invoice->unit_id = $request->unit_id;
        $invoice->invoice_month = $request->invoice_month . '-01';
        $invoice->end_date = $request->end_date;
        $invoice->notes = $request->notes;
        $invoice->status = 'open';
        $invoice->parent_id = parentId();
        $invoice->save();
        $rentType = Type::firstOrCreate([
            'type' => 'rent',
            'title' => 'Rent',
            'parent_id' => parentId(),
        ]);
        $invoiceItem = new InvoiceItem();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->invoice_type = $rentType->id;
        $invoiceItem->amount = $amount;
        $invoiceItem->description = $request->types[0]['description'] ?? '';
        $invoiceItem->save();

        // Send email notification to tenant
        $this->sendRentInvoiceEmail($invoice);

        return redirect()->route('rent.index')->with('success', __('Rent invoice successfully created.'));
    }

    public function destroy(Invoice $invoice)
    {
        if (\Auth::user()->can('delete invoice')) {
            $invoice->delete();
            return redirect()->route('rent.index')->with('success', __('Rent invoice successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    private function invoiceNumber()
    {
        $latest = Invoice::orderBy('invoice_id', 'desc')->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->invoice_id + 1;
        }
    }
    
    private function generateUniqueInvoiceNumber()
    {
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            $invoiceNumber = $this->invoiceNumber();
            $attempts++;
            
            if ($attempts > $maxAttempts) {
                throw new \Exception('Unable to generate unique invoice number after ' . $maxAttempts . ' attempts');
            }
        } while (Invoice::where('invoice_id', $invoiceNumber)->exists());
        
        return $invoiceNumber;
    }

    /**
     * Send email notification to tenant for rent invoice
     */
    private function sendRentInvoiceEmail($invoice)
    {
        try {
            // Get tenant information
            $tenant = Tenant::where('unit', $invoice->unit_id)->first();
            if (!$tenant) {
                \Log::info('No tenant found for unit: ' . $invoice->unit_id);
                return;
            }

            $user = User::find($tenant->user_id);
            if (!$user || !$user->email) {
                \Log::info('No user or email found for tenant: ' . $tenant->id);
                return;
            }

            // Get property and unit information
            $property = Property::find($invoice->property_id);
            $unit = PropertyUnit::find($invoice->unit_id);
            $settings = settings();

            // Prepare email data
            $emailData = [
                'subject' => 'New Rent Invoice - ' . $settings['company_name'],
                'message' => $this->getRentInvoiceEmailTemplate($user, $invoice, $property, $unit, $settings),
                'module' => 'rent_invoice',
                'logo' => $settings['company_logo'] ?? 'logo.png'
            ];

            // Send email using existing helper function
            $response = commonEmailSend($user->email, $emailData);
            
            if ($response['status'] == 'error') {
                \Log::error('Rent invoice email notification error', [
                    'email' => $user->email,
                    'error' => $response['message']
                ]);
            } else {
                \Log::info('Rent invoice email sent successfully to: ' . $user->email);
            }
        } catch (\Exception $e) {
            \Log::error('Rent invoice email notification exception', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id
            ]);
        }
    }

    /**
     * Generate email template for rent invoice
     */
    private function getRentInvoiceEmailTemplate($user, $invoice, $property, $unit, $settings)
    {
        $invoiceNumber = invoicePrefix() . $invoice->invoice_id;
        $amount = priceFormat($invoice->getInvoiceDueAmount());
        $dueDate = date('M j, Y', strtotime($invoice->end_date));
        $invoiceDate = date('M j, Y', strtotime($invoice->created_at));

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
                <h2 style='color: #333; margin: 0;'>New Rent Invoice</h2>
            </div>
            
            <div style='padding: 20px;'>
                <p>Dear <strong>{$user->name}</strong>,</p>
                
                <p>A new rent invoice has been generated for your property. Please find the details below:</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <h3 style='margin-top: 0; color: #333;'>Invoice Details</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Invoice Number:</td>
                            <td style='padding: 8px 0;'>{$invoiceNumber}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Property:</td>
                            <td style='padding: 8px 0;'>{$property->name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Unit:</td>
                            <td style='padding: 8px 0;'>{$unit->name}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Amount Due:</td>
                            <td style='padding: 8px 0; color: #dc3545; font-weight: bold;'>{$amount}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Due Date:</td>
                            <td style='padding: 8px 0;'>{$dueDate}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Invoice Date:</td>
                            <td style='padding: 8px 0;'>{$invoiceDate}</td>
                        </tr>
                    </table>
                </div>
                
                " . ($invoice->notes ? "<p><strong>Notes:</strong> {$invoice->notes}</p>" : "") . "
                
                <p>Please ensure payment is made by the due date to avoid any late fees.</p>
                
                <p>If you have any questions regarding this invoice, please don't hesitate to contact us.</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='margin: 5px 0;'><strong>{$settings['company_name']}</strong></p>
                    <p style='margin: 5px 0; color: #666;'>{$settings['company_email']}</p>
                    <p style='margin: 5px 0; color: #666;'>{$settings['company_phone']}</p>
                    <p style='margin: 5px 0; color: #666;'>{$settings['company_address']}</p>
                </div>
            </div>
        </div>";
    }
}
