<?php

namespace App\Modules\Finance\Account\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MasterData\ManagementAccount\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Account::query()
            ->when($request->get('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->get('q'), fn ($q, $search) => $q->where('account_name', 'like', "%{$search}%"));

        $perPage = (int) $request->get('per_page', 15);
        $accounts = $query->paginate($perPage);

        $data = $accounts->through(fn ($account) => [
            'id' => $account->id,
            'account_name' => $account->account_name,
            'account_number' => $account->account_number,
            'type' => $account->type,
            'bank_name' => $account->bank_name,
            'opening_balance' => (float) $account->opening_balance,
            'balance' => (float) $account->balance,
            'is_active' => $account->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar akun finance berhasil diambil',
            'data' => $data,
        ]);
    }
}
