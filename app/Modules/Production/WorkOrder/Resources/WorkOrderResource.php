<?php

namespace App\Modules\Production\WorkOrder\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'wo_number' => $this->wo_number,
            'sales_order_id' => $this->sales_order_id,
            'sales_order' => $this->whenLoaded('salesOrder', fn () => [
                'id' => $this->salesOrder->id,
                'order_number' => $this->salesOrder->order_number,
            ]),
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'product_name' => $this->product->product_name,
            ]),
            'tech_pack_id' => $this->tech_pack_id,
            'tech_pack' => $this->whenLoaded('techPack', fn () => [
                'id' => $this->techPack->id,
                'name' => $this->techPack->name,
            ]),
            'quantity' => $this->quantity,
            'stage' => $this->stage,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'progress' => $this->progress,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
