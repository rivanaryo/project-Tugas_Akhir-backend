<?php

namespace App\Modules\Inventory\StockOpname\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\StockOpname\DTOs\StockOpnameDTO;
use App\Modules\Inventory\StockOpname\Repositories\StockOpnameRepositories;
use App\Modules\Inventory\StockOpname\Request\StoreStockOpnameRequest;
use App\Modules\Inventory\StockOpname\Request\UpdateStockOpnameRequest;
use App\Modules\Inventory\StockOpname\Resources\StockOpnameResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function __construct(private readonly StockOpnameRepositories $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $opnames = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar stock opname berhasil diambil',
            'data' => StockOpnameResource::collection($opnames),
        ], 200);
    }

    public function store(StoreStockOpnameRequest $request): JsonResponse
    {
        $dto = StockOpnameDTO::fromRequest($request);
        $opname = $this->repo->createOpname($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Stock opname berhasil dibuat',
            'data' => new StockOpnameResource($opname->load(['warehouse', 'items.product', 'creator'])),
        ], 201);
    }

    public function show(string $stockOpname): JsonResponse
    {
        $opname = $this->repo->findById($stockOpname);

        if (! $opname) {
            return response()->json([
                'success' => false,
                'message' => 'Stock opname tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail stock opname berhasil diambil',
            'data' => new StockOpnameResource($opname),
        ], 200);
    }

    public function update(string $stockOpname, UpdateStockOpnameRequest $request): JsonResponse
    {
        $opname = $this->repo->findById($stockOpname);

        if (! $opname) {
            return response()->json([
                'success' => false,
                'message' => 'Stock opname tidak ditemukan',
            ], 404);
        }

        $dto = StockOpnameDTO::fromUpdateRequest($request);
        $updated = $this->repo->update($stockOpname, $dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Stock opname berhasil diperbarui',
            'data' => new StockOpnameResource($updated),
        ], 200);
    }

    public function destroy(string $stockOpname): JsonResponse
    {
        $opname = $this->repo->findById($stockOpname);

        if (! $opname) {
            return response()->json([
                'success' => false,
                'message' => 'Stock opname tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($stockOpname);

        return response()->json([
            'success' => true,
            'message' => 'Stock opname berhasil dihapus',
        ], 200);
    }
}
