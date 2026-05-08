<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vendor_payments') && Schema::hasColumn('vendor_payments', 'account__id')) {
            DB::statement('ALTER TABLE vendor_payments DROP CONSTRAINT IF EXISTS vendor_payments_account__id_foreign');
            DB::statement('ALTER TABLE vendor_payments DROP COLUMN IF EXISTS account__id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vendor_payments') && ! Schema::hasColumn('vendor_payments', 'account__id')) {
            Schema::table('vendor_payments', function (Blueprint $table) {
                $table->foreignUuid('account__id')->nullable()->references('id')->on('m_accounts')->onDelete('cascade');
            });
        }
    }
};
