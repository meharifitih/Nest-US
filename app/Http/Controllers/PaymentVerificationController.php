<?php

namespace App\Http\Controllers;

use App\Models\PackageTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentVerificationController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('manage payment verification')) {
            $transactions = PackageTransaction::where('payment_type', 'manual')
                ->where('status', 'pending')
                ->with(['user', 'subscription'])
                ->get();
            return view('payment_verification.index', compact('transactions'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function uploadScreenshot(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'payment_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $user = \Auth::user();
        $subscription = \App\Models\Subscription::find($request->subscription_id);
        
        if (!$subscription) {
            return response()->json(['error' => __('Subscription not found.')], 404);
        }

        $packageTransId = uniqid('', true);
        $data = [
            'subscription_id' => $subscription->id,
            'subscription_transactions_id' => $packageTransId,
            'amount' => $subscription->package_amount,
            'payment_type' => 'manual',
            'status' => 'pending',
        ];

        if ($request->hasFile('payment_screenshot')) {
            $file = $request->file('payment_screenshot');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('payment_screenshots', $filename, 'public');
            $data['payment_screenshot'] = $filename;
        }

        $transaction = PackageTransaction::transactionData($data);

        return response()->json([
            'success' => true,
            'message' => __('Payment screenshot uploaded successfully. Waiting for admin approval.'),
            'transaction' => $transaction
        ]);
    }

    public function approve($id)
    {
        if (\Auth::user()->can('manage payment verification')) {
            $transaction = PackageTransaction::find($id);
            if (!$transaction) {
                return redirect()->back()->with('error', __('Transaction not found.'));
            }

            $transaction->status = 'approved';
            $transaction->payment_status = 'success';
            $transaction->save();

            // Approve the user
            $user = $transaction->user;
            $user->approval_status = 'approved';
            $user->is_active = 1;
            $user->rejection_reason = null;
            $user->save();

            $assignPlan = assignManuallySubscription($transaction->subscription_id, $transaction->user_id);
            if ($assignPlan['is_success']) {
                return redirect()->back()->with('success', __('Payment approved and subscription activated successfully.'));
            } else {
                return redirect()->back()->with('error', __($assignPlan['error']));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function reject(Request $request, $id)
    {
        if (\Auth::user()->can('manage payment verification')) {
            $validator = \Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $transaction = PackageTransaction::find($id);
            if (!$transaction) {
                return redirect()->back()->with('error', __('Transaction not found.'));
            }

            $transaction->status = 'rejected';
            $transaction->rejection_reason = $request->rejection_reason;
            $transaction->save();

            // Reject the user
            $user = $transaction->user;
            $user->approval_status = 'rejected';
            $user->is_active = 0;
            $user->rejection_reason = $request->rejection_reason;
            $user->save();

            return redirect()->back()->with('success', __('Payment rejected successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
} 