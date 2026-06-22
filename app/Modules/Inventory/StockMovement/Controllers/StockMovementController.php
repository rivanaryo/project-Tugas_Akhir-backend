<?php

namespace App\Modules\Inventory\StockMovement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\StockMovement\Models\StockMovement;
use App\Modules\Inventory\StockMovement\Repositories\StockMovementRepositories;
use App\Modules\Inventory\StockMovement\Request\StoreStockMovementRequest;
use App\Modules\Inventory\StockMovement\Request\UpdateStockMovementRequest;
use App\Modules\Inventory\StockMovement\DTOs\StockMovementDTO;
use App\Modules\Inventory\StockMovement\Resources\StockMovementResource;
use Illuminate\Http\JsonResponse;

class StockMovementController extends Controller
{
    protected StockMovementRepositories $repo;

    public function __construct(StockMovementRepositories $repo)
    {
        $this->repo = $repo;
    }

    public function index(): JsonResponse
    {
        $movements = $this->repo->paginate();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pergerakan stok berhasil diambil',
            'data' => $movements,
        ], 200);
    }

    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $dto = StockMovementDTO::fromRequest($request);
        $userId = auth()->id();
        $movement = $this->repo->createMovement($dto->toArray(), $userId);

        return response()->json([
            'success' => true,
            'message' => 'Pergerakan stok berhasil dibuat',
            'data' => new StockMovementResource($movement),
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $movement = StockMovement::find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Pergerakan stok tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pergerakan stok berhasil diambil',
            'data' => new StockMovementResource($movement),
        ], 200);
    }

    public function update(string $id, UpdateStockMovementRequest $request): JsonResponse
    {
        $movement = StockMovement::find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Pergerakan stok tidak ditemukan',
            ], 404);
        }

        $dto = StockMovementDTO::fromUpdateRequest($request);
        $movement->update($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pergerakan stok berhasil diperbarui',
            'data' => new StockMovementResource($movement),
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $movement = StockMovement::find($id);

        if (!$movement) {
            return response()->json([
                'success' => false,
                'message' => 'Pergerakan stok tidak ditemukan',
            ], 404);
        }

        $movement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pergerakan stok berhasil dihapus',
        ], 200);
    }
}