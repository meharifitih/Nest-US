<?php

namespace App\Http\Controllers;

use App\Models\Hoa;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HoaController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage hoa')) {
            $query = \App\Models\Hoa::query();

            if ($request->property_id) {
                $query->where('property_id', $request->property_id);
            }
            if ($request->unit_id) {
                $query->where('unit_id', $request->unit_id);
            }
            if ($request->tenant) {
                $query->whereHas('unit.tenants', function($q) use ($request) {
                    $q->where('user_id', $request->tenant);
                });
            }
            if ($request->hoa_type) {
                $query->where('hoa_type_id', $request->hoa_type);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->due_date) {
                $query->whereDate('due_date', $request->due_date);
            }

            $hoas = $query->get();
            $properties = \App\Models\Property::all();
            $units = \App\Models\PropertyUnit::all();
            $tenants = \App\Models\Tenant::with('user')->get();
            $hoaTypes = \App\Models\Type::where('type', 'hoa')->get();
            $statusOptions = [
                'pending' => __('Pending'),
                'open' => __('Open'),
                'paid' => __('Paid')
            ];

            return view('hoa.index', compact('hoas', 'properties', 'units', 'tenants', 'hoaTypes', 'statusOptions'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function create(Request $request)
    {
        $properties = Property::all();
        $hoa_types = \App\Models\Type::where('type', 'hoa')->pluck('title', 'id');
        $units = [];
        if ($request->property_id) {
            $units = \App\Models\PropertyUnit::where('property_id', $request->property_id)->pluck('name', 'id');
        }
        return view('hoa.create', compact('properties', 'hoa_types', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'required|exists:property_units,id',
            'hoa_type_id' => 'required|exists:types,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,semi_annual,annual',
            'due_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        foreach ($validated['unit_ids'] as $unitId) {
            $data = $validated;
            $data['unit_id'] = $unitId;
            $data['created_by'] = Auth::id();
            $data['status'] = 'open';

            // Set tenant_id from unit
            $unit = \App\Models\PropertyUnit::find($unitId);
            $tenant = $unit && $unit->tenants ? $unit->tenants : null;
            $data['tenant_id'] = $tenant ? $tenant->id : null;

            Hoa::create($data);
        }

        return redirect()->route('hoa.index')->with('success', 'HOA created successfully');
    }

    public function getTenantForUnit($unit_id)
    {
        $unit = \App\Models\PropertyUnit::with('tenants.user')->find($unit_id);
        if ($unit && $unit->tenants && $unit->tenants->user) {
            return response()->json(['tenant' => $unit->tenants->user->name]);
        }
        return response()->json(['tenant' => null]);
    }

    public function show(Hoa $hoa)
    {
        $hoa->load(['property', 'unit.tenants.user', 'creator']);
        // Get the property owner's id for this HOA
        $ownerId = $hoa->property ? $hoa->property->parent_id : ($hoa->creator ? $hoa->creator->id : null);
        $settings = $ownerId ? invoicePaymentSettings($ownerId) : invoicePaymentSettings(1);
        $paymentAccounts = \App\Models\PaymentAccount::where('is_active', 1)->get();
        return view('hoa.show', compact('hoa', 'settings', 'paymentAccounts'));
    }

    public function destroy(Hoa $hoa)
    {
        $hoa->delete();
        return redirect()->route('hoa.index')->with('success', 'HOA deleted successfully');
    }

    public function markAsPaid(Hoa $hoa)
    {
        if (Auth::user()->hasRole('tenant')) {
            // Tenant is making payment - set to pending for owner approval
            $data = [
                'status' => 'pending',
                'paid_date' => now(),
                'notes' => request('notes')
            ];

            if (request()->hasFile('receipt')) {
                $receipt = request()->file('receipt');
                $filename = time() . '_' . $receipt->getClientOriginalName();
                $receipt->storeAs('hoa-receipts', $filename, 'public');
                $data['receipt'] = 'hoa-receipts/' . $filename;
            }

            $hoa->update($data);
            return redirect()->back()->with('success', 'HOA payment submitted for approval');
        } else {
            // Owner is approving payment
            $hoa->update([
                'status' => 'paid',
                'paid_date' => now()
            ]);
            return redirect()->back()->with('success', 'HOA marked as paid');
        }
    }

    public function banktransferPayment(Request $request, Hoa $hoa)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('hoa-receipts', $filename, 'public');
            $hoa->receipt = 'hoa-receipts/' . $filename;
        }
        $hoa->amount = $request->amount;
        $hoa->status = 'pending';
        $hoa->paid_date = now();
        $hoa->save();

        return redirect()->route('hoa.index')->with('success', 'HOA bank transfer submitted for approval.');
    }

    public function receiptPayment(Request $request, Hoa $hoa)
    {
        $request->validate([
            'payment_type' => 'required|in:telebirr,cbe',
            'receipt_number' => 'required|string|max:255',
        ]);
        $hoa->receipt = strtoupper($request->payment_type) . ':' . $request->receipt_number;
        $hoa->status = 'pending';
        $hoa->paid_date = now();
        $hoa->save();
        return redirect()->route('hoa.index')->with('success', 'Payment submitted for approval.');
    }
} 