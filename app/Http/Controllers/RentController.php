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
use App\Services\WhatsAppService;

class RentController extends Controller
{
    public function index()
    {
        $invoices = Invoice::whereHas('types', function($q) {
            $q->whereHas('types', function($q2) {
                $q2->where('type', 'rent');
            });
        })->get();

        return view('rent.index', compact('invoices'));
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
        $invoice = new Invoice();
        $invoice->invoice_id = $request->invoice_id;
        $invoice->property_id = $request->property_id;
        $invoice->unit_id = $request->unit_id;
        $invoice->invoice_month = $request->invoice_month . '-01';
        $invoice->end_date = $request->end_date;
        $invoice->notes = $request->notes;
        $invoice->status = 'open';
        $invoice->parent_id = parentId();
        $invoice->save();
        $types = $request->types;
        $rentType = Type::firstOrCreate([
            'type' => 'rent',
            'title' => 'Rent',
            'parent_id' => parentId(),
        ]);
        for ($i = 0; $i < count($types); $i++) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->invoice_type = $rentType->id;
            $invoiceItem->amount = $types[$i]['amount'];
            $invoiceItem->description = $types[$i]['description'];
            $invoiceItem->save();
        }

        // Send WhatsApp notification to tenant
        $tenant = Tenant::where('unit', $invoice->unit_id)->first();
        if ($tenant) {
            $user = User::find($tenant->user_id);
            if ($user && !empty($user->phone_number)) {
                // Format phone number
                $phone = preg_replace('/[^0-9+]/', '', $user->phone_number);
                if (substr($phone, 0, 1) !== '+') {
                    $phone = '+' . $phone;
                }

                $property = Property::find($invoice->property_id);
                $unit = PropertyUnit::find($invoice->unit_id);

                $message = "🔔 *System Notification*\n\n";
                $message .= "New rent invoice has been generated:\n\n";
                $message .= "📋 *Invoice Details:*\n";
                $message .= "Invoice #: " . invoicePrefix() . $invoice->invoice_id . "\n";
                $message .= "Property: {$property->name}\n";
                $message .= "Unit: {$unit->name}\n";
                $message .= "Month: " . date('F Y', strtotime($invoice->invoice_month)) . "\n";
                $message .= "Due Date: " . date('d M Y', strtotime($invoice->end_date)) . "\n";
                $message .= "Amount: " . priceFormat($invoice->getInvoiceSubTotalAmount()) . "\n\n";
                $message .= "Please make the payment before the due date.\n\n";
                $message .= "Best regards,\n" . settings()['company_name'];

                $response = app(WhatsAppService::class)->sendMessage($phone, $message);
                
                if ($response['status'] === 'error') {
                    \Log::error('Failed to send WhatsApp notification to tenant', [
                        'error' => $response['message'],
                        'user_id' => $user->id,
                        'phone' => $phone
                    ]);
                } else {
                    \Log::info('WhatsApp notification sent to tenant', [
                        'user_id' => $user->id,
                        'phone' => $phone
                    ]);
                }
            }
        }

        return redirect()->route('rent.index')->with('success', __('Rent invoice successfully created.'));
    }

    private function invoiceNumber()
    {
        $latest = Invoice::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->invoice_id + 1;
        }
    }
}
