<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hoa extends Model
{
    protected $fillable = [
        'property_id',
        'unit_id',
        'hoa_type_id',
        'amount',
        'frequency',
        'status',
        'due_date',
        'paid_date',
        'description',
        'created_by',
        'receipt',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'paid_date' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\PropertyUnit::class, 'unit_id');
    }

    public function hoaType()
    {
        return $this->belongsTo(\App\Models\Type::class, 'hoa_type_id');
    }

    public function getTenant()
    {
        return $this->unit && $this->unit->tenants ? $this->unit->tenants : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 