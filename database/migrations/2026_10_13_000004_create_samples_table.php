<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sample_number')->unique();
            $table->foreignUuid('product_id')->constrained('m_products');
            $table->foreignUuid('customer_id')->constrained('m_customer');
            $table->foreignUuid('tech_pack_id')->nullable()->constrained('tech_packs')->nullOnDelete();
            $table->date('submitted_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision'])->default('pending');
            $table->text('notes')->nullable();
            $table->integer('revision_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
