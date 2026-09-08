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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('m_products_id')->nullable()->constrained('m_products')->nullOnDelete();
            $table->foreignUuid('m_warehouses_id')->nullable()->constrained('warehouses')->nullOnDelete();            
            $table->enum('type', ['Out', 'In']);
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->nullableUuidMorphs('referenceable');
            $table->string('reference_number')->nullable(); 
            $table->text('description')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
