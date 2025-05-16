<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Tenant;
use App\Models\PropertyUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Notifications\PasswordChangeNotification;
use Illuminate\Support\Str;

class TenantsImport implements ToModel, WithHeadingRow
{
    protected $propertyId;

    public function __construct($propertyId)
    {
        $this->propertyId = $propertyId;
    }

    public function model(array $row)
    {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'phone_number', 'unit_name'];
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Validate email format
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format: {$row['email']}");
        }

        // Check if email already exists
        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("Email already exists: {$row['email']}");
        }

        // Find or create user role
        $userRole = Role::where('name', 'tenant')->first();
        if (!$userRole) {
            throw new \Exception('Tenant role not found');
        }

        // Generate a random password
        $password = Str::random(8);

        // Create user
        $user = new User();
        $user->first_name = $row['first_name'] ?? '';
        $user->last_name = $row['last_name'] ?? '';
        $user->email = $row['email'] ?? '';
        $user->password = Hash::make($password);
        $user->phone_number = $row['phone_number'] ?? '';
        $user->type = $userRole->name;
        $user->email_verified_at = now();
        $user->profile = 'avatar.png';
        $user->lang = 'english';
        $user->parent_id = parentId();
        $user->save();
        $user->assignRole($userRole);

        // Send password notification
        $user->notify(new PasswordChangeNotification($password));

        // Find or create unit with all info
        $unit = PropertyUnit::firstOrCreate(
            [
                'property_id' => $this->propertyId,
                'name' => $row['unit_name'] ?? ''
            ],
            [
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
            ]
        );

        // Create tenant
        $tenant = new Tenant();
        $tenant->user_id = $user->id;
        $tenant->family_member = $row['family_member'] ?? 0;
        $tenant->sub_city = $row['sub_city'] ?? '';
        $tenant->woreda = $row['woreda'] ?? '';
        $tenant->house_number = $row['house_number'] ?? '';
        $tenant->location = $row['location'] ?? '';
        $tenant->city = $row['city'] ?? '';
        $tenant->property = $this->propertyId;
        $tenant->unit = $unit->id;
        $tenant->lease_start_date = $row['lease_start_date'] ?? null;
        $tenant->lease_end_date = $row['lease_end_date'] ?? null;
        $tenant->parent_id = parentId();
        $tenant->save();

        return $tenant;
    }
} 