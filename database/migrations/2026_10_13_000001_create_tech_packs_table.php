<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_packs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('fabric')->nullable();
            $table->integer('gsm')->nullable();
            $table->string('thread')->nullable();
            $table->string('zipper')->nullable();
            $table->json('accessories')->nullable();
            $table->enum('status', ['draft', 'review', 'approved'])->default('draft');
            $table->foreignUuid('product_id')->nullable()->constrained('m_products')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_packs');
    }
};
