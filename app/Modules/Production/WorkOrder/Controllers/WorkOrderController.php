<?php

namespace App\Modules\Production\WorkOrder\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Production\WorkOrder\Request\FromSalesOrderRequest;
use App\Modules\Production\WorkOrder\Request\StoreWorkOrderRequest;
use App\Modules\Production\WorkOrder\Request\UpdateStageRequest;
use App\Modules\Production\WorkOrder\Request\UpdateWorkOrderRequest;
use App\Modules\Production\WorkOrder\Resources\WorkOrderResource;
use App\Modules\Sales\SalesOrder\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::with(['salesOrder.customer', 'product', 'techPack'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($stage = $request->get('stage')) {
            $stages = array_filter(array_map('trim', explode(',', $stage)));
            $query->whereIn('stage', $stages);
        }

        if ($salesOrderId = $request->get('sales_order_id')) {
            $query->where('sales_order_id', $salesOrderId);
        }

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('start_date', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('due_date', '<=', $to);
        }

        if ($search = $request->get('q')) {
            $query->where('wo_number', 'like', "%{$search}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        $workOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Work orders retrieved successfully',
            'data' => [
                'current_page' => $workOrders->currentPage(),
                'per_page' => $workOrders->perPage(),
                'last_page' => $workOrders->lastPage(),
                'total' => $workOrders->total(),
                'data' => WorkOrderResource::collection($workOrders->getCollection())->resolve(),
            ],
        ]);
    }

    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['progress'] = WorkOrder::resolveProgress($data['stage'], $data['status']);

        $workOrder = WorkOrder::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Work order berhasil dibuat',
            'data' => new WorkOrderResource($workOrder->load(['salesOrder.customer', 'product', 'techPack'])),
        ], 201);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail work order berhasil diambil',
            'data' => new WorkOrderResource($workOrder->load(['salesOrder.customer', 'product', 'techPack'])),
        ]);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $data = $request->validated();
        $stage = $data['stage'] ?? $workOrder->stage;
        $status = $data['status'] ?? $workOrder->status;
        $data['progress'] = WorkOrder::resolveProgress($stage, $status, $workOrder->progress);

        $workOrder->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Work order berhasil diperbarui',
            'data' => new WorkOrderResource($workOrder->load(['salesOrder.customer', 'product', 'techPack'])),
        ]);
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order berhasil dihapus',
        ]);
    }

    public function fromSalesOrder(FromSalesOrderRequest $request): JsonResponse
    {
        $salesOrder = SalesOrder::with('items.product')->findOrFail($request->validated('sales_order_id'));

        $workOrders = DB::transaction(function () use ($salesOrder) {
            $created = [];
            $startDate = $salesOrder->order_date ?? today();
            foreach ($salesOrder->items as $item) {
                $wo = WorkOrder::create([
                    'wo_number' => 'SPK-'.strtoupper(Str::random(4)).'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'stage' => 'cutting',
                    'status' => 'in-progress',
                    'start_date' => $startDate,
                    'due_date' => $startDate->copy()->addDays(7),
                    'progress' => WorkOrder::stageToProgress('cutting'),
                ]);
                $created[] = $wo;
            }

            if (! in_array($salesOrder->status, ['in_production', 'completed'], true)) {
                $salesOrder->updateStatus('in_production');
            }

            return $created;
        });

        return response()->json([
            'success' => true,
            'message' => 'Work order berhasil dibuat dari sales order',
            'data' => WorkOrderResource::collection(
                WorkOrder::with(['salesOrder.customer', 'product', 'techPack'])->whereIn('id', collect($workOrders)->pluck('id'))->get()
            ),
        ], 201);
    }

    public function updateStage(UpdateStageRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $stage = $request->validated('stage');

        $workOrder->update([
            'stage' => $stage,
            'progress' => WorkOrder::stageToProgress($stage),
            'status' => $stage === 'ready' ? 'completed' : 'in-progress',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stage work order berhasil diperbarui',
            'data' => new WorkOrderResource($workOrder->load(['salesOrder.customer', 'product', 'techPack'])),
        ]);
    }
}
