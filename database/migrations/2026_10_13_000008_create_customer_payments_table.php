<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_number')->unique();
            $table->foreignUuid('sales_order_id')->constrained('sales_order');
            $table->foreignUuid('customer_id')->constrained('m_customer');
            $table->enum('type', ['dp', 'pelunasan'])->default('dp');
            $table->decimal('amount', 15, 2)->default(0);
            $table->foreignUuid('account_id')->constrained('m_accounts');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['paid', 'pending'])->default('paid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
