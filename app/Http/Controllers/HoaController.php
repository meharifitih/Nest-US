<?php

namespace App\Http\Controllers;

use App\Models\Hoa;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe;

class HoaController extends Controller
{
    public function index(Request $request)
    {
        $query = Hoa::query();

        if (Auth::user()->type == 'tenant') {
            // For tenants, only show HOA for their unit
            $tenant = Tenant::where('user_id', Auth::user()->id)->first();
            $query->where('unit_id', $tenant->unit);
            
            // Filter properties and units for tenant
            $properties = Property::where('id', $tenant->property)->get();
            $units = PropertyUnit::where('id', $tenant->unit)->get();
            $tenants = Tenant::where('id', $tenant->id)->with('user')->get();
        } else {
            // For owners, show all HOA for their properties
            $query->whereHas('property', function($q) {
                $q->where('parent_id', parentId());
            });
            
            // Get all properties and units for owner
            $properties = Property::where('parent_id', parentId())->get();
            $units = PropertyUnit::where('parent_id', parentId())->get();
            $tenants = Tenant::with('user')->where('parent_id', parentId())->get();
        }

        if ($request->property_id) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $hoas = $query->get();
        $hoaTypes = \App\Models\Type::where('type', 'hoa')->get();
        $statusOptions = [
            'pending' => __('Pending'),
            'open' => __('Open'),
            'paid' => __('Paid')
        ];

        return view('hoa.index', compact('hoas', 'properties', 'units', 'tenants', 'hoaTypes', 'statusOptions'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('parent_id', parentId())->get();
        $hoa_types = \App\Models\Type::where('type', 'hoa')->pluck('title', 'id');
        $tenants = \App\Models\Tenant::with('user')->where('parent_id', parentId())->get();
        $units = [];
        if ($request->property_id) {
            $units = \App\Models\PropertyUnit::where('property_id', $request->property_id)->pluck('name', 'id');
        }
        return view('hoa.create', compact('properties', 'hoa_types', 'units', 'tenants'));
    }

    public function store(Request $request)
    {
        $unitIds = $request->unit_ids ?? [];
        if (in_array('all', $unitIds)) {
            $unitIds = \App\Models\PropertyUnit::where('property_id', $request->property_id)->pluck('id')->toArray();
            if (empty($unitIds)) {
                $unitIds = [null];
            }
            $request->merge(['unit_ids' => $unitIds]);
        } else {
            $unitIds = array_filter($unitIds, function($v) { return $v !== 'all'; });
            $request->merge(['unit_ids' => $unitIds]);
        }

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'nullable|integer', // allow null for property-wide
            'hoa_type_id' => 'required|exists:types,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,semi_annual,annual',
            'due_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $latestHoa = \App\Models\Hoa::orderByDesc('hoa_number')->first();
        $nextHoaNumber = $latestHoa ? $latestHoa->hoa_number + 1 : 1;

        foreach ($validated['unit_ids'] as $unitId) {
            $data = $validated;
            $data['unit_id'] = $unitId; // will be null for property-wide
            $data['created_by'] = Auth::id();
            $data['status'] = 'open';
            $data['hoa_number'] = $nextHoaNumber++;

            // Set tenant_id from unit
            if ($unitId) {
                $unit = \App\Models\PropertyUnit::find($unitId);
                $tenant = $unit && $unit->tenants ? $unit->tenants : null;
                $data['tenant_id'] = $tenant ? $tenant->id : null;
            } else {
                $data['tenant_id'] = null; // property-wide HOA
            }

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

    public function stripePayment(Request $request, Hoa $hoa)
    {
        $request->validate([
            'stripeToken' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'card_holder_name' => 'required|string',
        ]);

        try {
            // Get payment settings from property owner
            $ownerId = $hoa->property ? $hoa->property->parent_id : ($hoa->creator ? $hoa->creator->id : null);
            $settings = $ownerId ? invoicePaymentSettings($ownerId) : invoicePaymentSettings(1);

            $transactionID = uniqid('', true);
            Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);

            $response = Stripe\Charge::create([
                "amount" => 100 * $request->amount,
                "currency" => $settings['CURRENCY'] ?? 'USD',
                "source" => $request->stripeToken,
                "description" => "HOA Payment - " . $hoa->hoa_number,
                "metadata" => ["hoa_id" => $hoa->id],
            ]);

            if ($response['status'] == 'succeeded') {
                $hoa->update([
                    'status' => 'paid',
                    'paid_date' => now(),
                    'receipt' => 'stripe:' . $response['id'],
                ]);

                return redirect()->route('hoa.index')->with('success', 'HOA payment completed successfully.');
            } else {
                return redirect()->back()->with('error', 'Payment failed. Please try again.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function paypalPayment(Request $request, Hoa $hoa)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            // Get payment settings from property owner
            $ownerId = $hoa->property ? $hoa->property->parent_id : ($hoa->creator ? $hoa->creator->id : null);
            $settings = $ownerId ? invoicePaymentSettings($ownerId) : invoicePaymentSettings(1);

            $provider = new \Srmklive\PayPal\Services\PayPal;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('hoa.paypal.status', [$hoa->id, 'success']),
                    "cancel_url" => route('hoa.paypal.status', [$hoa->id, 'cancel']),
                ],
                "purchase_units" => [
                    0 => [
                        "amount" => [
                            "currency_code" => $settings['CURRENCY'] ?? 'USD',
                            "value" => $request->amount
                        ],
                        "description" => "HOA Payment - " . $hoa->hoa_number
                    ]
                ]
            ]);

            if (isset($response['id']) && $response['id'] != null) {
                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        return redirect()->away($links['href']);
                    }
                }
            }

            return redirect()->back()->with('error', 'Something went wrong with PayPal.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function paypalStatus(Request $request, Hoa $hoa, $status)
    {
        if ($status == 'success') {
            try {
                $provider = new \Srmklive\PayPal\Services\PayPal;
                $provider->setApiCredentials(config('paypal'));
                $provider->getAccessToken();

                $response = $provider->capturePaymentOrder($request['token']);

                if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                    $hoa->update([
                        'status' => 'paid',
                        'paid_date' => now(),
                        'receipt' => 'paypal:' . $response['id'],
                    ]);

                    return redirect()->route('hoa.index')->with('success', 'HOA payment completed successfully.');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Payment failed.');
    }

    public function edit(Hoa $hoa)
    {
        if (\Auth::user()->can('edit hoa')) {
            $properties = Property::where('parent_id', parentId())->get();
            $hoa_types = \App\Models\Type::where('type', 'hoa')->pluck('title', 'id');
            $tenants = \App\Models\Tenant::with('user')->where('parent_id', parentId())->get();
            $units = PropertyUnit::where('property_id', $hoa->property_id)->pluck('name', 'id');
            return view('hoa.edit', compact('hoa', 'properties', 'hoa_types', 'units', 'tenants'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function update(Request $request, Hoa $hoa)
    {
        if (\Auth::user()->can('edit hoa')) {
            $validated = $request->validate([
                'property_id' => 'required|exists:properties,id',
                'unit_id' => 'required|exists:property_units,id',
                'hoa_type_id' => 'required|exists:types,id',
                'amount' => 'required|numeric|min:0',
                'frequency' => 'required|in:monthly,quarterly,semi_annual,annual',
                'due_date' => 'required|date',
                'description' => 'nullable|string'
            ]);

            $hoa->property_id = $validated['property_id'];
            $hoa->unit_id = $validated['unit_id'];
            $hoa->hoa_type_id = $validated['hoa_type_id'];
            $hoa->amount = $validated['amount'];
            $hoa->frequency = $validated['frequency'];
            $hoa->due_date = $validated['due_date'];
            $hoa->description = $validated['description'] ?? null;
            $hoa->save();

            return redirect()->route('hoa.index')->with('success', 'HOA updated successfully');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
} 