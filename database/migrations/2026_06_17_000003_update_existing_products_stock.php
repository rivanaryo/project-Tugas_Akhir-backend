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
        // Update existing products to have default stock values
        DB::table('m_products')
            ->whereNull('stock_level')
            ->update(['stock_level' => 0]);

        DB::table('m_products')
            ->whereNull('min_stock_level')
            ->update(['min_stock_level' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as this only sets default values
    }
};
