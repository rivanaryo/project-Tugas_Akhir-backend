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
        Schema::create('finished_goods_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fgr_number')->unique();
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->date('receipt_date');
            $table->enum('status', ['draft', 'received', 'cancelled'])->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_goods_receipts');
    }
};
