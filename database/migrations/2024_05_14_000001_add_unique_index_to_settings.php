<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Drop the constraint if it exists (Postgres only)
        \DB::statement('ALTER TABLE settings DROP CONSTRAINT IF EXISTS settings_name_parent_id_unique;');
        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['name', 'parent_id']);
        });
    }

    public function down()
    {
        \DB::statement('ALTER TABLE settings DROP CONSTRAINT IF EXISTS settings_name_parent_id_unique;');
    }
}; 