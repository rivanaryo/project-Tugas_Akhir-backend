<?php

namespace App\Console\Commands;

use App\Modules\Finance\CustomerPayment\Models\CustomerPayment;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use Illuminate\Console\Command;

class BackfillFinanceLedger extends Command
{
    protected $signature = 'finance:backfill-ledger';

    protected $description = 'Backfill finance ledger dari customer dan vendor payment yang sudah ada.';

    public function handle(FinanceLedgerService $ledgerService): int
    {
        $success = 0;
        $failed = 0;

        CustomerPayment::query()
            ->where('status', 'paid')
            ->chunk(100, function ($payments) use ($ledgerService, &$success, &$failed): void {
                foreach ($payments as $payment) {
                    try {
                        $existing = $ledgerService->findPostedTransaction('customer_payment', $payment->id, 'in');
                        if (! $existing) {
                            $ledgerService->postCustomerPayment($payment, null);
                            $success++;
                        }
                    } catch (\Throwable $e) {
                        $this->error("Customer payment {$payment->id} gagal: {$e->getMessage()}");
                        $failed++;
                    }
                }
            });

        VendorPayment::query()
            ->where('status', 'paid')
            ->chunk(100, function ($payments) use ($ledgerService, &$success, &$failed): void {
                foreach ($payments as $payment) {
                    try {
                        $existing = $ledgerService->findPostedTransaction('vendor_payment', $payment->id, 'out');
                        if (! $existing) {
                            $ledgerService->postVendorPayment($payment, null);
                            $success++;
                        }
                    } catch (\Throwable $e) {
                        $this->error("Vendor payment {$payment->id} gagal: {$e->getMessage()}");
                        $failed++;
                    }
                }
            });

        $this->info("Backfill selesai. Berhasil: {$success}, Gagal: {$failed}");

        return self::SUCCESS;
    }
}
