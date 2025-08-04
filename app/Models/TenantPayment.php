<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount',
        'payment_type',
        'payment_method',
        'transaction_id',
        'status',
        'payment_date',
        'due_date',
        'is_recurring',
        'recurring_interval',
        'next_payment_date',
        'stripe_subscription_id',
        'paypal_subscription_id',
        'receipt_url',
        'notes',
        'parent_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date' => 'date',
        'next_payment_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public static $status = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
    ];

    public static $payment_methods = [
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
    ];

    public static $recurring_intervals = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'badge-warning',
            'processing' => 'badge-info',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            'cancelled' => 'badge-secondary',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }
} 