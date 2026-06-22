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
        DB::statement("UPDATE stock_movements SET referenceable_type = CASE 
            WHEN referenceable_type = 'Purchase Order' THEN 'App\\\\Modules\\\\Purchase\\\\PurchaseOrder\\\\Models\\\\PurchaseOrder'
            WHEN referenceable_type = 'Sales Order' THEN 'App\\\\Modules\\\\Sales\\\\SalesOrder\\\\Models\\\\SalesOrder'
            WHEN referenceable_type = 'Good Receipt' THEN 'App\\\\Modules\\\\Purchase\\\\GoodReceipt\\\\Models\\\\GoodReceipt'
            WHEN referenceable_type = 'Sales Delivery' THEN 'App\\\\Modules\\\\Sales\\\\SalesDelivery\\\\Models\\\\SalesDelivery'
            ELSE referenceable_type
        END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old format
        DB::statement("UPDATE stock_movements SET referenceable_type = CASE 
            WHEN referenceable_type = 'App\\\\Modules\\\\Purchase\\\\PurchaseOrder\\\\Models\\\\PurchaseOrder' THEN 'Purchase Order'
            WHEN referenceable_type = 'App\\\\Modules\\\\Sales\\\\SalesOrder\\\\Models\\\\SalesOrder' THEN 'Sales Order'
            WHEN referenceable_type = 'App\\\\Modules\\\\Purchase\\\\GoodReceipt\\\\Models\\\\GoodReceipt' THEN 'Good Receipt'
            WHEN referenceable_type = 'App\\\\Modules\\\\Sales\\\\SalesDelivery\\\\Models\\\\SalesDelivery' THEN 'Sales Delivery'
            ELSE referenceable_type
        END");
    }
};
