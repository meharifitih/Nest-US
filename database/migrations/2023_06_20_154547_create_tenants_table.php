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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->integer('family_member')->nullable();
            $table->text('sub_city')->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->string('house_number')->nullable();
            $table->string('woreda')->nullable();
            $table->integer('property')->default(0);
            $table->integer('unit')->default(0);
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->integer('parent_id')->default(0);
            $table->string('city')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
};
