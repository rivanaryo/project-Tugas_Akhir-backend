<?php

namespace App\Modules\Rd\TechPack\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TechPackResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'fabric' => $this->fabric,
            'gsm' => $this->gsm,
            'thread' => $this->thread,
            'zipper' => $this->zipper,
            'accessories' => $this->accessories ?? [],
            'status' => $this->status,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'product_name' => $this->product->product_name,
            ]),
            'bom_items_count' => $this->whenCounted('bomItems'),
            'size_charts' => TechPackSizeChartResource::collection($this->whenLoaded('sizeCharts')),
            'bom_items' => TechPackBomItemResource::collection($this->whenLoaded('bomItems')),
            'costing' => $this->whenLoaded('costing', fn () => new TechPackCostingResource($this->costing)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
