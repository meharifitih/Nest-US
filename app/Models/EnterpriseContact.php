<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnterpriseContact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'property_limit',
        'unit_limit',
        'interval',
        'message',
        'is_contacted'
    ];
} 