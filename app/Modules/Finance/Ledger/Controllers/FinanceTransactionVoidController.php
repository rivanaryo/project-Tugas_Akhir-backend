<?php

namespace App\Modules\Finance\Ledger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Models\FinanceTransaction;
use App\Modules\Finance\Ledger\Request\VoidFinanceTransactionRequest;
use App\Modules\Finance\Ledger\Resources\FinanceTransactionResource;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use Illuminate\Http\JsonResponse;

class FinanceTransactionVoidController extends Controller
{
    public function __construct(private readonly FinanceLedgerService $ledgerService) {}

    public function __invoke(VoidFinanceTransactionRequest $request, FinanceTransaction $transaction): JsonResponse
    {
        if ($transaction->status !== 'posted') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi posted yang dapat di-void.',
            ], 422);
        }

        $voided = $this->ledgerService->voidTransaction(
            $transaction,
            $request->user(),
            $request->validated('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil di-void',
            'data' => new FinanceTransactionResource($voided->load(['account', 'createdBy', 'voidedBy'])),
        ]);
    }
}
