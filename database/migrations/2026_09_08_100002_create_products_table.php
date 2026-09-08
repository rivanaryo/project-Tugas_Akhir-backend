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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignUuid('category_id')->constrained('categories', 'id')->cascadeOnDelete();
            $table->foreignUuid('vendor_id')->constrained('vendors', 'id')->cascadeOnDelete();
            $table->integer('current_stock')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
