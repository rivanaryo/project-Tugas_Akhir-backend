<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\FinishedGoodsReceipt\Models\FinishedGoodsReceipt;
use App\Modules\Production\FinishedGoodsReceipt\Request\StoreFinishedGoodsReceiptRequest;
use App\Modules\Production\FinishedGoodsReceipt\Request\UpdateFinishedGoodsReceiptRequest;
use App\Modules\Production\FinishedGoodsReceipt\Services\FinishedGoodsReceiptProcessor;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinishedGoodsReceiptController extends Controller
{
    public function __construct(
        private readonly FinishedGoodsReceiptProcessor $processor,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = FinishedGoodsReceipt::with(['warehouse', 'items.workOrder.salesOrderItem', 'items.product'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($workOrderId = $request->get('work_order_id')) {
            $query->whereHas('items', fn ($q) => $q->where('work_order_id', $workOrderId));
        }

        if ($search = $request->get('q')) {
            $query->where('fgr_number', 'like', "%{$search}%");
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('receipt_date', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('receipt_date', '<=', $to);
        }

        $perPage = (int) $request->get('per_page', 15);

        return response()->json([
            'success' => true,
            'message' => 'Finished goods receipts retrieved successfully',
            'data' => $query->paginate($perPage)->withQueryString(),
        ]);
    }

    public function store(StoreFinishedGoodsReceiptRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $fgr = DB::transaction(function () use ($validated): FinishedGoodsReceipt {
            $fgr = FinishedGoodsReceipt::create(collect($validated)->except('items')->toArray());

            foreach ($validated['items'] ?? [] as $item) {
                $workOrder = WorkOrder::findOrFail($item['work_order_id']);
                $fgr->items()->create([
                    'work_order_id' => $workOrder->id,
                    'product_id' => $workOrder->product_id,
                    'size' => $workOrder->salesOrderItem?->size,
                    'qty_good' => (int) $item['qty_good'],
                    'qty_reject' => (int) ($item['qty_reject'] ?? 0),
                ]);
            }

            if ($fgr->status === 'received') {
                $this->processor->process($fgr);
            }

            return $fgr;
        });

        return response()->json([
            'success' => true,
            'message' => 'Finished goods receipt berhasil dibuat',
            'data' => $fgr->load(['warehouse', 'items.workOrder', 'items.product']),
        ], 201);
    }

    public function show(FinishedGoodsReceipt $finishedGoodsReceipt): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail finished goods receipt berhasil diambil',
            'data' => $finishedGoodsReceipt->load(['warehouse', 'items.workOrder.salesOrderItem', 'items.product', 'stockMovements']),
        ]);
    }

    public function update(UpdateFinishedGoodsReceiptRequest $request, FinishedGoodsReceipt $finishedGoodsReceipt): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $finishedGoodsReceipt): void {
            $oldStatus = $finishedGoodsReceipt->status;
            $finishedGoodsReceipt->update($validated);
            $newStatus = $finishedGoodsReceipt->status;

            if ($oldStatus !== 'received' && $newStatus === 'received') {
                $this->processor->process($finishedGoodsReceipt);
            } elseif ($oldStatus === 'received' && $newStatus !== 'received') {
                $this->processor->reverse($finishedGoodsReceipt);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Finished goods receipt berhasil diperbarui',
            'data' => $finishedGoodsReceipt->fresh(['warehouse', 'items.workOrder', 'items.product']),
        ]);
    }

    public function destroy(FinishedGoodsReceipt $finishedGoodsReceipt): JsonResponse
    {
        DB::transaction(function () use ($finishedGoodsReceipt): void {
            if ($finishedGoodsReceipt->status === 'received') {
                $this->processor->reverse($finishedGoodsReceipt);
            }
            $finishedGoodsReceipt->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Finished goods receipt berhasil dihapus',
        ]);
    }
}
