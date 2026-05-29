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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->nullable()->change();
            $table->decimal('tax_total', 15, 2)->nullable()->change();
            $table->decimal('grand_total', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->nullable(false)->change();
            $table->decimal('tax_total', 15, 2)->nullable(false)->change();
            $table->decimal('grand_total', 15, 2)->nullable(false)->change();
        });
    }
};
