<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('hoas', function (Blueprint $table) {
            $table->integer('hoa_number')->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('hoas', function (Blueprint $table) {
            $table->dropColumn('hoa_number');
        });
    }
}; 