<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wo_number')->unique();
            $table->foreignUuid('sales_order_id')->constrained('sales_order');
            $table->foreignUuid('product_id')->constrained('m_products');
            $table->foreignUuid('tech_pack_id')->nullable()->constrained('tech_packs')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->enum('stage', ['cutting', 'printing', 'sewing', 'qc', 'ready'])->default('cutting');
            $table->enum('status', ['draft', 'in-progress', 'completed', 'cancelled'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->tinyInteger('progress')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
