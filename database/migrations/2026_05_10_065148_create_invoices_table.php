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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->foreignUuid('customer_id')->constrained('m_customer')->onDelete('cascade');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_total', 15, 2);
            $table->decimal('grand_total', 15, 2);
            $table->boolean('status')->default(0)->comment('0: pending, 1: paid, 2: cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
