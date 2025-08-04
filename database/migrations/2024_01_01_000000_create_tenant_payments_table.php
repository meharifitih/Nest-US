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
        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_type')->default('rent'); // rent, utilities, maintenance, etc.
            $table->string('payment_method'); // stripe, paypal, bank_transfer, cash
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->date('due_date');
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_interval', ['monthly', 'quarterly', 'yearly'])->nullable();
            $table->date('next_payment_date')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('paypal_subscription_id')->nullable();
            $table->string('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->index(['tenant_id', 'status']);
            $table->index(['payment_date', 'status']);
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_payments');
    }
}; 