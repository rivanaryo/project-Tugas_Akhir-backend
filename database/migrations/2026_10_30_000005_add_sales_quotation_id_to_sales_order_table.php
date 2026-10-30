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
        Schema::table('sales_order', function (Blueprint $table) {
            $table->foreignUuid('sales_quotation_id')->nullable()->after('customer_id')
                ->references('id')->on('sales_quotation')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order', function (Blueprint $table) {
            $table->dropForeign(['sales_quotation_id']);
            $table->dropColumn('sales_quotation_id');
        });
    }
};
