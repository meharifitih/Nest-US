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
        'rent',
        'rent_type',
        'start_date',
        'end_date',
        'parent_id',
        'notes',
    ];

    public static $Types=[
        'fixed'=> 'Fixed',
        'percentage'=>'Percentage',
    ];
    public static $rentTypes = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'six_months' => '6 Months',
        'yearly' => 'Yearly'
    ];
    public function properties()
    {
        return $this->hasOne('App\Models\Property','id','property_id');
    }

    public function tenants()
    {
        return $this->hasOne('App\Models\Tenant', 'unit', 'id');
    }

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class, 'property_id');
    }
}
