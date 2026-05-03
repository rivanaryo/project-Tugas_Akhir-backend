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
        Schema::create('good_receipt_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('good_receipt_id')->references('id')->on('good_receipt')->onDelete('cascade');
            $table->foreignUuid('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            $table->foreignUuid('pricelist_id')->references('id')->on('m_pricelists')->onDelete('cascade');
            $table->foreignUuid('product_id')->references('id')->on('m_products')->onDelete('cascade');
            $table->integer('qty_received');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipt_items');
    }
};
