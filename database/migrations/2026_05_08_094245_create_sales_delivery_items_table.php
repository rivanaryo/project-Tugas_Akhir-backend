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
        Schema::create('sales_delivery_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_delivery_id')->references('id')->on('sales_delivery')->onDelete('cascade');
            $table->foreignUuid('sales_order_item_id')->references('id')->on('sales_order_items')->onDelete('cascade');
            $table->integer('qty_delivered')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_delivery_items');
    }
};
