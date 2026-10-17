<?php

namespace App\Modules\Inventory\StockBalance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\StockBalance\Repositories\StockBalanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockBalanceController extends Controller
{
    public function __construct(private readonly StockBalanceRepository $repository)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['product_id', 'warehouse_id', 'search']);
        $perPage = (int) $request->get('per_page', 15);

        $balance = $this->repository->paginate($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Stock balance berhasil diambil',
            'data' => $balance,
        ], 200);
    }
}
