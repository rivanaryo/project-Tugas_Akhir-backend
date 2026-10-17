<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_charts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('m_products')->cascadeOnDelete();
            $table->foreignUuid('tech_pack_id')->nullable()->constrained('tech_packs')->nullOnDelete();
            $table->enum('size', ['S', 'M', 'L', 'XL', 'XXL']);
            $table->decimal('chest_width', 8, 2)->nullable();
            $table->decimal('body_length', 8, 2)->nullable();
            $table->decimal('sleeve_length', 8, 2)->nullable();
            $table->decimal('shoulder_width', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_charts');
    }
};
