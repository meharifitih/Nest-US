<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnifiedExcelUpload extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'property_id',
        'file_name',
        'original_name',
        'status',
        'error_log',
        'imported_count',
        'error_count',
        'parent_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
} 