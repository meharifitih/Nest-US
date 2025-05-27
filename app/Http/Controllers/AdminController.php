<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PackageTransaction;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function approvePayment($id)
    {
        try {
            // Find the payment transaction with user relationship
            $payment = PackageTransaction::with(['user', 'subscription'])->findOrFail($id);
            
            // Update the payment status to approved and payment_status to completed
            $payment->status = 'approved';
            $payment->payment_status = 'completed';
            
            // Update the user's approval status
            $user = $payment->user;
            if (!$user) {
                \Log::error('User not found for payment', ['payment_id' => $payment->id]);
                return redirect()->route('users.index')->with('error', 'User not found for this payment.');
            }

            $user->approval_status = 'approved';
            $user->is_active = 1;
            $user->rejection_reason = null;
            
            // Assign the subscription to the user and set correct expiry date
            $user->subscription = $payment->subscription_id;
            assignManuallySubscription($payment->subscription_id, $user->id);
            
            // Save changes
            $payment->save();
            $user->save();

            // Fetch user's phone number from their profile
            $userProfile = User::where('id', $user->id)
                             ->whereNotNull('phone_number')
                             ->where('phone_number', '!=', '')
                             ->first();

            // Send WhatsApp notification to owner if phone exists
            if ($userProfile && $userProfile->phone_number) {
                // Format phone number
                $phone = preg_replace('/[^0-9+]/', '', $userProfile->phone_number);
                if (substr($phone, 0, 1) !== '+') {
                    $phone = '+' . $phone;
                }

                \Log::info('Found user phone number', [
                    'user_id' => $user->id,
                    'phone' => $phone
                ]);

                $message = "🔔 *System Notification*\n\n";
                $message .= "Your subscription payment has been approved!\n\n";
                $message .= "📋 *Subscription Details:*\n";
                $message .= "Plan: " . ($payment->subscription ? $payment->subscription->title : 'N/A') . "\n";
                $message .= "Amount: " . priceFormat($payment->amount) . "\n";
                $message .= "Payment ID: {$payment->id}\n";
                $message .= "Status: Active\n\n";
                $message .= "Your account is now active and you can start managing your properties.\n\n";
                $message .= "Best regards,\n" . settings()['company_name'];

                $response = $this->whatsappService->sendMessage($phone, $message);
                
                if ($response['status'] === 'error') {
                    \Log::error('Failed to send WhatsApp notification', [
                        'error' => $response['message'],
                        'user_id' => $user->id,
                        'phone' => $phone
                    ]);
                } else {
                    \Log::info('WhatsApp notification sent successfully', [
                        'user_id' => $user->id,
                        'phone' => $phone
                    ]);
                }
            } else {
                \Log::warning('User has no phone number in profile', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }

            return redirect()->route('users.index')->with('success', 'Payment approved successfully.');
        } catch (\Exception $e) {
            \Log::error('Error in approvePayment', [
                'error' => $e->getMessage(),
                'payment_id' => $id
            ]);
            return redirect()->route('users.index')->with('error', 'Error approving payment: ' . $e->getMessage());
        }
    }

    public function rejectPayment($id)
    {
        // Find the payment transaction
        $payment = PackageTransaction::findOrFail($id);
        
        // Update the payment status to rejected
        $payment->status = 'rejected';
        $payment->payment_status = 'rejected';
        
        // Optionally, set a rejection reason
        $user = $payment->user;
        $user->approval_status = 'rejected';
        $user->is_active = 0;
        $user->rejection_reason = 'Payment rejected by admin.';
        
        // Save changes
        $payment->save();
        $user->save();

        // Send WhatsApp notification to owner
        if ($user && $user->phone) {
            $message = "🔔 *System Notification*\n\n";
            $message .= "Your payment has been rejected by the administrator.\n";
            $message .= "Payment ID: {$payment->id}\n";
            $message .= "Amount: " . priceFormat($payment->amount) . "\n";
            $message .= "Status: Rejected\n";
            $message .= "Reason: {$request->rejection_reason}\n\n";
            $message .= "Please contact support for more information.\n\n";
            $message .= "Best regards,\n" . settings()['company_name'];

            $this->whatsappService->sendMessage($user->phone, $message);
        }

        return redirect()->route('users.index')->with('error', 'Payment rejected and user notified.');
    }

    public function showPendingPayments()
    {
        $pendingPayments = PackageTransaction::where('status', 'pending')->with('user')->get();
        return view('admin.payments.pending', compact('pendingPayments'));
    }
} 