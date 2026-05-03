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
        Schema::table('m_customer', function (Blueprint $table) {
            if (! Schema::hasColumn('m_customer', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }

            if (! Schema::hasColumn('m_customer', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }

            if (! Schema::hasColumn('m_customer', 'email')) {
                $table->string('email')->nullable();
            }

            if (! Schema::hasColumn('m_customer', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep core columns intact on rollback.
    }
};
