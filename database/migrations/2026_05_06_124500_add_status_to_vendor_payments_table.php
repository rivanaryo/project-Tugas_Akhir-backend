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
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_payments', 'status')) {
                $table->string('status')->default('unpaid')->comment('paid, unpaid')->after('payment_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_payments', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
