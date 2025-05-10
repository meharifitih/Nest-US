<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Tenant;
use App\Models\PropertyUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantsImport implements ToModel, WithHeadingRow
{
    protected $propertyId;

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function model(array $row)
    {
        // Find or create user role
        $userRole = Role::where('name', 'tenant')->first();
        if (!$userRole) {
            throw new \Exception('Tenant role not found');
        }

        // Create user
        $user = new User();
        $user->first_name = $row['first_name'] ?? '';
        $user->last_name = $row['last_name'] ?? '';
        $user->email = $row['email'] ?? '';
        $user->password = Hash::make($row['password'] ?? 'password123');
        $user->phone_number = $row['phone_number'] ?? '';
        $user->type = $userRole->name;
        $user->email_verified_at = now();
        $user->profile = 'avatar.png';
        $user->lang = 'english';
        $user->parent_id = parentId();
        $user->save();
        $user->assignRole($userRole);

        // Find property unit
        $unit = PropertyUnit::where('property_id', $this->propertyId)
            ->where('name', $row['unit_name'] ?? '')
            ->first();

        if (!$unit) {
            throw new \Exception('Unit not found: ' . ($row['unit_name'] ?? ''));
        }

        // Create tenant
        $tenant = new Tenant();
        $tenant->user_id = $user->id;
        $tenant->family_member = $row['family_member'] ?? 0;
        $tenant->country = $row['country'] ?? '';
        $tenant->state = $row['state'] ?? '';
        $tenant->city = $row['city'] ?? '';
        $tenant->zip_code = $row['zip_code'] ?? '';
        $tenant->address = $row['address'] ?? '';
        $tenant->property = $this->propertyId;
        $tenant->unit = $unit->id;
        $tenant->lease_start_date = $row['lease_start_date'] ?? null;
        $tenant->lease_end_date = $row['lease_end_date'] ?? null;
        $tenant->parent_id = parentId();
        $tenant->save();

        return $tenant;
    }
} 