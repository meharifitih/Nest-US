<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UnifiedImport implements WithMultipleSheets
{
    protected $propertyId;

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function sheets(): array
    {
        return [
            'Units' => new UnitImport($this->propertyId),
            'Tenants' => new TenantsImport($this->propertyId),
        ];
    }
} 