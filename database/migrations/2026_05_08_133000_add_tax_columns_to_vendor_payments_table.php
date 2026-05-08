<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_payments', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('total_paid');
            }

            if (! Schema::hasColumn('vendor_payments', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_payments', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_payments', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }

            if (Schema::hasColumn('vendor_payments', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};

