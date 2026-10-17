<?php

namespace App\Modules\Production\GoodReceipt\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Production\GoodReceipt\DTOs\GoodReceiptItemsDTO;
use App\Modules\Production\GoodReceipt\Repositories\GoodReceiptItemsRepositories;
use App\Modules\Production\GoodReceipt\Repositories\GoodReceiptRepositories;
use App\Modules\Production\GoodReceipt\Request\StoreGoodReceiptItemsRequest;
use App\Modules\Production\GoodReceipt\Request\UpdateGoodReceiptItemsRequest;
use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;

class GoodReceiptItemsController extends Controller
{
    public function __construct(
        private readonly GoodReceiptItemsRepositories $repo,
        private readonly GoodReceiptRepositories $goodReceiptRepo,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $items = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar item good receipt berhasil diambil',
            'data' => $items,
        ], 200);
    }

    public function show(string $goodReceiptItem): JsonResponse
    {
        $item = $this->repo->findById($goodReceiptItem);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Item good receipt tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail item good receipt berhasil diambil',
            'data' => $item,
        ], 200);
    }

    public function store(StoreGoodReceiptItemsRequest $request): JsonResponse
    {
        $dto = GoodReceiptItemsDTO::fromRequest($request);
        $payload = $dto->toArray();

        $goodReceipt = $this->goodReceiptRepo->findById((string) $payload['good_receipt_id']);

        if (! $goodReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Good receipt tidak ditemukan',
                'errors' => [
                    'good_receipt_id' => ['Good receipt tidak ditemukan'],
                ],
            ], 422);
        }

        $purchaseOrderItem = PurchaseOrderItems::query()->find((string) $payload['purchase_order_item_id']);

        if (! $purchaseOrderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order item tidak ditemukan',
                'errors' => [
                    'purchase_order_item_id' => ['Purchase order item tidak ditemukan'],
                ],
            ], 422);
        }

        if ((string) $purchaseOrderItem->purchase_order_id !== (string) $goodReceipt->purchase_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order item tidak sesuai dengan purchase order pada good receipt',
                'errors' => [
                    'purchase_order_item_id' => ['Purchase order item tidak sesuai dengan purchase order pada good receipt'],
                ],
            ], 422);
        }

        $payload['pricelist_id'] = $purchaseOrderItem->pricelist_id;
        $payload['product_id'] = $purchaseOrderItem->product_id;

        $item = $this->repo->create($payload);

        if ($goodReceipt->status === 'received') {
            $this->goodReceiptRepo->syncStockMovements($goodReceipt->load(['items', 'stockMovements']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Item good receipt berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function update(string $goodReceiptItem, UpdateGoodReceiptItemsRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($goodReceiptItem);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item good receipt tidak ditemukan',
            ], 404);
        }

        $dto = GoodReceiptItemsDTO::fromUpdateRequest($request);
        $payload = $dto->toArray();

        $goodReceipt = $this->goodReceiptRepo->findById((string) $payload['good_receipt_id']);

        if (! $goodReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Good receipt tidak ditemukan',
                'errors' => [
                    'good_receipt_id' => ['Good receipt tidak ditemukan'],
                ],
            ], 422);
        }

        $purchaseOrderItem = PurchaseOrderItems::query()->find((string) $payload['purchase_order_item_id']);

        if (! $purchaseOrderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order item tidak ditemukan',
                'errors' => [
                    'purchase_order_item_id' => ['Purchase order item tidak ditemukan'],
                ],
            ], 422);
        }

        if ((string) $purchaseOrderItem->purchase_order_id !== (string) $goodReceipt->purchase_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order item tidak sesuai dengan purchase order pada good receipt',
                'errors' => [
                    'purchase_order_item_id' => ['Purchase order item tidak sesuai dengan purchase order pada good receipt'],
                ],
            ], 422);
        }

        $payload['pricelist_id'] = $purchaseOrderItem->pricelist_id;
        $payload['product_id'] = $purchaseOrderItem->product_id;

        $item = $this->repo->update($goodReceiptItem, $payload);

        if ($goodReceipt->status === 'received') {
            $this->goodReceiptRepo->syncStockMovements($goodReceipt->load(['items', 'stockMovements']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Item good receipt berhasil diperbarui',
            'data' => $item,
        ], 200);
    }

    public function destroy(string $goodReceiptItem): JsonResponse
    {
        $existing = $this->repo->findById($goodReceiptItem);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item good receipt tidak ditemukan',
            ], 404);
        }

        $goodReceipt = $existing->goodReceipt;
        $this->repo->delete($goodReceiptItem);

        if ($goodReceipt && $goodReceipt->status === 'received') {
            $this->goodReceiptRepo->syncStockMovements($goodReceipt->load(['items', 'stockMovements']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Item good receipt berhasil dihapus',
        ], 200);
    }
}
