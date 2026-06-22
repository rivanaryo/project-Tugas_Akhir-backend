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
        // Fix sales_delivery status conversion - ensure all numeric status are converted
        DB::statement("UPDATE sales_delivery SET status = CASE 
            WHEN status = '0' THEN 'draft'
            WHEN status = '1' THEN 'delivered'
            WHEN status = '2' THEN 'cancelled'
            WHEN status = 'pending' THEN 'draft'
            WHEN status = 'completed' THEN 'delivered'
            ELSE status
        END WHERE status IN ('0', '1', '2', 'pending', 'completed')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert sales_delivery status to numeric format
        DB::statement("UPDATE sales_delivery SET status = CASE 
            WHEN status = 'draft' THEN '0'
            WHEN status = 'delivered' THEN '1'
            WHEN status = 'cancelled' THEN '2'
            ELSE status
        END WHERE status IN ('draft', 'delivered', 'cancelled')");
    }
};
