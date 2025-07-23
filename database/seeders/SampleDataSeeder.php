<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample properties with new address fields
        $properties = [
            [
                'name' => 'Sunset Apartments',
                'description' => 'Modern apartment complex with excellent amenities',
                'type' => 'apartment',
                'location' => 'Near City Center',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1000',
                'address' => '123 Bole Road, Bole District',
                'parent_id' => 1,
                'is_active' => 1,
            ],
            [
                'name' => 'Downtown Commercial Plaza',
                'description' => 'Premium commercial space in the heart of the city',
                'type' => 'commercial',
                'location' => 'City Center',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1001',
                'address' => '456 Churchill Road, Piazza',
                'parent_id' => 1,
                'is_active' => 1,
            ],
            [
                'name' => 'Green Valley Residences',
                'description' => 'Peaceful residential area with garden views',
                'type' => 'apartment',
                'location' => 'Suburban Area',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1002',
                'address' => '789 Kazanchis Street, Kazanchis',
                'parent_id' => 1,
                'is_active' => 1,
            ],
        ];

        foreach ($properties as $propertyData) {
            $property = Property::create($propertyData);
            
            // Create sample units for each property
            $units = [
                [
                    'name' => 'Unit 101',
                    'bedroom' => 2,
                    'baths' => 1,
                    'rent' => 8000,
                    'rent_type' => 'monthly',
                    'notes' => 'Corner unit with balcony',
                ],
                [
                    'name' => 'Unit 102',
                    'bedroom' => 1,
                    'baths' => 1,
                    'rent' => 6000,
                    'rent_type' => 'monthly',
                    'notes' => 'Studio apartment',
                ],
                [
                    'name' => 'Unit 103',
                    'bedroom' => 3,
                    'baths' => 2,
                    'rent' => 12000,
                    'rent_type' => 'monthly',
                    'notes' => 'Family apartment with parking',
                ],
            ];

            foreach ($units as $unitData) {
                $unitData['property_id'] = $property->id;
                $unitData['parent_id'] = 1;
                PropertyUnit::create($unitData);
            }
        }

        // Create sample tenants with new address fields
        $tenantUsers = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone_number' => '+251912345678',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1000',
                'address' => '123 Bole Road, Bole District',
                'location' => 'Near City Center',
                'family_member' => 3,
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone_number' => '+251987654321',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1001',
                'address' => '456 Churchill Road, Piazza',
                'location' => 'City Center',
                'family_member' => 2,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'phone_number' => '+251945678901',
                'country' => 'Ethiopia',
                'state' => 'Addis Ababa',
                'city' => 'Addis Ababa',
                'zip_code' => '1002',
                'address' => '789 Kazanchis Street, Kazanchis',
                'location' => 'Suburban Area',
                'family_member' => 4,
            ],
        ];

        $tenantRole = Role::where('name', 'tenant')->first();

        foreach ($tenantUsers as $userData) {
            // Create user
            $user = User::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
                'phone_number' => $userData['phone_number'],
                'type' => 'tenant',
                'email_verified_at' => now(),
                'profile' => 'avatar.png',
                'lang' => 'english',
                'parent_id' => 1,
                'is_active' => 1,
            ]);

            $user->assignRole($tenantRole);

            // Create tenant record
            Tenant::create([
                'user_id' => $user->id,
                'family_member' => $userData['family_member'],
                'country' => $userData['country'],
                'state' => $userData['state'],
                'city' => $userData['city'],
                'zip_code' => $userData['zip_code'],
                'address' => $userData['address'],
                'location' => $userData['location'],
                'property' => rand(1, 3), // Random property assignment
                'unit' => rand(1, 9), // Random unit assignment
                'lease_start_date' => now()->subMonths(rand(1, 6)),
                'lease_end_date' => now()->addMonths(rand(6, 12)),
                'parent_id' => 1,
            ]);
        }

        $this->command->info('Sample data created successfully with new address fields!');
    }
} 