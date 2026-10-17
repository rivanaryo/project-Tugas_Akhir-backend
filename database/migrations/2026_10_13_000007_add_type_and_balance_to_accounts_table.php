<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_accounts', function (Blueprint $table) {
            $table->enum('type', ['bank', 'cash'])->default('bank')->after('account_number');
            $table->decimal('balance', 15, 2)->default(0)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('m_accounts', function (Blueprint $table) {
            $table->dropColumn(['type', 'balance']);
        });
    }
};
