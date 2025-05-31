<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PackageTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
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

        return redirect()->route('users.index')->with('error', 'Payment rejected and user notified.');
    }

    public function showPendingPayments()
    {
        $pendingPayments = PackageTransaction::where('status', 'pending')->with('user')->get();
        return view('admin.payments.pending', compact('pendingPayments'));
    }
} 