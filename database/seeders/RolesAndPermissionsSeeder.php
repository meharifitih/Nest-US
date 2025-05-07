<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'manage users',
            'create users',
            'edit users',
            'delete users',
            'view users',
            
            // Role Management
            'manage roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view roles',
            
            // Property Management
            'manage properties',
            'create properties',
            'edit properties',
            'delete properties',
            'view properties',
            
            // Tenant Management
            'manage tenants',
            'create tenants',
            'edit tenants',
            'delete tenants',
            'view tenants',
            
            // Lease Management
            'manage leases',
            'create leases',
            'edit leases',
            'delete leases',
            'view leases',
            
            // Maintenance Management
            'manage maintenance',
            'create maintenance',
            'edit maintenance',
            'delete maintenance',
            'view maintenance',
            
            // Payment Management
            'manage payments',
            'create payments',
            'edit payments',
            'delete payments',
            'view payments',
            
            // Reports
            'view reports',
            'generate reports',
            
            // Settings
            'manage settings',
            'view settings',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
} 