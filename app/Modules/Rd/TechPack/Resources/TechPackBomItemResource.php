<?php

namespace App\Modules\Rd\TechPack\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TechPackBomItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'material_product_id' => $this->material_product_id,
            'material' => $this->whenLoaded('material', fn () => [
                'id' => $this->material->id,
                'product_name' => $this->material->product_name,
            ]),
            'usage' => (float) $this->usage,
            'unit_id' => $this->unit_id,
            'unit' => $this->whenLoaded('unit', fn () => [
                'id' => $this->unit->id,
                'unit_code' => $this->unit->unit_code,
                'unit_name' => $this->unit->unit_name,
            ]),
            'cost_per_unit' => (float) $this->cost_per_unit,
            'total' => (float) $this->total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
