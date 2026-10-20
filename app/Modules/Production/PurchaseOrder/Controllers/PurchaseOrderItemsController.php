<?php

namespace App\Modules\Production\PurchaseOrder\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\PurchaseOrder\DTOs\PurchaseOrderItemsDTO;
use App\Modules\Production\PurchaseOrder\Repositories\PurchaseOrderItemsRepositories;
use App\Modules\Production\PurchaseOrder\Repositories\PurchaseOrderRepositories;
use App\Modules\Production\PurchaseOrder\Request\StorePurchaseOrderItemsRequest;
use App\Modules\Production\PurchaseOrder\Request\UpdatePurchaseOrderItemsRequest;
use App\Modules\Production\PurchaseOrder\Services\PurchaseOrderCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderItemsController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderItemsRepositories $repo,
        private readonly PurchaseOrderRepositories $purchaseOrderRepo,
        private readonly PurchaseOrderCalculator $calculator,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $items = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar item purchase order berhasil diambil',
            'data' => $items,
        ], 200);
    }

    public function store(StorePurchaseOrderItemsRequest $request): JsonResponse
    {
        $dto = PurchaseOrderItemsDTO::fromRequest($request);
        $payload = $dto->toArray();

        $payload = $this->calculator->applyTotalPrice($payload);
        $item = $this->repo->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Item purchase order berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function show(string $purchaseOrderItem): JsonResponse
    {
        $item = $this->repo->findById($purchaseOrderItem);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Item purchase order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail item purchase order berhasil diambil',
            'data' => $item,
        ], 200);
    }

    public function update(string $purchaseOrderItem, UpdatePurchaseOrderItemsRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($purchaseOrderItem);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item purchase order tidak ditemukan',
            ], 404);
        }

        $dto = PurchaseOrderItemsDTO::fromUpdateRequest($request);
        $payload = $dto->toArray();

        $payload = $this->calculator->applyTotalPrice($payload, $existing);
        $item = $this->repo->update($purchaseOrderItem, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Item purchase order berhasil diperbarui',
            'data' => $item,
        ], 200);
    }

    public function destroy(string $purchaseOrderItem): JsonResponse
    {
        $existing = $this->repo->findById($purchaseOrderItem);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item purchase order tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($purchaseOrderItem);

        return response()->json([
            'success' => true,
            'message' => 'Item purchase order berhasil dihapus',
        ], 200);
    }
}
