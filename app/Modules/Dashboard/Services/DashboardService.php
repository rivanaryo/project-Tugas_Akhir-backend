<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\MasterData\ManagementAccount\Models\Account;
use App\Modules\Purchase\VendorPayment\Models\VendorPayment;
use App\Modules\Sales\Invoice\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKpi(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Gross Revenue (paid invoices this month)
        $grossRevenueThisMonth = Invoice::whereIn('status', ['approved', 'paid'])
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->sum('grand_total');

        $grossRevenueLastMonth = Invoice::whereIn('status', ['approved', 'paid'])
            ->whereBetween('invoice_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('grand_total');

        $grossRevenueGrowth = $grossRevenueLastMonth > 0
            ? (($grossRevenueThisMonth - $grossRevenueLastMonth) / $grossRevenueLastMonth) * 100
            : 0;

        // Cash In Bank (total from active accounts)
        $cashInBank = Account::where('is_active', true)->count();

        // Pending Payables (unpaid vendor payments)
        $pendingPayables = VendorPayment::where('status', 'unpaid')
            ->sum('total_paid');

        $pendingPayablesThisWeek = VendorPayment::where('status', 'unpaid')
            ->whereBetween('payment_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
            ->count();

        // Collection Rate (% of paid invoices)
        $totalInvoices = Invoice::count();
        $paidInvoices = Invoice::whereIn('status', ['approved', 'paid'])->count();
        $collectionRate = $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0;

        return [
            'gross_revenue' => [
                'value' => $grossRevenueThisMonth,
                'growth' => round($grossRevenueGrowth, 1),
            ],
            'cash_in_bank' => [
                'value' => $cashInBank,
                'accounts' => $cashInBank,
            ],
            'pending_payables' => [
                'value' => $pendingPayables,
                'due_this_week' => $pendingPayablesThisWeek,
            ],
            'collection_rate' => [
                'value' => round($collectionRate, 1),
            ],
        ];
    }

    public function getPipeline(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        // Get Sales Invoices as Receivables
        $invoices = Invoice::with('customer')
            ->select([
                'id',
                'invoice_number as ref_number',
                'customer_id',
                DB::raw("'Sales' as module"),
                'status',
                'invoice_date as date',
                'grand_total as amount',
                DB::raw("'incoming' as type"),
                'created_at',
            ]);

        // Get Vendor Payments as Payables
        $payments = VendorPayment::with('vendors')
            ->select([
                'id',
                'payment_number as ref_number',
                'vendor_id',
                DB::raw("'Purchase' as module"),
                'status',
                'payment_date as date',
                'total_paid as amount',
                DB::raw("'outgoing' as type"),
                'created_at',
            ]);

        // Union both queries
        $unionQuery = $invoices->unionAll($payments);

        // Get total count
        $total = DB::table(DB::raw("({$unionQuery->toSql()}) as sub"))
            ->mergeBindings($unionQuery->getQuery())
            ->count();

        // Get paginated results
        $results = $unionQuery
            ->orderByDesc('date')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'invoice_number' => $item->ref_number,
                    'customer' => $item->customer?->customer_name ?? $item->vendors?->vendor_name ?? 'Unknown',
                    'module' => $item->module,
                    'status' => $item->status,
                    'due_date' => $item->date,
                    'amount' => $item->amount,
                    'type' => $item->type,
                ];
            });

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }
}
