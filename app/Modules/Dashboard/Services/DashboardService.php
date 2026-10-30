<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Sales\Invoice\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * KPI values. When $receivables is provided the unpaid_invoices card
     * reuses it so the caller can share one allocation query with
     * pipeline_summary.
     */
    public function getKpi(?array $receivables = null): array
    {
        $now = Carbon::now();
        [$startOfMonth, $startOfLastMonth, $endOfLastMonthPeriod] = $this->comparisonPeriods($now);
        $endOfMonth = $now->toDateString();

        // Revenue (recognized): non-cancelled invoices issued in the period.
        $revenueThisMonth = (float) Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('invoice_date', [$startOfMonth->toDateString(), $endOfMonth])
            ->sum('grand_total');

        $revenueLastMonth = (float) Invoice::where('status', '!=', 'cancelled')
            ->whereBetween('invoice_date', [$startOfLastMonth->toDateString(), $endOfLastMonthPeriod->toDateString()])
            ->sum('grand_total');

        $revenueGrowth = $this->growth($revenueThisMonth, $revenueLastMonth);

        // Cash In Bank (total balance from active accounts)
        $cashInBank = Account::where('is_active', true)->sum('balance');
        $activeAccounts = Account::where('is_active', true)->count();

        // Pending Payables (unpaid vendor payments)
        $pendingPayables = VendorPayment::where('status', 'unpaid')
            ->sum('total_paid');

        $pendingPayablesThisWeek = VendorPayment::where('status', 'unpaid')
            ->whereBetween('payment_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
            ->count();

        // Collection Rate (% of paid invoices, cancelled excluded)
        $totalInvoices = Invoice::where('status', '!=', 'cancelled')->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $collectionRate = $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0;

        // Active Purchase Orders: only draft + approved are "active";
        // received/cancelled stay in the breakdown for context.
        $activePoBreakdown = [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'approved' => PurchaseOrder::where('status', 'approved')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
        ];
        $activePoCount = $activePoBreakdown['draft'] + $activePoBreakdown['approved'];

        // Material Expense (cash basis: paid vendor payments in the period)
        $materialExpenseThisMonth = (float) VendorPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$startOfMonth->toDateString(), $endOfMonth])
            ->sum('total_paid');

        $materialExpenseLastMonth = (float) VendorPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$startOfLastMonth->toDateString(), $endOfLastMonthPeriod->toDateString()])
            ->sum('total_paid');

        $materialExpenseGrowth = $this->growth($materialExpenseThisMonth, $materialExpenseLastMonth);

        // Unpaid Invoices (actual outstanding after valid payments)
        $receivables ??= $this->getReceivableSummary();

        return [
            'gross_revenue' => [
                'value' => $revenueThisMonth,
                'growth' => $revenueGrowth,
            ],
            'cash_in_bank' => [
                'value' => $cashInBank,
                'accounts' => $activeAccounts,
            ],
            'pending_payables' => [
                'value' => $pendingPayables,
                'due_this_week' => $pendingPayablesThisWeek,
            ],
            'collection_rate' => [
                'value' => round($collectionRate, 1),
            ],
            'revenue_month' => [
                'value' => $revenueThisMonth,
                'growth' => $revenueGrowth,
            ],
            'active_po' => [
                'value' => $activePoCount,
                'breakdown' => $activePoBreakdown,
            ],
            'material_expense' => [
                'value' => $materialExpenseThisMonth,
                'growth' => $materialExpenseGrowth,
            ],
            'unpaid_invoices' => [
                'value' => $receivables['pending_amount'],
                'count' => $receivables['pending_count'],
            ],
        ];
    }

    /**
     * Aggregates over the whole receivable scope (non-cancelled invoices),
     * independent of pipeline pagination.
     */
    public function getReceivableSummary(): array
    {
        $outstandingSql = "CASE WHEN invoice_status = 'paid' THEN 0 ELSE {$this->outstandingExpression()} END";
        $today = Carbon::now()->toDateString();
        $overdueSql = "({$outstandingSql} > 0 AND due_date IS NOT NULL AND due_date < '{$today}')";

        $row = DB::query()->fromSub($this->receivableBaseQuery(), 'recv')
            ->selectRaw("COALESCE(SUM(grand_total - {$outstandingSql}), 0) as paid_amount")
            ->selectRaw("COALESCE(SUM({$outstandingSql}), 0) as pending_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN {$overdueSql} THEN {$outstandingSql} ELSE 0 END), 0) as overdue_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN {$outstandingSql} <= 0 THEN 1 ELSE 0 END), 0) as paid_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN {$outstandingSql} > 0 THEN 1 ELSE 0 END), 0) as pending_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN {$overdueSql} THEN 1 ELSE 0 END), 0) as overdue_count")
            ->first();

        return [
            'paid_amount' => (float) $row->paid_amount,
            'pending_amount' => (float) $row->pending_amount,
            'overdue_amount' => (float) $row->overdue_amount,
            'paid_count' => (int) $row->paid_count,
            'pending_count' => (int) $row->pending_count,
            'overdue_count' => (int) $row->overdue_count,
        ];
    }

    public function getPipeline(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $paginator = DB::query()->fromSub($this->receivableBaseQuery(), 'recv')
            ->orderByDesc('invoice_date')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $paginator->getCollection()->transform(function ($row) {
            $grandTotal = (float) $row->grand_total;
            $outstanding = $row->invoice_status === 'paid'
                ? 0.0
                : min($grandTotal, max(0, (float) $row->cum_total - (float) $row->so_paid));
            $paid = $grandTotal - $outstanding;

            return [
                'id' => $row->id,
                'invoice_number' => $row->invoice_number,
                'customer' => $row->customer_name ?? 'Unknown',
                'module' => 'Sales',
                'status' => $this->receivableStatus($row->invoice_status, $outstanding, $row->due_date),
                'due_date' => $row->due_date ? substr((string) $row->due_date, 0, 10) : null,
                'invoice_date' => $row->invoice_date,
                'amount' => $grandTotal,
                'type' => 'incoming',
                'invoice_type' => $row->invoice_type === 'dp' ? 'DP' : 'Pelunasan',
                'paid_amount' => $paid,
                'outstanding_amount' => $outstanding,
            ];
        });

        return $paginator;
    }

    /**
     * Active work orders (not completed/cancelled), ordered by priority
     * (high > medium > low), then earliest due_date, then id.
     *
     * @return array{items: array<int, array>, total: int}
     */
    public function getActiveWorkOrders(int $limit = 4): array
    {
        $base = WorkOrder::query()->whereNotIn('status', ['completed', 'cancelled']);

        $total = (clone $base)->count();

        $items = $base->with('product')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->orderBy('due_date')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (WorkOrder $wo) => [
                'id' => $wo->id,
                'work_order_number' => $wo->wo_number,
                'product_name' => $wo->product?->product_name,
                'stage' => $this->stageLabel($wo->stage),
                'progress' => (int) $wo->progress,
                'status' => $wo->status,
            ])
            ->toArray();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Invoices (receivables) with per-invoice payment allocation.
     *
     * Customer payments attach to the sales order, not to a single invoice.
     * Paid amounts are allocated FIFO across the order's invoices ordered by
     * (invoice_date, id) so a DP invoice + a pelunasan invoice never double
     * count: outstanding_i = clamp(cum_total_i - so_paid, 0, grand_total_i).
     */
    private function receivableBaseQuery(): Builder
    {
        $paidSub = DB::table('customer_payments')
            ->select('sales_order_id', DB::raw('SUM(amount) as paid_total'))
            ->where('status', 'paid')
            ->groupBy('sales_order_id');

        return DB::table('invoices')
            ->leftJoin('m_customer', 'invoices.customer_id', '=', 'm_customer.id')
            ->leftJoinSub($paidSub, 'payments', 'payments.sales_order_id', '=', 'invoices.sales_order_id')
            ->where('invoices.status', '!=', 'cancelled')
            ->select([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.sales_order_id',
                'm_customer.customer_name',
                'invoices.status as invoice_status',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.grand_total',
                'invoices.type as invoice_type',
                DB::raw('COALESCE(payments.paid_total, 0) as so_paid'),
                DB::raw('SUM(invoices.grand_total) OVER (PARTITION BY COALESCE(invoices.sales_order_id, invoices.id) ORDER BY invoices.invoice_date, invoices.id) as cum_total'),
            ]);
    }

    private function outstandingExpression(): string
    {
        return 'CASE WHEN cum_total - so_paid <= 0 THEN 0 '
            .'WHEN cum_total - so_paid >= grand_total THEN grand_total '
            .'ELSE cum_total - so_paid END';
    }

    private function receivableStatus(string $invoiceStatus, float $outstanding, ?string $dueDate): string
    {
        if ($outstanding <= 0) {
            return 'paid';
        }

        if ($dueDate !== null && substr((string) $dueDate, 0, 10) < Carbon::now()->toDateString()) {
            return 'overdue';
        }

        return 'pending';
    }

    /**
     * Current month-to-date vs the equivalent day range last month
     * (e.g. Oct 1-24 vs Sep 1-24) so growth is not biased by a
     * partial current month vs a full previous month.
     *
     * @return array{0: Carbon, 1: Carbon, 2: Carbon}
     */
    private function comparisonPeriods(Carbon $now): array
    {
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonthPeriod = $startOfLastMonth->copy()
            ->addDays($now->day - 1)
            ->min($startOfLastMonth->copy()->endOfMonth());

        return [$startOfMonth, $startOfLastMonth, $endOfLastMonthPeriod];
    }

    /**
     * Deterministic growth policy: if previous > 0 use the percentage
     * formula; if previous = 0 then 100 when current > 0, else 0.
     */
    private function growth(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function stageLabel(?string $stage): ?string
    {
        return match ($stage) {
            'cutting' => 'Cutting',
            'printing' => 'Printing',
            'sewing' => 'Sewing',
            'qc' => 'QC',
            'ready' => 'Ready',
            default => $stage,
        };
    }
}
