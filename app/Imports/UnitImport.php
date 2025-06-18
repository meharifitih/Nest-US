<?php

namespace App\Imports;

use App\Models\PropertyUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnitImport implements ToModel, WithHeadingRow
{
    protected $propertyId;

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function model(array $row)
    {
        // Only handle unit fields
        return new PropertyUnit([
            'property_id' => $this->propertyId,
            'name' => $row['name'] ?? '',
            'bedroom' => $row['bedroom'] ?? 0,
            'kitchen' => $row['kitchen'] ?? 0,
            'baths' => $row['baths'] ?? 0,
            'rent' => $row['rent'] ?? 0,
            'rent_type' => $row['rent_type'] ?? 'monthly',
            'deposit_type' => $row['deposit_type'] ?? 'fixed',
            'deposit_amount' => $row['deposit_amount'] ?? 0,
            'late_fee_type' => $row['late_fee_type'] ?? 'fixed',
            'late_fee_amount' => $row['late_fee_amount'] ?? 0,
            'incident_receipt_amount' => $row['incident_receipt_amount'] ?? 0,
            'notes' => $row['notes'] ?? '',
            'parent_id' => parentId(),
        ]);
    }
} 