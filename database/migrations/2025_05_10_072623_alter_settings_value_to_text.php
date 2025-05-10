<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE settings ALTER COLUMN value TYPE TEXT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE settings ALTER COLUMN value TYPE VARCHAR(191)');
    }
};
