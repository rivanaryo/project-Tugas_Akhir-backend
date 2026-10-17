<?php

namespace App\Modules\Production\MaterialChecklist\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialChecklistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workOrderId = $request->get('work_order_id');

        $query = WorkOrder::with(['product', 'techPack.bomItems.product.unit', 'salesOrder'])
            ->when($workOrderId, fn ($q) => $q->where('id', $workOrderId));

        $perPage = (int) $request->get('per_page', 15);
        $workOrders = $query->paginate($perPage);

        $items = [];

        foreach ($workOrders->items() as $workOrder) {
            $bomItems = $workOrder->techPack?->bomItems ?? [];

            foreach ($bomItems as $bom) {
                $required = (float) $bom->usage * (int) $workOrder->quantity;

                $purchased = (float) DB::table('purchase_order_items')
                    ->join('purchase_order', 'purchase_order_items.purchase_order_id', '=', 'purchase_order.id')
                    ->where('purchase_order_items.product_id', $bom->product_id)
                    ->where('purchase_order.status', 'received')
                    ->sum('purchase_order_items.quantity');

                $items[] = [
                    'work_order_id' => $workOrder->id,
                    'wo_number' => $workOrder->wo_number,
                    'product_id' => $workOrder->product_id,
                    'product_name' => $workOrder->product?->product_name,
                    'material_id' => $bom->product_id,
                    'material_name' => $bom->product?->product_name,
                    'required' => $required,
                    'purchased' => $purchased,
                    'unit' => $bom->unit?->unit_code,
                    'status' => $purchased >= $required ? 'sufficient' : 'shortage',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Material checklist berhasil diambil',
            'data' => $items,
        ]);
    }
}
