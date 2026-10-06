<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Purchase Order model moved from Purchase module to Inventory module
        DB::table('stock_movements')
            ->where('referenceable_type', 'App\\Modules\\Purchase\\PurchaseOrder\\Models\\PurchaseOrder')
            ->update(['referenceable_type' => 'App\\Modules\\Inventory\\PurchaseOrder\\Models\\PurchaseOrder']);

        // Good Receipt model moved from Purchase module to Inventory module
        DB::table('stock_movements')
            ->where('referenceable_type', 'App\\Modules\\Purchase\\GoodReceipt\\Models\\GoodReceipt')
            ->update(['referenceable_type' => 'App\\Modules\\Inventory\\GoodReceipt\\Models\\GoodReceipt']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('stock_movements')
            ->where('referenceable_type', 'App\\Modules\\Inventory\\PurchaseOrder\\Models\\PurchaseOrder')
            ->update(['referenceable_type' => 'App\\Modules\\Purchase\\PurchaseOrder\\Models\\PurchaseOrder']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'App\\Modules\\Inventory\\GoodReceipt\\Models\\GoodReceipt')
            ->update(['referenceable_type' => 'App\\Modules\\Purchase\\GoodReceipt\\Models\\GoodReceipt']);
    }
};
