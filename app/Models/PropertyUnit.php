<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyUnit extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'bedroom',
        'property_id',
        'baths',
        'kitchen',
        'rent',
        'deposit_amount',
        'deposit_type',
        'late_fee_type',
        'late_fee_amount',
        'incident_receipt_amount',
        'rent_type',
        'rent_duration',
        'start_date',
        'end_date',
        'payment_due_date',
        'parent_id',
        'notes',
    ];

    public static $Types=[
        'fixed'=> 'Fixed',
        'percentage'=>'Percentage',
    ];
    public static $rentTypes=[
         'monthly'=> 'Monthly',
        'yearly'=>'Yearly',
        'custom'=>'Custom',
    ];
    public function properties()
    {
        return $this->hasOne('App\Models\Property','id','property_id');
    }

    public function tenants()
    {
        return Tenant::where('unit',$this->id)->first();
    }

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class, 'property_id');
    }
}
