<?php

namespace App\Modules\Finance\Ledger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Request\StoreAccountTransferRequest;
use App\Modules\Finance\Ledger\Resources\FinanceTransactionResource;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AccountTransferController extends Controller
{
    public function __construct(private readonly FinanceLedgerService $ledgerService) {}

    public function store(StoreAccountTransferRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $source = Account::findOrFail($data['source_account_id']);
            $destination = Account::findOrFail($data['destination_account_id']);

            $result = $this->ledgerService->transfer(
                $source,
                $destination,
                (string) $data['amount'],
                Carbon::parse($data['transaction_date']),
                $data['description'] ?? null,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Transfer antar akun berhasil',
                'data' => [
                    'transfer_group_id' => $result['transfer_group_id'],
                    'source_transaction' => new FinanceTransactionResource($result['source_transaction']->load('account')),
                    'destination_transaction' => new FinanceTransactionResource($result['destination_transaction']->load('account')),
                    'source_account' => [
                        'id' => $result['source_account']->id,
                        'balance' => (float) $result['source_account']->balance,
                    ],
                    'destination_account' => [
                        'id' => $result['destination_account']->id,
                        'balance' => (float) $result['destination_account']->balance,
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
