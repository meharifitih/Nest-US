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
        // Use raw SQL to add the constraint only if it doesn't exist (Postgres safe)
        \DB::statement("DO $$
        BEGIN
            IF NOT EXISTS (
                SELECT 1 FROM pg_constraint WHERE conname = 'property_units_name_property_id_unique'
            ) THEN
                ALTER TABLE property_units ADD CONSTRAINT property_units_name_property_id_unique UNIQUE (name, property_id);
            END IF;
        END
        $$;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_units', function (Blueprint $table) {
            $table->dropUnique('property_units_name_property_id_unique');
        });
    }
}; 