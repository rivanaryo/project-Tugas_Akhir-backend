<?php

namespace App\Modules\Rd\TechPack\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TechPackCostingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tech_pack_id' => $this->tech_pack_id,
            'material_cost' => (float) $this->material_cost,
            'printing_cost' => (float) $this->printing_cost,
            'sewing_cost' => (float) $this->sewing_cost,
            'additional_cost' => (float) $this->additional_cost,
            'hpp' => (float) $this->hpp,
            'margin_type' => $this->margin_type,
            'margin' => (float) $this->margin,
            'sell_price' => (float) $this->sell_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
