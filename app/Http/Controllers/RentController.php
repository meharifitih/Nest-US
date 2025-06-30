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
        // Send WhatsApp notification to tenant
        $tenant = Tenant::where('unit', $invoice->unit_id)->first();
        if ($tenant) {
            $user = User::find($tenant->user_id);
            if ($user && !empty($user->phone_number)) {
                $property = Property::find($invoice->property_id);
                $unit = PropertyUnit::find($invoice->unit_id);
            }
        }
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
        $latest = Invoice::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->invoice_id + 1;
        }
    }
}
