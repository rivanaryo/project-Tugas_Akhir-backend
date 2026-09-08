<?php

namespace App\Modules\Inventory\StockOpname\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOpnameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'opname_number' => $this->opname_number,
            'opname_date' => $this->opname_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'email' => $this->creator->email,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->product_name,
                ] : null,
                'system_qty' => $item->system_qty,
                'physical_qty' => $item->physical_qty,
                'difference_qty' => $item->difference_qty,
                'notes' => $item->notes,
            ])),
            'created_at' => $this->created_at?->isoFormat('D MMMM YYYY, HH:mm'),
            'updated_at' => $this->updated_at?->isoFormat('D MMMM YYYY, HH:mm'),
        ];
    }
}
