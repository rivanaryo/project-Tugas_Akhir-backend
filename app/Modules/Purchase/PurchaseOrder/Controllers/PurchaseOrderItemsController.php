<?php

namespace App\Modules\Purchase\PurchaseOrder\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Purchase\PurchaseOrder\Repositories\PurchaseOrderItemsRepositories;
use App\Modules\Purchase\PurchaseOrder\Repositories\PurchaseOrderRepositories;
use App\Modules\Purchase\PurchaseOrder\Request\StorePurchaseOrderItemsRequest;
use App\Modules\Purchase\PurchaseOrder\Request\UpdatePurchaseOrderItemsRequest;
use App\Modules\Purchase\PurchaseOrder\DTOs\PurchaseOrderItemsDTO;
use App\Modules\Purchase\PurchaseOrder\Services\PurchaseOrderCalculator;
use App\Modules\MasterData\ManagementPricelist\Models\Pricelist;

class PurchaseOrderItemsController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderItemsRepositories $repo,
        private readonly PurchaseOrderRepositories $purchaseOrderRepo,
        private readonly PurchaseOrderCalculator $calculator,
    )
    {
    }

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

        $purchaseOrder = $this->purchaseOrderRepo->findById((string) $payload['purchase_order_id']);
        $pricelist = Pricelist::query()->find((string) $payload['pricelist_id']);

        if ($purchaseOrder && $pricelist && $purchaseOrder->vendor_id !== $pricelist->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor pada pricelist tidak sesuai dengan vendor pada purchase order',
                'errors' => [
                    'pricelist_id' => ['Vendor pada pricelist tidak sesuai dengan vendor pada purchase order'],
                ],
            ], 422);
        }

        if ($pricelist) {
            $payload['product_id'] = $pricelist->product_id;
            $payload['unit_price'] = (string) $pricelist->price;
        }

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

        $purchaseOrderId = (string) ($payload['purchase_order_id'] ?? $existing->purchase_order_id);
        $pricelistId = (string) ($payload['pricelist_id'] ?? $existing->pricelist_id ?? '');

        $purchaseOrder = $purchaseOrderId !== '' ? $this->purchaseOrderRepo->findById($purchaseOrderId) : null;
        $pricelist = $pricelistId !== '' ? Pricelist::query()->find($pricelistId) : null;

        if ($purchaseOrder && $pricelist && $purchaseOrder->vendor_id !== $pricelist->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor pada pricelist tidak sesuai dengan vendor pada purchase order',
                'errors' => [
                    'pricelist_id' => ['Vendor pada pricelist tidak sesuai dengan vendor pada purchase order'],
                ],
            ], 422);
        }

        if ($pricelist) {
            $payload['pricelist_id'] = $pricelist->id;
            $payload['product_id'] = $pricelist->product_id;
            $payload['unit_price'] = (string) $pricelist->price;
        }

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
