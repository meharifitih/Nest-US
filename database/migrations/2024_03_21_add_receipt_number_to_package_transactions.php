<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('package_transactions', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('payment_screenshot');
        });
    }

    public function down()
    {
        Schema::table('package_transactions', function (Blueprint $table) {
            $table->dropColumn('receipt_number');
        });
    }
}; 