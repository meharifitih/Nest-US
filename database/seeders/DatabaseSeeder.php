<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DefaultDataUsersTableSeeder::class,
            AddressFieldsMigrationSeeder::class, // Migrate existing data to new address fields
            SampleDataSeeder::class, // Add sample data with new address fields
        ]);
    }
}
