<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddressFieldsMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the old fields still exist (before migration)
        if (Schema::hasColumn('properties', 'house_number') && 
            Schema::hasColumn('properties', 'woreda') && 
            Schema::hasColumn('properties', 'sub_city')) {
            
            // Migrate existing property data
            $properties = DB::table('properties')->get();
            
            foreach ($properties as $property) {
                // Map old fields to new fields
                $newAddressData = [
                    'country' => 'Ethiopia', // Default country for existing data
                    'state' => $property->woreda ?? 'Addis Ababa', // Map woreda to state
                    'zip_code' => $property->sub_city ?? '1000', // Map sub_city to zip_code
                    'address' => $property->house_number ?? '', // Map house_number to address
                ];
                
                // Update the property with new address fields
                DB::table('properties')
                    ->where('id', $property->id)
                    ->update($newAddressData);
            }
            
            $this->command->info('Migrated ' . $properties->count() . ' properties to new address fields.');
        }
        
        // Check if the old fields still exist in tenants table
        if (Schema::hasColumn('tenants', 'house_number') && 
            Schema::hasColumn('tenants', 'woreda') && 
            Schema::hasColumn('tenants', 'sub_city')) {
            
            // Migrate existing tenant data
            $tenants = DB::table('tenants')->get();
            
            foreach ($tenants as $tenant) {
                // Map old fields to new fields
                $newAddressData = [
                    'country' => 'Ethiopia', // Default country for existing data
                    'state' => $tenant->woreda ?? 'Addis Ababa', // Map woreda to state
                    'zip_code' => $tenant->sub_city ?? '1000', // Map sub_city to zip_code
                    'address' => $tenant->house_number ?? '', // Map house_number to address
                ];
                
                // Update the tenant with new address fields
                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update($newAddressData);
            }
            
            $this->command->info('Migrated ' . $tenants->count() . ' tenants to new address fields.');
        }
    }
} 