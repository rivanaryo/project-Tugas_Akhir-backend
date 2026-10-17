<?php

namespace App\Modules\Sales\Invoice\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Sales\Invoice\Repositories\InvoiceRepositories;
use App\Modules\Sales\Invoice\Request\StoreInvoiceRequest;
use App\Modules\Sales\Invoice\Request\UpdateInvoiceRequest;
use App\Modules\Sales\Invoice\DTOs\InvoiceDTO;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceRepositories $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $invoices = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar invoice berhasil diambil',
            'data' => $invoices,
        ], 200);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $dto = InvoiceDTO::fromRequest($request);
        $invoice = $this->repo->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dibuat',
            'data' => $invoice,
        ], 201);
    }

    public function balance(string $id): JsonResponse
    {
        $invoice = $this->repo->findById($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        $grandTotal = (float) $invoice->grand_total;
        $paid = (float) \App\Modules\Finance\CustomerPayment\Models\CustomerPayment::where('sales_order_id', $invoice->sales_order_id)
            ->where('status', 'paid')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Balance invoice berhasil diambil',
            'data' => [
                'grand_total' => $grandTotal,
                'paid' => $paid,
                'remaining' => max(0, $grandTotal - $paid),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $invoice = $this->repo->findById($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail invoice berhasil diambil',
            'data' => $invoice,
        ], 200);
    }

    public function update(string $id, UpdateInvoiceRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($id);

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        $dto = InvoiceDTO::fromUpdateRequest($request, $id);
        $invoice = $this->repo->update($id, $dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil diperbarui',
            'data' => $invoice,
        ], 200);
    }

    public function destroy(string $id): JsonResponse
    {
        $existing = $this->repo->findById($id);

        if (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        $this->repo->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dihapus',
        ], 200);
    }
}
