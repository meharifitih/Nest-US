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
        $hoa_types = \App\Models\Type::where('type', 'hoa')->pluck('title', 'id');
        $hoas = Hoa::with(['property', 'unit.tenants.user', 'hoaType'])
            ->when($request->hoa_type_filter, function ($query) use ($request) {
                $query->where('hoa_type_id', $request->hoa_type_filter);
            })
            ->when($request->status_filter, function ($query) use ($request) {
                $query->where('status', $request->status_filter);
            })
            ->when(Auth::user()->hasRole('tenant') && Auth::user()->tenant, function ($query) {
                return $query->where('tenant_id', Auth::user()->tenant->id);
            })
            ->latest()
            ->paginate(10);
        return view('hoa.index', compact('hoas', 'hoa_types'));
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
} 