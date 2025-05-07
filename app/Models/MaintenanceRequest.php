<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'unit_id',
        'issue_type',
        'maintainer_id',
        'status',
        'amount',
        'issue_attachment',
        'invoice',
        'notes',
        'parent_id',
        'request_date',
        'fixed_date',
        'parent_id',
    ];

    public static  $status = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];

    public function properties()
    {
        return $this->hasOne(Property::class, 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function types()
    {
        return $this->hasOne(Type::class, 'id', 'issue_type');
    }

    public function maintainers()
    {
        return $this->hasOne(User::class, 'id', 'maintainer_id');
    }

    public function tenetData()
    {
        return Tenant::where('property',$this->property_id)->where('unit',$this->unit_id)->first();
    }
}
