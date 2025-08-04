<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponHistory;
use App\Models\PackageTransaction;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Stripe;

class SubscriptionController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage pricing packages')) {
            $subscriptions = Subscription::get();
            return view('subscription.index', compact('subscriptions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $intervals = Subscription::$intervals;
        return view('subscription.create', compact('intervals'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create pricing packages')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'package_amount' => 'required',
                    'interval' => 'required',
                    'user_limit' => 'required',
                    'property_limit' => 'required',
                    'tenant_limit' => 'required',
                    'min_units' => 'required|integer|min:0',
                    'max_units' => 'required|integer|min:0',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $subscription = new Subscription();
            $subscription->title = $request->title;
            $subscription->interval = $request->interval;
            $subscription->package_amount = $request->package_amount;
            $subscription->user_limit = $request->user_limit;
            $subscription->property_limit = $request->property_limit;
            $subscription->tenant_limit = $request->tenant_limit;
            $subscription->min_units = $request->min_units;
            $subscription->max_units = $request->max_units;
            $subscription->enabled_logged_history = isset($request->enabled_logged_history) ? 1 : 0;
            $subscription->save();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show($id)
    {
        if (\Auth::user()->type == 'owner' || \Auth::user()->can('buy pricing packages')) {
            try {
                \Log::info('Subscription show request', [
                    'encrypted_id' => $id,
                    'user_id' => \Auth::id(),
                    'user_type' => \Auth::user()->type
                ]);
                
                $decryptedId = \Illuminate\Support\Facades\Crypt::decrypt($id);
                \Log::info('Decrypted ID', ['decrypted_id' => $decryptedId]);
                
                $subscription = Subscription::find($decryptedId);
                
                if (!$subscription) {
                    \Log::error('Subscription not found', ['decrypted_id' => $decryptedId]);
                    return redirect()->route('subscriptions.index')->with('error', __('Subscription not found.'));
                }
                
                \Log::info('Subscription found', [
                    'subscription_id' => $subscription->id,
                    'subscription_title' => $subscription->title
                ]);
                
            $paymentAccounts = \App\Models\PaymentAccount::where('is_active', 1)->get();
            $settings = subscriptionPaymentSettings();
            return view('subscription.show', compact('subscription', 'settings', 'paymentAccounts'));
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                \Log::error('Failed to decrypt subscription ID', [
                    'encrypted_id' => $id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('subscriptions.index')->with('error', __('Invalid subscription link.'));
            } catch (\Exception $e) {
                \Log::error('Error in subscription show', [
                    'encrypted_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('subscriptions.index')->with('error', __('An error occurred while loading the subscription.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(subscription $subscription)
    {
        $intervals = Subscription::$intervals;

        return view('subscription.edit', compact('intervals', 'subscription'));
    }

    public function update(Request $request, subscription $subscription)
    {
        if (\Auth::user()->can('edit pricing packages')) {
            $validator = \Validator::make(
                $request->all(), [
                    'title' => 'required',
                    'package_amount' => 'required',
                    'interval' => 'required',
                    'user_limit' => 'required',
                    'property_limit' => 'required',
                    'tenant_limit' => 'required',
                    'min_units' => 'required|integer|min:0',
                    'max_units' => 'required|integer|min:0',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $subscription->title = $request->title;
            $subscription->interval = $request->interval;
            $subscription->package_amount = $request->package_amount;
            $subscription->user_limit = $request->user_limit;
            $subscription->property_limit = $request->property_limit;
            $subscription->tenant_limit = $request->tenant_limit;
            $subscription->min_units = $request->min_units;
            $subscription->max_units = $request->max_units;
            $subscription->enabled_logged_history = isset($request->enabled_logged_history) ? 1 : 0;
            $subscription->save();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(subscription $subscription)
    {
        if (\Auth::user()->can('delete pricing packages')) {
            $subscription->delete();

            return redirect()->route('subscriptions.index')->with('success', __('Subscription successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {
        if (\Auth::user()->can('manage pricing transation')) {
            $users = \App\Models\User::all();
            $subscriptions = \App\Models\Subscription::all();
            $query = \App\Models\PackageTransaction::with(['subscription', 'user']);
            if (\Auth::user()->type != 'super admin') {
                $query->where('user_id', \Auth::user()->id);
            }
            $query->when($request->user_id, function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
            $query->when($request->subscription_id, function($q) use ($request) {
                $q->where('subscription_id', $request->subscription_id);
            });
            $query->when($request->payment_type, function($q) use ($request) {
                $q->where('payment_type', $request->payment_type);
            });
            $query->when($request->payment_status, function($q) use ($request) {
                $q->where('payment_status', $request->payment_status);
            });
            $query->when($request->date, function($q) use ($request) {
                $q->whereDate('created_at', $request->date);
            });
            $query->when($request->amount, function($q) use ($request) {
                $q->where('amount', $request->amount);
            });
            $transactions = $query->orderBy('created_at', 'DESC')->get();
            $settings = settings();
            return view('subscription.transaction', compact('transactions', 'settings', 'users', 'subscriptions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function stripePayment(Request $request, $ids)
    {
        \Log::info('Stripe payment endpoint hit', [
            'request_method' => $request->method(),
            'request_url' => $request->url(),
            'request_data' => $request->all(),
            'user_id' => \Auth::id()
        ]);
        
        if (\Auth::user()->can('buy pricing packages')) {
            $settings = subscriptionPaymentSettings();
            
            // Debug settings
            \Log::info('Payment settings loaded', [
                'stripe_payment' => $settings['STRIPE_PAYMENT'] ?? 'not set',
                'stripe_key' => !empty($settings['STRIPE_KEY']) ? 'set' : 'not set',
                'stripe_secret' => !empty($settings['STRIPE_SECRET']) ? 'set' : 'not set',
                'all_settings' => $settings
            ]);
            
            $authUser = \Auth::user();
            $id = \Illuminate\Support\Facades\Crypt::decrypt($ids);
            $subscription = Subscription::find($id);
            if ($subscription) {
                try {
                    $amount = Coupon::couponApply($id, $request->coupon);
                    
                    // Ensure amount is always defined
                    if (!isset($amount) || $amount === null) {
                        $amount = $subscription->package_amount;
                    }
                    
                    // Debug logging
                    \Log::info('Stripe payment request', [
                        'request_data' => $request->all(),
                        'subscription_id' => $id,
                        'stripe_token' => $request->stripeToken ? 'present' : 'missing',
                        'stripe_token_value' => $request->stripeToken,
                        'stripe_token_length' => $request->stripeToken ? strlen($request->stripeToken) : 0,
                        'amount' => $amount,
                        'currency' => strtolower($settings['CURRENCY']),
                        'stripe_secret_length' => strlen($settings['STRIPE_SECRET']),
                        'stripe_secret_key' => substr($settings['STRIPE_SECRET'], 0, 10) . '...',
                        'request_method' => $request->method(),
                        'request_url' => $request->url(),
                        'all_request_headers' => $request->headers->all()
                    ]);
                    $packageTransId = uniqid('', true);
                    if ($amount > 0) {
                        // Check if Stripe token is present
                        if (empty($request->stripeToken)) {
                            \Log::error('Stripe token is missing', [
                                'request_data' => $request->all(),
                                'subscription_id' => $id
                            ]);
                            return redirect()->route('subscriptions.index')->with('error', __('Stripe token is missing. Please try again.'));
                        }
                        
                                                \Log::info('Creating Stripe charge', [
                            'amount' => 100 * $amount,
                            'currency' => strtolower($settings['CURRENCY']),
                            'stripe_token' => $request->stripeToken,
                            'description' => "Subscription - " . $subscription->title,
                            'metadata' => ["package_transaction_id" => $packageTransId]
                        ]);
                        
                        \Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                        $data = \Stripe\Charge::create([
                            "amount" => 100 * $amount,
                            "currency" => strtolower($settings['CURRENCY']),
                            "source" => $request->stripeToken,
                            "description" => "Subscription - " . $subscription->title,
                            "metadata" => ["package_transaction_id" => $packageTransId],
                        ]);
                        
                        \Log::info('Stripe charge created successfully', [
                            'charge_id' => $data->id,
                            'status' => $data->status,
                            'amount' => $data->amount,
                            'currency' => $data->currency
                        ]);
                    } else {
                        $data['amount_refunded'] = 0;
                        $data['failure_code'] = '';
                        $data['paid'] = 1;
                        $data['captured'] = 1;
                        $data['status'] = 'succeeded';
                    }

                    if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {

                        if ($data['status'] == 'succeeded') {
                            $data['holder_name'] = $request->name;
                            $data['subscription_id'] = $subscription->id;
                            $data['amount'] = $amount;
                            $data['subscription_transactions_id'] = $packageTransId;
                            $data['payment_type'] = 'Stripe';
                            $data['status'] = 'pending'; // Set status to pending for admin approval
                            $data['payment_status'] = 'pending';
                            $transaction = PackageTransaction::transactionData($data);

                            if ($subscription->couponCheck() > 0 && !empty($request->coupon)) {
                                $couhis['coupon'] = $request->coupon;
                                $couhis['package'] = $subscription->id;
                                CouponHistory::couponData($couhis);
                            }

                            // Save Stripe receipt URL - Stripe doesn't always provide receipt URL in charge response
                            // We'll create a receipt URL using the charge ID
                            $receiptUrl = null;
                            if (isset($data['id'])) {
                                $receiptUrl = "https://dashboard.stripe.com/payments/" . $data['id'];
                            }
                            $transaction->receipt = $receiptUrl;
                            $transaction->save();

                            \Log::info('Stripe payment successful - transaction created', [
                                'transaction_id' => $transaction->id,
                                'subscription_id' => $subscription->id,
                                'amount' => $amount,
                                'status' => 'pending',
                                'receipt_url' => $data['receipt']['url'] ?? null
                            ]);

                            return redirect()->route('account.pending')->with('success', __('Payment received successfully! Your account is pending admin approval.'));
                        } else {
                            return redirect()->route('subscriptions.index')->with('error', __('Your payment failed.'));
                        }
                    } else {
                        return redirect()->route('subscriptions.index')->with('error', __('Transaction failed.'));
                    }
                } catch (\Exception $e) {
                    \Log::error('Stripe payment error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'request_data' => $request->all(),
                        'subscription_id' => $id,
                        'stripe_token_present' => $request->has('stripeToken'),
                        'stripe_token_value' => $request->stripeToken,
                        'amount' => $amount,
                        'currency' => strtolower($settings['CURRENCY']),
                        'stripe_secret_length' => strlen($settings['STRIPE_SECRET']),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    return redirect()->route('subscriptions.index')->with('error', __('Payment processing failed. Please try again.'));
                }
            } else {
                return redirect()->route('subscriptions.index')->with('error', __('Subscription not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function subscribe($id)
    {
        $user = \Auth::user();
        $subscription = Subscription::findOrFail($id);

        // Only allow for free packages
        if ($subscription->package_amount > 0) {
            return redirect()->back()->with('error', __('This package requires payment.'));
        }

        // Assign subscription to user
        $user->subscription = $subscription->id;
        $user->subscription_expire_date = null; // or set as needed
        $user->save();

        return redirect()->route('subscriptions.index')->with('success', __('Subscription activated successfully.'));
    }

    public function subscriptionBankTransferAction($id, $status)
    {
        // ... existing code ...

        if ($status == 'approve') {
            // Send WhatsApp notification to owner
            $owner = User::find($subscription->user_id);
            if ($owner && $owner->phone) {
                $message = "🔔 *System Notification*\n\n";
                $message .= "Your subscription payment has been approved!\n";
                $message .= "Plan: {$subscription->name}\n";
                $message .= "Amount: " . priceFormat($subscription->package_amount) . "\n";
                $message .= "Status: Active\n\n";
                $message .= "Best regards,\n" . settings()['company_name'];

                $this->whatsappService->sendMessage($owner->phone, $message);
            }
        }

        // ... rest of the existing code ...
    }
}
