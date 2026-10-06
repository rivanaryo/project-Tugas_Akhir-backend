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
        // Fix referenceable_type in stock_movements table
        // Convert invalid class names to proper full namespaces
        DB::table('stock_movements')
            ->where('referenceable_type', 'Purchase Order')
            ->update(['referenceable_type' => 'App\Modules\Inventory\PurchaseOrder\Models\PurchaseOrder']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'Sales Order')
            ->update(['referenceable_type' => 'App\Modules\Sales\SalesOrder\Models\SalesOrder']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'Good Receipt')
            ->update(['referenceable_type' => 'App\Modules\Inventory\GoodReceipt\Models\GoodReceipt']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'Sales Delivery')
            ->update(['referenceable_type' => 'App\Modules\Sales\SalesDelivery\Models\SalesDelivery']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old format
        DB::table('stock_movements')
            ->where('referenceable_type', 'App\Modules\Inventory\PurchaseOrder\Models\PurchaseOrder')
            ->update(['referenceable_type' => 'Purchase Order']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'App\Modules\Sales\SalesOrder\Models\SalesOrder')
            ->update(['referenceable_type' => 'Sales Order']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'App\Modules\Inventory\GoodReceipt\Models\GoodReceipt')
            ->update(['referenceable_type' => 'Good Receipt']);

        DB::table('stock_movements')
            ->where('referenceable_type', 'App\Modules\Sales\SalesDelivery\Models\SalesDelivery')
            ->update(['referenceable_type' => 'Sales Delivery']);
    }
};
