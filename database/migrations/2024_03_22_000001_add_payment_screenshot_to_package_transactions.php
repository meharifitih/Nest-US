<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('package_transactions', function (Blueprint $table) {
            $table->string('payment_screenshot')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('package_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_screenshot', 'status', 'rejection_reason']);
        });
    }
}; 