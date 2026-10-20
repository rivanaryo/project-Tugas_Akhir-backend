<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sample_code')->unique();
            $table->foreignUuid('product_id')->constrained('m_products')->restrictOnDelete();
            $table->foreignUuid('customer_id')->constrained('m_customer')->restrictOnDelete();
            $table->foreignUuid('sales_order_id')->nullable()->constrained('sales_order')->nullOnDelete();
            $table->date('submitted_date')->index();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision'])->default('pending')->index();
            $table->text('notes')->nullable();
            $table->unsignedInteger('revision_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_approvals');
    }
};
