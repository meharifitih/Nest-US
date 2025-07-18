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
        Schema::table('unified_excel_uploads', function (Blueprint $table) {
            if (!Schema::hasColumn('unified_excel_uploads', 'imported_count')) {
                $table->integer('imported_count')->default(0)->after('error_log');
            }
            if (!Schema::hasColumn('unified_excel_uploads', 'error_count')) {
                $table->integer('error_count')->default(0)->after('imported_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unified_excel_uploads', function (Blueprint $table) {
            $table->dropColumn(['imported_count', 'error_count']);
        });
    }
};
