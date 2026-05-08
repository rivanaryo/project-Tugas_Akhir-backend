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
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('sales_order', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'total_tax', 'grand_total']);
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'tax_amount']);
        });
    }
};
