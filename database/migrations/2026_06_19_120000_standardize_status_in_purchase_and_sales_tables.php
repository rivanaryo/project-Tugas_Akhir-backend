<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update purchase_order table
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->string('status')->default('draft')->comment('Draft: new order, Approved: approved, Received: received, Cancelled: cancelled')->change();
        });

        // Update good_receipt table
        Schema::table('good_receipt', function (Blueprint $table) {
            $table->string('status')->default('draft')->comment('Draft: new receipt, Approved: approved, Received: received, Cancelled: cancelled')->change();
        });

        // Update sales_order table
        Schema::table('sales_order', function (Blueprint $table) {
            $table->string('status')->default('draft')->comment('draft: new order, approved: approved, delivered: delivered, cancelled: cancelled')->change();
        });

        // Update sales_delivery table
        Schema::table('sales_delivery', function (Blueprint $table) {
            $table->string('status')->default('draft')->comment('draft: new delivery, approved: approved, delivered: delivered, cancelled: cancelled')->change();
        });

        // Update invoices table comment (payment status)
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('pending: unpaid, paid: paid, cancelled: cancelled')->change();
        });

        // Convert existing data
        $this->convertExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data conversion
        $this->revertDataConversion();

        // Revert schema changes
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('draft: new order, approved: approved, delivered: delivered, cancelled: cancelled')->change();
        });

        Schema::table('good_receipt', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('draft: new receipt, approved: approved, delivered: delivered, cancelled: cancelled')->change();
        });

        Schema::table('sales_order', function (Blueprint $table) {
            $table->string('status')->default('0')->comment('0: pending, 1: completed, 2: cancelled')->change();
        });

        Schema::table('sales_delivery', function (Blueprint $table) {
            $table->string('status')->default('0')->comment('0: pending, 1: completed, 2: cancelled')->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('0: pending, 1: paid, 2: cancelled')->change();
        });
    }

    /**
     * Convert existing data to new status format.
     */
    private function convertExistingData(): void
    {
        // Convert purchase_order: pending -> Draft, completed -> Received
        DB::statement("UPDATE purchase_order SET status = CASE 
            WHEN status = 'pending' THEN 'draft'
            WHEN status = 'completed' THEN 'received'
            WHEN status = 'delivered' THEN 'received'
            WHEN status = 'cancelled' THEN 'cancelled'
            ELSE status
        END");

        // Convert good_receipt: pending -> Draft, completed -> Received
        DB::statement("UPDATE good_receipt SET status = CASE 
            WHEN status = 'pending' THEN 'draft'
            WHEN status = 'completed' THEN 'received'
            WHEN status = 'delivered' THEN 'received'
            WHEN status = 'cancelled' THEN 'cancelled'
            ELSE status
        END");

        // Convert sales_order: 0 -> draft, 1 -> delivered, 2 -> cancelled
        DB::statement("UPDATE sales_order SET status = CASE 
            WHEN status = '0' THEN 'draft'
            WHEN status = '1' THEN 'delivered'
            WHEN status = '2' THEN 'cancelled'
            ELSE status
        END");

        // Convert sales_delivery: 0 -> draft, 1 -> delivered, 2 -> cancelled
        DB::statement("UPDATE sales_delivery SET status = CASE 
            WHEN status = '0' THEN 'draft'
            WHEN status = '1' THEN 'delivered'
            WHEN status = '2' THEN 'cancelled'
            ELSE status
        END");

        // Convert invoices: 0 -> pending, 1 -> paid, 2 -> cancelled
        DB::statement("UPDATE invoices SET status = CASE 
            WHEN status = '0' THEN 'pending'
            WHEN status = '1' THEN 'paid'
            WHEN status = '2' THEN 'cancelled'
            ELSE status
        END");
    }

    /**
     * Revert data conversion to old status format.
     */
    private function revertDataConversion(): void
    {
        // Revert purchase_order: Draft -> pending, Received -> completed, Approved -> pending, Cancelled -> cancelled
        DB::statement("UPDATE purchase_order SET status = CASE 
            WHEN status = 'draft' THEN 'pending'
            WHEN status = 'received' THEN 'completed'
            WHEN status = 'approved' THEN 'pending'
            WHEN status = 'cancelled' THEN 'cancelled'
            ELSE status
        END");

        // Revert good_receipt: Draft -> pending, Received -> completed, Approved -> pending, Cancelled -> cancelled
        DB::statement("UPDATE good_receipt SET status = CASE 
            WHEN status = 'draft' THEN 'pending'
            WHEN status = 'received' THEN 'completed'
            WHEN status = 'approved' THEN 'pending'
            WHEN status = 'cancelled' THEN 'cancelled'
            ELSE status
        END");

        // Revert sales_order: draft -> 0, delivered -> 1, cancelled -> 2
        DB::statement("UPDATE sales_order SET status = CASE 
            WHEN status = 'draft' THEN '0'
            WHEN status = 'delivered' THEN '1'
            WHEN status = 'cancelled' THEN '2'
            ELSE status
        END");

        // Revert sales_delivery: draft -> 0, delivered -> 1, cancelled -> 2
        DB::statement("UPDATE sales_delivery SET status = CASE 
            WHEN status = 'draft' THEN '0'
            WHEN status = 'delivered' THEN '1'
            WHEN status = 'cancelled' THEN '2'
            ELSE status
        END");

        // Revert invoices: pending -> 0, paid -> 1, cancelled -> 2
        DB::statement("UPDATE invoices SET status = CASE 
            WHEN status = 'pending' THEN '0'
            WHEN status = 'paid' THEN '1'
            WHEN status = 'cancelled' THEN '2'
            ELSE status
        END");
    }
};
