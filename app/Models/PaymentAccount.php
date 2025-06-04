<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $fillable = [
        'user_id',
        'account_type', // 'cbe' or 'telebirr'
        'account_number',
        'account_name',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 