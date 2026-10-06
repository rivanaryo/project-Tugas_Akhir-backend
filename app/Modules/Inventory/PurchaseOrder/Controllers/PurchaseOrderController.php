<?php

namespace App\Modules\Inventory\PurchaseOrder\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Inventory\PurchaseOrder\Repositories\PurchaseOrderRepositories;
use App\Modules\Inventory\PurchaseOrder\Request\StorePurchaseOrderRequest;
use App\Modules\Inventory\PurchaseOrder\Request\UpdatePurchaseOrderRequest;
use App\Modules\Inventory\PurchaseOrder\DTOs\PurchaseOrderDTO;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PurchaseOrderRepositories $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $purchaseOrders = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar purchase order berhasil diambil',
            'data' => $purchaseOrders,
        ], 200);
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $dto = PurchaseOrderDTO::fromRequest($request);
        $purchaseOrder = $this->repo->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Purchase order berhasil dibuat',
            'data' => $purchaseOrder,
        ], 201);
    }

    public function show(string $purchaseOrder): JsonResponse
    {
        $purchaseOrder = $this->repo->findById($purchaseOrder);

        if (! $purchaseOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail purchase order berhasil diambil',
            'data' => $purchaseOrder,
        ], 200);
    }

    public function update(string $purchaseOrder, UpdatePurchaseOrderRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($purchaseOrder);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order tidak ditemukan',
            ], 404);
        }

        $dto = PurchaseOrderDTO::fromUpdateRequest($request);
        $purchaseOrder = $this->repo->update($purchaseOrder, $dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Purchase order berhasil diperbarui',
            'data' => $purchaseOrder,
        ], 200);
    }

    public function destroy(string $purchaseOrder): JsonResponse
    {
        $existing = $this->repo->findById($purchaseOrder);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($purchaseOrder);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order berhasil dihapus',
        ], 200);
    }
}
