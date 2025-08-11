<?php

namespace App\Http\Controllers;

use App\Models\TenantPayment;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Stripe;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Carbon\Carbon;

class TenantPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (Auth::user()->type == 'tenant') {
            $tenant = Tenant::where('user_id', Auth::user()->id)->first();
            if (!$tenant) {
                return redirect()->back()->with('error', 'Tenant not found.');
            }
            
            $payments = TenantPayment::where('tenant_id', $tenant->id)
                ->with(['invoice', 'property', 'unit'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // For property owners/managers
            $payments = TenantPayment::where('parent_id', parentId())
                ->with(['tenant.user', 'invoice', 'property', 'unit'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('tenant.payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = TenantPayment::with(['tenant.user', 'invoice', 'property', 'unit'])->findOrFail($id);
        
        // Check if user has permission to view this payment
        if (Auth::user()->type == 'tenant') {
            $tenant = Tenant::where('user_id', Auth::user()->id)->first();
            if ($payment->tenant_id != $tenant->id) {
                return redirect()->back()->with('error', 'Permission denied.');
            }
        }

        return view('tenant.payments.show', compact('payment'));
    }

    public function create(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $invoice = null;
        $tenant = null;

        if ($invoiceId) {
            $invoice = Invoice::findOrFail($invoiceId);
            $tenant = Tenant::where('unit', $invoice->unit_id)->first();
        } elseif (Auth::user()->type == 'tenant') {
            $tenant = Tenant::where('user_id', Auth::user()->id)->first();
        }

        $properties = Property::where('parent_id', parentId())->get();
        $units = PropertyUnit::where('parent_id', parentId())->get();
        $tenants = Tenant::where('parent_id', parentId())->with('user')->get();

        return view('tenant.payments.create', compact('invoice', 'tenant', 'properties', 'units', 'tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_type' => 'required|in:rent,utilities,maintenance,other',
            'due_date' => 'required|date',
            'is_recurring' => 'boolean',
            'recurring_interval' => 'required_if:is_recurring,1|in:monthly,quarterly,yearly',
        ]);

        $payment = TenantPayment::create([
            'tenant_id' => $request->tenant_id,
            'invoice_id' => $request->invoice_id,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'payment_method' => 'pending',
            'due_date' => $request->due_date,
            'is_recurring' => $request->is_recurring ?? false,
            'recurring_interval' => $request->recurring_interval,
            'parent_id' => parentId(),
        ]);

        return redirect()->route('tenant.payments.payment', $payment->id)
            ->with('success', 'Payment created successfully. Please complete the payment.');
    }

    public function payment($id)
    {
        $payment = TenantPayment::with(['tenant.user', 'invoice', 'tenant.properties'])->findOrFail($id);
        
        // Check permissions
        if (Auth::user()->type == 'tenant') {
            $tenant = Tenant::where('user_id', Auth::user()->id)->first();
            if ($payment->tenant_id != $tenant->id) {
                return redirect()->back()->with('error', 'Permission denied.');
            }
        }

        // Get payment settings from the property owner
        $propertyOwnerId = $payment->tenant->properties->parent_id ?? parentId();
        $settings = invoicePaymentSettings($propertyOwnerId);

        return view('tenant.payments.payment', compact('payment', 'settings'));
    }

    public function stripePayment(Request $request, $id)
    {
        $payment = TenantPayment::with(['tenant.properties'])->findOrFail($id);
        $propertyOwnerId = $payment->tenant->properties->parent_id ?? parentId();
        $settings = invoicePaymentSettings($propertyOwnerId);

        try {
            $transactionID = uniqid('', true);
            Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);

            if ($payment->is_recurring) {
                // Create recurring payment with Stripe
                $response = $this->createStripeSubscription($payment, $settings);
            } else {
                // One-time payment
                $response = Stripe\Charge::create([
                    "amount" => 100 * $payment->amount,
                    "currency" => $settings['CURRENCY'],
                    "source" => $request->stripeToken,
                    "description" => "Payment for {$payment->payment_type} - Tenant: {$payment->tenant->user->name}",
                    "metadata" => ["payment_id" => $payment->id],
                ]);
            }

            if ($response['status'] == 'succeeded' || $response['status'] == 'active') {
                $payment->update([
                    'payment_method' => 'stripe',
                    'transaction_id' => $transactionID,
                    'status' => 'completed',
                    'payment_date' => now(),
                    'stripe_subscription_id' => $response['id'] ?? null,
                    'receipt_url' => $response['receipt_url'] ?? null,
                ]);

                // Update invoice if exists
                if ($payment->invoice_id) {
                    Invoice::addPayment([
                        'invoice_id' => $payment->invoice_id,
                        'transaction_id' => $transactionID,
                        'payment_type' => 'Stripe',
                        'amount' => $payment->amount,
                        'receipt' => $payment->receipt_url,
                        'notes' => "Payment for {$payment->payment_type}",
                    ]);
                }

                return redirect()->route('tenant.payments.show', $payment->id)
                    ->with('success', 'Payment completed successfully.');
            } else {
                return redirect()->back()->with('error', 'Payment failed.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function paypalPayment(Request $request, $id)
    {
        $payment = TenantPayment::with(['tenant.properties'])->findOrFail($id);
        $propertyOwnerId = $payment->tenant->properties->parent_id ?? parentId();
        $settings = invoicePaymentSettings($propertyOwnerId);

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            if ($payment->is_recurring) {
                // Create recurring payment with PayPal
                $response = $this->createPayPalSubscription($payment, $settings);
            } else {
                // One-time payment
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('tenant.payments.paypal.status', [$payment->id, 'success']),
                        "cancel_url" => route('tenant.payments.paypal.status', [$payment->id, 'cancel']),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => $settings['CURRENCY'],
                                "value" => $payment->amount
                            ],
                            "description" => "Payment for {$payment->payment_type} - Tenant: {$payment->tenant->user->name}"
                        ]
                    ]
                ]);
            }

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

    public function paypalStatus(Request $request, $id, $status)
    {
        $payment = TenantPayment::findOrFail($id);

        if ($status == 'success') {
            try {
                $provider = new PayPalClient;
                $provider->setApiCredentials(config('paypal'));
                $provider->getAccessToken();

                $response = $provider->capturePaymentOrder($request['token']);

                if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                    $transactionID = uniqid('', true);
                    
                    $payment->update([
                        'payment_method' => 'paypal',
                        'transaction_id' => $transactionID,
                        'status' => 'completed',
                        'payment_date' => now(),
                        'receipt_url' => $response['links'][0]['href'] ?? null,
                    ]);

                    // Update invoice if exists
                    if ($payment->invoice_id) {
                        Invoice::addPayment([
                            'invoice_id' => $payment->invoice_id,
                            'transaction_id' => $transactionID,
                            'payment_type' => 'PayPal',
                            'amount' => $payment->amount,
                            'receipt' => $payment->receipt_url,
                            'notes' => "Payment for {$payment->payment_type}",
                        ]);
                    }

                    return redirect()->route('tenant.payments.show', $payment->id)
                        ->with('success', 'Payment completed successfully.');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Payment failed.');
    }

    public function bankTransferPayment(Request $request, $id)
    {
        $payment = TenantPayment::with(['tenant.properties'])->findOrFail($id);
        
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $transactionID = uniqid('', true);
            
            // Handle file upload
            if ($request->hasFile('receipt')) {
                $receiptFile = $request->file('receipt');
                $receiptName = time() . '_' . $receiptFile->getClientOriginalName();
                $receiptPath = $receiptFile->storeAs('receipts', $receiptName, 'public');
            }

            $payment->update([
                'payment_method' => 'bank_transfer',
                'transaction_id' => $transactionID,
                'status' => 'pending',
                'payment_date' => now(),
                'receipt_url' => $receiptPath ?? null,
                'notes' => $request->notes,
            ]);

            // Update invoice if exists
            if ($payment->invoice_id) {
                Invoice::addPayment([
                    'invoice_id' => $payment->invoice_id,
                    'transaction_id' => $transactionID,
                    'payment_type' => 'Bank Transfer',
                    'amount' => $payment->amount,
                    'receipt' => $receiptPath ?? '',
                    'notes' => $request->notes ?? "Payment for {$payment->payment_type}",
                ]);
            }

            return redirect()->route('tenant.payments.show', $payment->id)
                ->with('success', 'Bank transfer payment submitted successfully. It will be reviewed shortly.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancelRecurring($id)
    {
        $payment = TenantPayment::with(['tenant.properties'])->findOrFail($id);
        
        if (!$payment->is_recurring) {
            return redirect()->back()->with('error', 'This is not a recurring payment.');
        }

        try {
            if ($payment->stripe_subscription_id) {
                $propertyOwnerId = $payment->tenant->properties->parent_id ?? parentId();
                $settings = invoicePaymentSettings($propertyOwnerId);
                Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                $subscription = Stripe\Subscription::retrieve($payment->stripe_subscription_id);
                $subscription->cancel();
            }

            $payment->update([
                'status' => 'cancelled',
                'next_payment_date' => null,
            ]);

            return redirect()->back()->with('success', 'Recurring payment cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function createStripeSubscription($payment, $settings)
    {
        // Create customer if not exists
        $customer = Stripe\Customer::create([
            'email' => $payment->tenant->user->email,
            'name' => $payment->tenant->user->name,
        ]);

        // Create product
        $product = Stripe\Product::create([
            'name' => ucfirst($payment->payment_type) . ' Payment',
        ]);

        // Create price
        $price = Stripe\Price::create([
            'product' => $product->id,
            'unit_amount' => 100 * $payment->amount,
            'currency' => $settings['CURRENCY'],
            'recurring' => [
                'interval' => $payment->recurring_interval,
            ],
        ]);

        // Create subscription
        $subscription = Stripe\Subscription::create([
            'customer' => $customer->id,
            'items' => [
                ['price' => $price->id],
            ],
            'metadata' => [
                'payment_id' => $payment->id,
                'tenant_id' => $payment->tenant_id,
            ],
        ]);

        return $subscription;
    }

    private function createPayPalSubscription($payment, $settings)
    {
        // This would require PayPal's subscription API
        // For now, we'll handle it as a one-time payment
        return null;
    }

    private function getPaymentSettings()
    {
        // Get payment settings from the property owner
        $payment = TenantPayment::with(['tenant.properties'])->find(request()->route('id'));
        $propertyOwnerId = $payment->tenant->properties->parent_id ?? parentId();
        
        $settings = invoicePaymentSettings($propertyOwnerId);
        return [
            'CURRENCY' => $settings['CURRENCY'] ?? 'USD',
            'CURRENCY_SYMBOL' => $settings['CURRENCY_SYMBOL'] ?? '$',
            'STRIPE_KEY' => $settings['STRIPE_KEY'] ?? '',
            'STRIPE_SECRET' => $settings['STRIPE_SECRET'] ?? '',
            'STRIPE_PAYMENT' => $settings['STRIPE_PAYMENT'] ?? 'off',
            'paypal_payment' => $settings['paypal_payment'] ?? 'off',
            'paypal_client_id' => $settings['paypal_client_id'] ?? '',
            'paypal_secret_key' => $settings['paypal_secret_key'] ?? '',
            'paypal_mode' => $settings['paypal_mode'] ?? 'sandbox',
        ];
    }
} 