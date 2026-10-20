<?php

namespace App\Modules\Finance\Ledger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Request\FinanceAccountSummaryRequest;
use App\Modules\Finance\Ledger\Resources\FinanceAccountSummaryResource;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class FinanceAccountSummaryController extends Controller
{
    public function __construct(private readonly FinanceLedgerService $ledgerService) {}

    public function __invoke(FinanceAccountSummaryRequest $request): JsonResponse
    {
        $dateFrom = $request->has('date_from')
            ? Carbon::parse($request->validated('date_from'))
            : Carbon::now()->startOfMonth();

        $dateTo = $request->has('date_to')
            ? Carbon::parse($request->validated('date_to'))
            : Carbon::now();

        $accountId = $request->validated('account_id');
        $singleAccount = $accountId ? Account::find($accountId) : null;

        $summary = $this->ledgerService->calculateSummary($singleAccount, $dateFrom, $dateTo);

        $query = Account::query();
        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get()->map(function (Account $account) use ($dateFrom, $dateTo): array {
            $accountSummary = $this->ledgerService->calculateSummary($account, $dateFrom, $dateTo);

            return [
                'id' => $account->id,
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'type' => $account->type,
                'bank_name' => $account->bank_name,
                'opening_balance' => $accountSummary['opening_balance'],
                'cash_in' => $accountSummary['cash_in'],
                'cash_out' => $accountSummary['cash_out'],
                'closing_balance' => $accountSummary['closing_balance'],
                'current_balance' => $account->balance,
                'is_active' => $account->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan akun finance berhasil diambil',
            'data' => [
                'date_from' => $summary['date_from'],
                'date_to' => $summary['date_to'],
                'opening_balance' => $summary['opening_balance'],
                'cash_in' => $summary['cash_in'],
                'cash_out' => $summary['cash_out'],
                'closing_balance' => $summary['closing_balance'],
                'accounts' => FinanceAccountSummaryResource::collection($accounts),
            ],
        ]);
    }
}
