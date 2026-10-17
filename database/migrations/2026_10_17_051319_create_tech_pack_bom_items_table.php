<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_pack_bom_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tech_pack_id')->constrained('tech_packs')->cascadeOnDelete();
            $table->foreignUuid('material_product_id')->constrained('m_products');
            $table->decimal('usage', 15, 4);
            $table->foreignUuid('unit_id')->constrained('units');
            $table->decimal('cost_per_unit', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_pack_bom_items');
    }
};
