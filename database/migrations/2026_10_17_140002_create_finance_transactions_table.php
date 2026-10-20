<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained('m_accounts', 'id')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('direction', ['in', 'out']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_type');
            $table->uuid('reference_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['posted', 'void'])->default('posted');
            $table->uuid('transfer_group_id')->nullable();
            $table->uuid('reversal_of_id')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->foreignUuid('voided_by')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->index('transaction_date');
            $table->index('direction');
            $table->index('status');
            $table->index('reference_type');
            $table->index('reference_id');
            $table->index('transfer_group_id');
            $table->unique(['reference_type', 'reference_id', 'direction'], 'finance_transactions_reference_unique');
        });

        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreign('reversal_of_id')
                ->references('id')
                ->on('finance_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
