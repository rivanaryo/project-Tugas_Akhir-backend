<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('invoice_date')->index();
        });

        // Backfill: jatuh tempo default 30 hari setelah tanggal invoice.
        DB::table('invoices')->whereNull('due_date')->orderBy('id')->lazy()
            ->each(function ($invoice): void {
                DB::table('invoices')->where('id', $invoice->id)->update([
                    'due_date' => Carbon::parse($invoice->invoice_date)->addDays(30)->toDateString(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropColumn('due_date');
        });
    }
};
