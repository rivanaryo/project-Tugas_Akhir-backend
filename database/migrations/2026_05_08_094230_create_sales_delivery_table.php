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
        Schema::create('sales_delivery', function (Blueprint $table) {
            $table->Uuid('id')->primary();
            $table->string('delivery_number')->unique();
            $table->foreignUuid('customer_id')->references('id')->on('m_customer')->onDelete('cascade');
            $table->foreignUuid('sales_order_id')->references('id')->on('sales_order')->onDelete('cascade');
            $table->date('delivery_date')->nullable();
            $table->boolean('status')->default('0')->comment('0: pending, 1: completed, 2: cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_delivery');
    }
};
