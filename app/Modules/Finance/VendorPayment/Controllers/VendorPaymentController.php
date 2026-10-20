<?php

namespace App\Modules\Finance\VendorPayment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Ledger\Models\FinanceTransaction;
use App\Modules\Finance\Ledger\Services\FinanceLedgerService;
use App\Modules\Finance\VendorPayment\DTOs\VendorPaymentDTO;
use App\Modules\Finance\VendorPayment\Repositories\VendorPaymentRepositories;
use App\Modules\Finance\VendorPayment\Request\StoreVendorPaymentRequest;
use App\Modules\Finance\VendorPayment\Request\UpdateVendorPaymentRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorPaymentController extends Controller
{
    public function __construct(
        private readonly VendorPaymentRepositories $repo,
        private readonly FinanceLedgerService $ledgerService,
    ) {}

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

        if ($vendorPayment->status === 'paid') {
            try {
                $this->ledgerService->postVendorPayment($vendorPayment, $request->user());
            } catch (\RuntimeException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['amount' => [$e->getMessage()]],
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran vendor berhasil dibuat',
            'data' => $vendorPayment->fresh(['accounts']),
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

        $oldStatus = $existing->status;
        $oldAccountId = $existing->account_id;
        $oldTotalPaid = $existing->total_paid;

        $dto = VendorPaymentDTO::fromUpdateRequest($request);
        $updatedVendorPayment = $this->repo->update($vendorPayment, $dto->toArray());

        $this->handleLedgerOnUpdate($existing, $updatedVendorPayment, $oldStatus, $oldAccountId, $oldTotalPaid, $request->user());

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

        DB::transaction(function () use ($existing) {
            if ($existing->status === 'paid') {
                $transaction = FinanceTransaction::where('reference_type', 'vendor_payment')
                    ->where('reference_id', $existing->id)
                    ->where('direction', 'out')
                    ->where('status', 'posted')
                    ->first();

                if ($transaction) {
                    $this->ledgerService->voidTransaction($transaction);
                }
            }

            $this->repo->delete($existing->id);
        });

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

    private function handleLedgerOnUpdate($existing, $updatedVendorPayment, string $oldStatus, string $oldAccountId, float $oldTotalPaid, ?Authenticatable $user): void
    {
        $newStatus = $updatedVendorPayment->status;
        $newAccountId = $updatedVendorPayment->account_id;
        $newTotalPaid = (float) $updatedVendorPayment->total_paid;

        $wasPaid = $oldStatus === 'paid';
        $isPaid = $newStatus === 'paid';

        if ($wasPaid && ! $isPaid) {
            $transaction = FinanceTransaction::where('reference_type', 'vendor_payment')
                ->where('reference_id', $updatedVendorPayment->id)
                ->where('direction', 'out')
                ->where('status', 'posted')
                ->first();

            if ($transaction) {
                $this->ledgerService->voidTransaction($transaction, $user);
            }

            return;
        }

        if ($isPaid) {
            $transaction = FinanceTransaction::where('reference_type', 'vendor_payment')
                ->where('reference_id', $updatedVendorPayment->id)
                ->where('direction', 'out')
                ->where('status', 'posted')
                ->first();

            if ($transaction && ($oldAccountId !== $newAccountId || $oldTotalPaid !== $newTotalPaid)) {
                $this->ledgerService->voidTransaction($transaction, $user);
            }

            if (! $transaction || ($oldAccountId !== $newAccountId || $oldTotalPaid !== $newTotalPaid)) {
                $this->ledgerService->postVendorPayment($updatedVendorPayment, $user);
            }
        }
    }
}
