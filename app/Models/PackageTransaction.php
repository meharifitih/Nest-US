<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'subscription_transactions_id',
        'amount',
        'transaction_id',
        'payment_status',
        'payment_type',
        'holder_name',
        'card_number',
        'card_expiry_month',
        'card_expiry_year',
        'receipt',
        'payment_screenshot',
        'receipt_number',
        'status',
        'rejection_reason',
    ];

    public static function transactionData($data)
    {
        $transaction = new PackageTransaction();
        $transaction->user_id = $data['user_id'] ?? \Auth::user()->id;
        $transaction->subscription_id = $data['subscription_id'];
        $transaction->subscription_transactions_id = $data['subscription_transactions_id'];
        $transaction->amount = $data['amount'];
        $transaction->transaction_id = $data['transaction_id'] ?? null;
        $transaction->payment_status = $data['payment_status'] ?? 'pending';
        $transaction->payment_type = $data['payment_type'] ?? 'manual';
        $transaction->holder_name = $data['holder_name'] ?? null;
        $transaction->card_number = $data['card_number'] ?? null;
        $transaction->card_expiry_month = $data['card_expiry_month'] ?? null;
        $transaction->card_expiry_year = $data['card_expiry_year'] ?? null;
        $transaction->receipt = $data['receipt'] ?? null;
        $transaction->payment_screenshot = $data['payment_screenshot'] ?? null;
        $transaction->receipt_number = $data['receipt_number'] ?? null;
        $transaction->status = $data['status'] ?? 'pending';
        $transaction->rejection_reason = $data['rejection_reason'] ?? null;
        $transaction->save();

        return $transaction;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function subscription()
    {
        return $this->belongsTo('App\Models\Subscription', 'subscription_id');
    }
}
