<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignUuid('sales_order_item_id')->nullable()->after('sales_order_id')
                ->constrained('sales_order_items')->nullOnDelete();
            $table->unsignedInteger('qty_completed')->default(0)->after('quantity');
            $table->unsignedInteger('qty_reject')->default(0)->after('qty_completed');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['sales_order_item_id']);
            $table->dropColumn(['sales_order_item_id', 'qty_completed', 'qty_reject']);
        });
    }
};
