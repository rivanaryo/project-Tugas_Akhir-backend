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
        Schema::create('m_pricelists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vendor_id');
            $table->uuid('product_id');
            $table->foreign('vendor_id')->references('id')->on('m_vendor')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('m_products')->onDelete('cascade');
            $table->decimal('price', 15, 2);
            $table->string('currency', 3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_pricelists');
    }
};
