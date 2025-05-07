<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'title',
        'package_amount',
        'interval',
        'user_limit',
        'property_limit',
        'tenant_limit',
        'enabled_logged_history',
    ];

    public static $intervals = [
        'Monthly' => 'Monthly',
        'Quarterly' => 'Quarterly',
        'Yearly' => 'Yearly',
        'Unlimited' => 'Unlimited',
    ];

    public function couponCheck()
    {
        $packages = Coupon::whereRaw("? = ANY(string_to_array(applicable_packages, ','))", [$this->id])->count();
        return $packages;
    }

}
