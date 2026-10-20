<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['pricelist_id']);
            $table->dropColumn('pricelist_id');

            $table->foreignUuid('unit_id')
                ->nullable()
                ->after('product_id')
                ->constrained('units', 'id')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');

            $table->foreignUuid('pricelist_id')
                ->nullable()
                ->after('product_id')
                ->constrained('m_pricelists', 'id')
                ->cascadeOnDelete();
        });
    }
};
