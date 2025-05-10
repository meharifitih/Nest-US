<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tenant_excel_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('status')->default('pending');
            $table->text('error_log')->nullable();
            $table->integer('parent_id')->default(0);
            $table->timestamps();
            
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_excel_uploads');
    }
}; 