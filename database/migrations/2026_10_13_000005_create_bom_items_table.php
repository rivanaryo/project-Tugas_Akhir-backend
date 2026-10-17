<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tech_pack_id')->constrained('tech_packs')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('m_products');
            $table->decimal('usage', 12, 4)->default(0);
            $table->foreignUuid('unit_id')->constrained('units');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
