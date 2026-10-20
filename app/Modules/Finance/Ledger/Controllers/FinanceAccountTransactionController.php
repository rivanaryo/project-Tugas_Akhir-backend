<?php

namespace App\Modules\Finance\Ledger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Models\FinanceTransaction;
use App\Modules\Finance\Ledger\Resources\FinanceTransactionResource;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceAccountTransactionController extends Controller
{
    public function __invoke(Request $request, Account $account): JsonResponse
    {
        $query = FinanceTransaction::with(['createdBy', 'voidedBy'])
            ->where('account_id', $account->id)
            ->when($request->get('direction'), fn ($q, $direction) => $q->where('direction', $direction))
            ->when($request->get('reference_type'), fn ($q, $type) => $q->where('reference_type', $type))
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->get('date_from'), fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when($request->get('date_to'), fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->when($request->get('q'), function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('transaction_date', $request->get('sort_dir', 'desc'))
            ->orderBy('created_at', 'desc');

        $perPage = (int) $request->get('per_page', 15);
        $transactions = $query->paginate($perPage);

        $data = $transactions->through(fn ($transaction) => new FinanceTransactionResource($transaction));

        return response()->json([
            'success' => true,
            'message' => 'Mutasi akun berhasil diambil',
            'data' => $data,
        ]);
    }
}
