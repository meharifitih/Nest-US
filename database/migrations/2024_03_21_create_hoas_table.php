<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained('property_units')->onDelete('set null');
            $table->foreignId('hoa_type_id')->nullable()->constrained('types')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['monthly', 'quarterly', 'semi_annual', 'annual']);
            $table->enum('status', ['pending', 'open', 'paid'])->default('open');
            $table->dateTime('due_date');
            $table->dateTime('paid_date')->nullable();
            $table->text('description')->nullable();
            $table->string('receipt')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hoas');
    }
}; 