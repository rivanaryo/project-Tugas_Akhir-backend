<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_number')->unique();
            $table->foreignUuid('vendor_id')->references('id')->on('m_vendor')->onDelete('cascade');
            $table->string('vendor_invoice_number');
            $table->string('payment_type')->comment('cash, transfer, giro,downpayment');
            $table->date('payment_date')->nullable();
            $table->decimal('total_paid', 15, 2);
            $table->foreignUuid('account_id')->references('id')->on('m_accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};
