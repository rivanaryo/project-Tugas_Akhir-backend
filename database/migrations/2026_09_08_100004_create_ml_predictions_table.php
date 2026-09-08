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
        Schema::create('ml_predictions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('m_products')->cascadeOnDelete();
            $table->foreignUuid('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('predicted_demand_total', 15, 2)->default(0);
            $table->decimal('reorder_point', 15, 2)->default(0);
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->boolean('reorder_recommended')->default(false);
            $table->decimal('suggested_order_quantity', 15, 2)->default(0);
            $table->integer('forecast_days')->default(0);
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_predictions');
    }
};
