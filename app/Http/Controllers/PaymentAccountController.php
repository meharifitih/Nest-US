<?php

namespace App\Http\Controllers;

use App\Models\PaymentAccount;
use Illuminate\Http\Request;

class PaymentAccountController extends Controller
{
    public function index()
    {
        $accounts = PaymentAccount::where('user_id', auth()->id())->get();
        return view('payment_accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'account_type' => 'required|in:cbe,telebirr',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        PaymentAccount::create([
            'user_id' => auth()->id(),
            'account_type' => $request->account_type,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
        ]);

        return redirect()->back()->with('success', __('Payment account added successfully.'));
    }

    public function destroy($id)
    {
        $account = PaymentAccount::where('user_id', auth()->id())->findOrFail($id);
        $account->delete();
        return redirect()->back()->with('success', __('Payment account deleted successfully.'));
    }
} 