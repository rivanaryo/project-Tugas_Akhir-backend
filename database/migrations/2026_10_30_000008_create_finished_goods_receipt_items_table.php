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
        Schema::create('finished_goods_receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('finished_goods_receipt_id')->constrained('finished_goods_receipts')->cascadeOnDelete();
            $table->foreignUuid('work_order_id')->constrained('work_orders');
            $table->foreignUuid('product_id')->constrained('m_products');
            $table->string('size')->nullable();
            $table->unsignedInteger('qty_good');
            $table->unsignedInteger('qty_reject')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_goods_receipt_items');
    }
};
