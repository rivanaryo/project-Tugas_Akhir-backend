<?php

namespace App\Modules\Finance\Ledger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Request\StoreAccountAdjustmentRequest;
use App\Modules\Finance\Ledger\Resources\FinanceTransactionResource;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AccountAdjustmentController extends Controller
{
    public function __construct(private readonly FinanceLedgerService $ledgerService) {}

    public function store(StoreAccountAdjustmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $account = Account::findOrFail($data['account_id']);

            $transaction = $this->ledgerService->postAdjustment(
                $account,
                $data['direction'],
                (string) $data['amount'],
                Carbon::parse($data['transaction_date']),
                $data['description'],
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Adjustment akun berhasil dibuat',
                'data' => [
                    'transaction' => new FinanceTransactionResource($transaction->load('account')),
                    'account' => [
                        'id' => $account->fresh()->id,
                        'balance' => (float) $account->fresh()->balance,
                    ],
                ],
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['amount' => [$e->getMessage()]],
            ], 422);
        }
    }
}
