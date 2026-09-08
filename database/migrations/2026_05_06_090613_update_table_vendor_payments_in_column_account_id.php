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
        if (Schema::hasTable('vendor_payments') && ! Schema::hasColumn('vendor_payments', 'account_id')) {
            Schema::table('vendor_payments', function (Blueprint $table) {
                $table->foreignUuid('account_id')->references('id')->on('m_accounts')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: kolom account_id sudah dibuat di migration create_vendor_payments_table
    }
};
