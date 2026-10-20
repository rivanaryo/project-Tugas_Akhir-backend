<?php

namespace App\Modules\Finance\Ledger\Services;

use App\Modules\Admin\Models\Admin;
use App\Modules\Finance\CustomerPayment\Models\CustomerPayment;
use App\Modules\Finance\Ledger\Models\FinanceTransaction;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceLedgerService
{
    public function postCustomerPayment(CustomerPayment $payment, ?Admin $user = null): ?FinanceTransaction
    {
        if ($payment->status !== 'paid') {
            return null;
        }

        return DB::transaction(function () use ($payment, $user): ?FinanceTransaction {
            $account = Account::lockForUpdate()->findOrFail($payment->account_id);

            $existing = $this->findPostedTransaction(
                'customer_payment',
                $payment->id,
                'in'
            );

            if ($existing) {
                return $existing;
            }

            $transaction = FinanceTransaction::create([
                'account_id' => $account->id,
                'transaction_date' => $payment->payment_date ?? now(),
                'direction' => 'in',
                'amount' => (string) $payment->amount,
                'reference_type' => 'customer_payment',
                'reference_id' => $payment->id,
                'reference_number' => $payment->payment_number,
                'description' => "Pembayaran customer {$payment->payment_number}",
                'status' => 'posted',
                'created_by' => $user?->id,
            ]);

            $this->recalculateBalance($account);

            return $transaction;
        });
    }

    public function postVendorPayment(VendorPayment $payment, ?Admin $user = null): ?FinanceTransaction
    {
        if ($payment->status !== 'paid') {
            return null;
        }

        return DB::transaction(function () use ($payment, $user): ?FinanceTransaction {
            $account = Account::lockForUpdate()->findOrFail($payment->account_id);

            $this->ensureSufficientBalance($account, (string) $payment->total_paid);

            $existing = $this->findPostedTransaction(
                'vendor_payment',
                $payment->id,
                'out'
            );

            if ($existing) {
                return $existing;
            }

            $transaction = FinanceTransaction::create([
                'account_id' => $account->id,
                'transaction_date' => $payment->payment_date ?? now(),
                'direction' => 'out',
                'amount' => (string) $payment->total_paid,
                'reference_type' => 'vendor_payment',
                'reference_id' => $payment->id,
                'reference_number' => $payment->payment_number,
                'description' => "Pembayaran vendor {$payment->payment_number}",
                'status' => 'posted',
                'created_by' => $user?->id,
            ]);

            $this->recalculateBalance($account);

            return $transaction;
        });
    }

    public function postAdjustment(
        Account $account,
        string $direction,
        string $amount,
        Carbon $transactionDate,
        ?string $description,
        ?Admin $user = null
    ): FinanceTransaction {
        return DB::transaction(function () use ($account, $direction, $amount, $transactionDate, $description, $user): FinanceTransaction {
            $account = Account::lockForUpdate()->findOrFail($account->id);

            if ($direction === 'out') {
                $this->ensureSufficientBalance($account, $amount);
            }

            $transaction = FinanceTransaction::create([
                'account_id' => $account->id,
                'transaction_date' => $transactionDate,
                'direction' => $direction,
                'amount' => $amount,
                'reference_type' => 'account_adjustment',
                'reference_id' => null,
                'reference_number' => null,
                'description' => $description,
                'status' => 'posted',
                'created_by' => $user?->id,
            ]);

            $this->recalculateBalance($account);

            return $transaction;
        });
    }

    public function transfer(
        Account $source,
        Account $destination,
        string $amount,
        Carbon $transactionDate,
        ?string $description,
        ?Admin $user = null
    ): array {
        return DB::transaction(function () use ($source, $destination, $amount, $transactionDate, $description, $user): array {
            $source = Account::lockForUpdate()->findOrFail($source->id);
            $destination = Account::lockForUpdate()->findOrFail($destination->id);

            $this->ensureSufficientBalance($source, $amount);

            $transferGroupId = Str::uuid();
            $baseDescription = $description ?? "Transfer dari {$source->account_name} ke {$destination->account_name}";

            $sourceTransaction = FinanceTransaction::create([
                'account_id' => $source->id,
                'transaction_date' => $transactionDate,
                'direction' => 'out',
                'amount' => $amount,
                'reference_type' => 'account_transfer',
                'reference_id' => null,
                'reference_number' => null,
                'description' => $baseDescription,
                'status' => 'posted',
                'transfer_group_id' => $transferGroupId,
                'created_by' => $user?->id,
            ]);

            $destinationTransaction = FinanceTransaction::create([
                'account_id' => $destination->id,
                'transaction_date' => $transactionDate,
                'direction' => 'in',
                'amount' => $amount,
                'reference_type' => 'account_transfer',
                'reference_id' => null,
                'reference_number' => null,
                'description' => $baseDescription,
                'status' => 'posted',
                'transfer_group_id' => $transferGroupId,
                'created_by' => $user?->id,
            ]);

            $this->recalculateBalance($source);
            $this->recalculateBalance($destination);

            return [
                'transfer_group_id' => $transferGroupId,
                'source_transaction' => $sourceTransaction,
                'destination_transaction' => $destinationTransaction,
                'source_account' => $source->fresh(),
                'destination_account' => $destination->fresh(),
            ];
        });
    }

    public function voidTransaction(FinanceTransaction $transaction, ?Admin $user = null, ?string $reason = null): FinanceTransaction
    {
        return DB::transaction(function () use ($transaction, $user, $reason): FinanceTransaction {
            if ($transaction->status !== 'posted') {
                return $transaction;
            }

            $account = Account::lockForUpdate()->findOrFail($transaction->account_id);

            $transaction->update([
                'status' => 'void',
                'voided_at' => now(),
                'voided_by' => $user?->id,
                'description' => $reason ? "{$transaction->description} (void: {$reason})" : $transaction->description,
            ]);

            $this->recalculateBalance($account);

            return $transaction->fresh();
        });
    }

    public function recalculateBalance(Account $account): string
    {
        $account = Account::lockForUpdate()->findOrFail($account->id);

        $cashIn = FinanceTransaction::where('account_id', $account->id)
            ->where('direction', 'in')
            ->where('status', 'posted')
            ->sum('amount');

        $cashOut = FinanceTransaction::where('account_id', $account->id)
            ->where('direction', 'out')
            ->where('status', 'posted')
            ->sum('amount');

        $balance = (float) $account->opening_balance + (float) $cashIn - (float) $cashOut;

        $account->update(['balance' => $balance]);

        return number_format($balance, 2, '.', '');
    }

    public function calculateSummary(?Account $account, Carbon $dateFrom, Carbon $dateTo): array
    {
        $query = FinanceTransaction::where('status', 'posted')
            ->whereDate('transaction_date', '>=', $dateFrom)
            ->whereDate('transaction_date', '<=', $dateTo);

        if ($account) {
            $query->where('account_id', $account->id);
        }

        $cashIn = (float) (clone $query)->where('direction', 'in')->sum('amount');
        $cashOut = (float) (clone $query)->where('direction', 'out')->sum('amount');

        $openingBalance = 0;
        if ($account) {
            $openingBalance = (float) $account->opening_balance + (float) FinanceTransaction::where('account_id', $account->id)
                ->where('status', 'posted')
                ->whereDate('transaction_date', '<', $dateFrom)
                ->selectRaw("SUM(CASE WHEN direction = 'in' THEN amount ELSE 0 END) - SUM(CASE WHEN direction = 'out' THEN amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        }

        $closingBalance = $openingBalance + $cashIn - $cashOut;

        return [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'opening_balance' => $openingBalance,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'closing_balance' => $closingBalance,
        ];
    }

    public function findPostedTransaction(string $referenceType, string $referenceId, string $direction): ?FinanceTransaction
    {
        return FinanceTransaction::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('direction', $direction)
            ->where('status', 'posted')
            ->first();
    }

    private function ensureSufficientBalance(Account $account, string $amount): void
    {
        $account->refresh();
        if ((float) $account->balance < (float) $amount) {
            throw new \RuntimeException('Saldo akun tidak mencukupi.');
        }
    }
}
