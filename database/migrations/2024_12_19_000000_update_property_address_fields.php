<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // Add new standard address fields
            $table->string('country')->nullable()->after('city');
            $table->string('state')->nullable()->after('country');
            $table->string('zip_code')->nullable()->after('state');
            $table->text('address')->nullable()->after('zip_code');
            
            // Drop old Ethiopian-specific fields
            $table->dropColumn(['house_number', 'woreda', 'sub_city']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            // Restore old fields
            $table->string('house_number')->nullable()->after('location');
            $table->string('woreda')->nullable()->after('house_number');
            $table->text('sub_city')->nullable()->after('woreda');
            
            // Drop new fields
            $table->dropColumn(['country', 'state', 'zip_code', 'address']);
        });
    }
}; 