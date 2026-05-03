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
        Schema::create('good_receipt', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gr_number')->unique();
            $table->foreignUuid('purchase_order_id')->references('id')->on('purchase_order')->onDelete('cascade');
            $table->foreignUuid('vendor_id')->references('id')->on('m_vendor')->onDelete('cascade');
            $table->date('receipt_date');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipt');
    }
};
