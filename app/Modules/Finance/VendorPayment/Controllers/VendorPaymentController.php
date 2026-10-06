<?php

namespace App\Modules\Finance\VendorPayment\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Finance\VendorPayment\Request\StoreVendorPaymentRequest;
use App\Modules\Finance\VendorPayment\Request\UpdateVendorPaymentRequest;
use App\Modules\Finance\VendorPayment\DTOs\VendorPaymentDTO;
use App\Modules\Finance\VendorPayment\Repositories\VendorPaymentRepositories;

class VendorPaymentController extends Controller
{
    public function __construct(private readonly VendorPaymentRepositories $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $vendorPayments = $this->repo->paginate($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pembayaran vendor berhasil diambil',
            'data' => $vendorPayments,
        ], 200);
    }

    public function store(StoreVendorPaymentRequest $request): JsonResponse
    {
        $dto = VendorPaymentDTO::fromStoreRequest($request);
        $vendorPayment = $this->repo->create($dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran vendor berhasil dibuat',
            'data' => $vendorPayment,
        ], 201);
    }

    public function show(string $vendorPayment): JsonResponse
    {
        $vendorPayment = $this->repo->findById($vendorPayment);

        if (! $vendorPayment) {
            return $this->notFoundResponse();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail pembayaran vendor berhasil diambil',
            'data' => $vendorPayment,
        ], 200);
    }

    public function update(string $vendorPayment, UpdateVendorPaymentRequest $request): JsonResponse
    {
        $existing = $this->repo->findById($vendorPayment);

        if (! $existing) {
            return $this->notFoundResponse();
        }

        $dto = VendorPaymentDTO::fromUpdateRequest($request);
        $updatedVendorPayment = $this->repo->update($vendorPayment, $dto->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran vendor berhasil diperbarui',
            'data' => $updatedVendorPayment,
        ], 200);
    }

    public function destroy(string $vendorPayment): JsonResponse
    {
        $existing = $this->repo->findById($vendorPayment);

        if (! $existing) {
            return $this->notFoundResponse();
        }

        $this->repo->delete($vendorPayment);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran vendor berhasil dihapus',
        ], 200);
    }

    private function notFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pembayaran vendor tidak ditemukan',
        ], 404);
    }
}
