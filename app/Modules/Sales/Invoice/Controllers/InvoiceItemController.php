<?php

namespace App\Modules\Sales\Invoice\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Invoice\DTOs\InvoiceItemDTO;
use App\Modules\Sales\Invoice\Repositories\InvoiceItemRepositories;
use App\Modules\Sales\Invoice\Repositories\InvoiceRepositories;
use App\Modules\Sales\Invoice\Request\StoreInvoiceItemRequest;
use App\Modules\Sales\Invoice\Request\UpdateInvoiceItemRequest;
use App\Modules\Sales\Invoice\Services\InvoiceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    public function __construct(
        private readonly InvoiceItemRepositories $repo,
        private readonly InvoiceRepositories $invoiceRepo,
        private readonly InvoiceCalculator $calculator,
    ) {}

    public function index(string $invoiceId, Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $filters = array_merge($request->all(), ['invoice_id' => $invoiceId]);
        $items = $this->repo->paginate($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar item invoice berhasil diambil',
            'data' => $items,
        ], 200);
    }

    public function store(string $invoiceId, StoreInvoiceItemRequest $request): JsonResponse
    {
        $dto = InvoiceItemDTO::fromRequest($request, $invoiceId);
        $payload = $dto->toArray();

        // Optional: Add validation logic here if needed, similar to PurchaseOrderItemsController

        $payload = $this->calculator->applyTotalPrice($payload);
        $item = $this->repo->create($payload);

        $this->calculator->updateInvoiceHeaderTotals($item->invoice_id);

        return response()->json([
            'success' => true,
            'message' => 'Item invoice berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function show(string $invoiceId, string $id): JsonResponse
    {
        $item = $this->repo->findById($id);

        if (! $item) {
            return response()->json([
                'success' => false,
                'message' => 'Item invoice tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail item invoice berhasil diambil',
            'data' => $item,
        ], 200);
    }

    public function update(string $invoiceId, string $id, UpdateInvoiceItemRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($id);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item invoice tidak ditemukan',
            ], 404);
        }

        $dto = InvoiceItemDTO::fromUpdateRequest($request, $id);
        $payload = $dto->toArray();

        $payload = $this->calculator->applyTotalPrice($payload, $existing);
        $item = $this->repo->update($id, $payload);

        $this->calculator->updateInvoiceHeaderTotals($item->invoice_id);

        return response()->json([
            'success' => true,
            'message' => 'Item invoice berhasil diperbarui',
            'data' => $item,
        ], 200);
    }

    public function destroy(string $invoiceId, string $id): JsonResponse
    {
        $existing = $this->repo->findById($id);

        if (! $existing) {
            return response()->json([
                'success' => false,
                'message' => 'Item invoice tidak ditemukan',
            ], 404);
        }

        $invoiceId = $existing->invoice_id;
        $this->repo->delete($id);

        $this->calculator->updateInvoiceHeaderTotals($invoiceId);

        return response()->json([
            'success' => true,
            'message' => 'Item invoice berhasil dihapus',
        ], 200);
    }
}
