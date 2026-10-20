<?php

namespace App\Modules\Production\MaterialChecklist\Services;

use App\Modules\Production\PurchaseOrder\Reference\Models\PurchaseOrderItems;
use App\Modules\Production\WorkOrder\Models\WorkOrder;
use App\Modules\Rd\TechPack\Models\TechPack;
use Illuminate\Support\Collection;

class MaterialChecklistService
{
    public function calculate(array $filters = []): array
    {
        $workOrders = WorkOrder::query()
            ->with(['product', 'techPack.bomItems.material', 'techPack.bomItems.unit'])
            ->whereIn('status', ['draft', 'in-progress'])
            ->when($filters['work_order_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id))
            ->get();

        $fallbackTechPacks = $this->fallbackTechPacks($workOrders);
        $materialIds = $workOrders
            ->flatMap(fn (WorkOrder $workOrder) => $this->bomItems($workOrder, $fallbackTechPacks)->pluck('material_product_id'))
            ->unique()
            ->values();
        $purchasedByMaterial = $this->purchasedByMaterial($materialIds);

        return $workOrders
            ->flatMap(function (WorkOrder $workOrder) use ($fallbackTechPacks, $purchasedByMaterial) {
                return $this->bomItems($workOrder, $fallbackTechPacks)->map(function ($bomItem) use ($workOrder, $purchasedByMaterial) {
                    $required = round((float) $bomItem->usage * $workOrder->quantity, 2);
                    $purchased = round((float) $purchasedByMaterial->get($bomItem->material_product_id, 0), 2);

                    return [
                        'id' => $workOrder->id.':'.$bomItem->material_product_id,
                        'work_order' => [
                            'id' => $workOrder->id,
                            'wo_number' => $workOrder->wo_number,
                        ],
                        'product' => [
                            'id' => $workOrder->product_id,
                            'product_name' => $workOrder->product?->product_name,
                        ],
                        'material_id' => $bomItem->material_product_id,
                        'material' => $bomItem->material?->product_name,
                        'required' => $required,
                        'purchased' => $purchased,
                        'unit' => $bomItem->unit?->unit_code,
                        'status' => $this->status($required, $purchased),
                    ];
                });
            })
            ->when($filters['material_id'] ?? null, fn (Collection $items, $id) => $items->where('material_id', $id))
            ->when($filters['status'] ?? null, fn (Collection $items, $status) => $items->where('status', $status))
            ->values()
            ->all();
    }

    private function fallbackTechPacks(Collection $workOrders): Collection
    {
        $productIds = $workOrders
            ->whereNull('tech_pack_id')
            ->pluck('product_id')
            ->unique();

        return TechPack::query()
            ->with(['bomItems.material', 'bomItems.unit'])
            ->whereIn('product_id', $productIds)
            ->latest()
            ->get()
            ->groupBy('product_id')
            ->map(fn (Collection $techPacks) => $techPacks->firstWhere('status', 'approved') ?? $techPacks->first());
    }

    private function bomItems(WorkOrder $workOrder, Collection $fallbackTechPacks): Collection
    {
        return $workOrder->techPack?->bomItems
            ?? $fallbackTechPacks->get($workOrder->product_id)?->bomItems
            ?? collect();
    }

    private function purchasedByMaterial(Collection $materialIds): Collection
    {
        if ($materialIds->isEmpty()) {
            return collect();
        }

        return PurchaseOrderItems::query()
            ->with(['purchaseOrder', 'goodReceiptsItems'])
            ->whereIn('product_id', $materialIds)
            ->whereHas('purchaseOrder', fn ($query) => $query->whereIn('status', ['approved', 'received']))
            ->get()
            ->groupBy('product_id')
            ->map(function (Collection $items) {
                return $items->sum(function (PurchaseOrderItems $item) {
                    $received = (float) $item->goodReceiptsItems->sum('qty_received');

                    return $received > 0 ? $received : (float) $item->quantity;
                });
            });
    }

    private function status(float $required, float $purchased): string
    {
        if ($purchased === 0.0) {
            return 'pending';
        }

        return $purchased >= $required ? 'sufficient' : 'shortage';
    }
}
