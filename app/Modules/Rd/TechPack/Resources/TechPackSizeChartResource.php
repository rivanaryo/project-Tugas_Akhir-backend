<?php

namespace App\Modules\Rd\TechPack\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TechPackSizeChartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tech_pack_id' => $this->tech_pack_id,
            'size' => $this->size,
            'body_length' => $this->body_length,
            'chest_width' => $this->chest_width,
            'sleeve_length' => $this->sleeve_length,
            'shoulder_width' => $this->shoulder_width,
        ];
    }
}
