<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\MasterData\ManagementAccount\Models\Account;
use App\Modules\Finance\VendorPayment\Models\VendorPayment;
use App\Modules\Production\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
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
            ->sum('grand_total') ?? 0;

        $grossRevenueLastMonth = Invoice::whereIn('status', ['approved', 'paid'])
            ->whereBetween('invoice_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('grand_total') ?? 0;

        $grossRevenueGrowth = $grossRevenueLastMonth > 0
            ? (($grossRevenueThisMonth - $grossRevenueLastMonth) / $grossRevenueLastMonth) * 100
            : 0;

        // Cash In Bank (total balance from active accounts)
        $cashInBank = Account::where('is_active', true)->sum('balance');
        $activeAccounts = Account::where('is_active', true)->count();

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

        // Revenue Month
        $revenueMonth = $grossRevenueThisMonth;
        $revenueMonthGrowth = round($grossRevenueGrowth, 1);

        // Active Purchase Orders
        $activePoCount = PurchaseOrder::whereIn('status', ['draft', 'approved'])->count();
        $activePoBreakdown = [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'approved' => PurchaseOrder::where('status', 'approved')->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
        ];

        // Material Expense (paid vendor payments this month)
        $materialExpenseThisMonth = VendorPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('total_paid') ?? 0;

        $materialExpenseLastMonth = VendorPayment::where('status', 'paid')
            ->whereBetween('payment_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_paid') ?? 0;

        $materialExpenseGrowth = $materialExpenseLastMonth > 0
            ? (($materialExpenseThisMonth - $materialExpenseLastMonth) / $materialExpenseLastMonth) * 100
            : 0;

        // Unpaid Invoices
        $unpaidInvoicesValue = Invoice::where('status', 'pending')->sum('grand_total') ?? 0;
        $unpaidInvoicesCount = Invoice::where('status', 'pending')->count();

        return [
            'gross_revenue' => [
                'value' => $grossRevenueThisMonth,
                'growth' => round($grossRevenueGrowth, 1),
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
                'value' => $revenueMonth,
                'growth' => $revenueMonthGrowth,
            ],
            'active_po' => [
                'value' => $activePoCount,
                'breakdown' => $activePoBreakdown,
            ],
            'material_expense' => [
                'value' => $materialExpenseThisMonth,
                'growth' => round($materialExpenseGrowth, 1),
            ],
            'unpaid_invoices' => [
                'value' => $unpaidInvoicesValue,
                'count' => $unpaidInvoicesCount,
            ],
        ];
    }

    public function getActiveWorkOrders(int $limit = 10): array
    {
        return WorkOrder::with('product')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (WorkOrder $wo) => [
                'id' => $wo->id,
                'wo_number' => $wo->wo_number,
                'product' => $wo->product?->product_name,
                'stage' => $wo->stage,
                'progress' => $wo->progress,
            ])
            ->toArray();
    }

    public function getPipeline(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        // Get Sales Invoices as Receivables with JOIN to get customer name
        $invoices = DB::table('invoices')
            ->leftJoin('m_customer', 'invoices.customer_id', '=', 'm_customer.id')
            ->select([
                'invoices.id',
                'invoices.invoice_number as ref_number',
                'm_customer.customer_name as contact_name',
                DB::raw("'Sales' as module"),
                'invoices.status',
                'invoices.invoice_date as date',
                'invoices.grand_total as amount',
                DB::raw("'incoming' as type"),
                'invoices.created_at',
            ]);

        // Get Vendor Payments as Payables with JOIN to get vendor name
        $payments = DB::table('vendor_payments')
            ->leftJoin('m_vendor', 'vendor_payments.vendor_id', '=', 'm_vendor.id')
            ->select([
                'vendor_payments.id',
                'vendor_payments.payment_number as ref_number',
                'm_vendor.vendor_name as contact_name',
                DB::raw("'Purchase' as module"),
                'vendor_payments.status',
                'vendor_payments.payment_date as date',
                'vendor_payments.total_paid as amount',
                DB::raw("'outgoing' as type"),
                'vendor_payments.created_at',
            ]);

        // Union both queries
        $unionQuery = $invoices->unionAll($payments);

        // Get total count
        $total = DB::table(DB::raw("({$unionQuery->toSql()}) as sub"))
            ->mergeBindings($unionQuery)
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
                    'customer' => $item->contact_name ?? 'Unknown',
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
