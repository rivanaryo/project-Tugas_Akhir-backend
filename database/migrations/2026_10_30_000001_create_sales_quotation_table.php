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
        Schema::create('sales_quotation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('quotation_number')->unique();
            $table->foreignUuid('customer_id')->references('id')->on('m_customer')->onDelete('cascade');
            $table->date('quotation_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status')->default('draft')->comment('draft, sent, accepted, rejected, expired, cancelled');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_quotation');
    }
};
