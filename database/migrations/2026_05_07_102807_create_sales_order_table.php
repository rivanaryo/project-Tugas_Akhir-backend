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
        Schema::create('sales_order', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->foreignUuid('customer_id')->references('id')->on('m_customer')->onDelete('cascade');
            $table->date('order_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->boolean('status')->default('0')->comment('0: pending, 1: completed, 2: cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order');
    }
};
