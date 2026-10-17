<?php

namespace App\Modules\MasterData\SizeChart\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeChartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
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
            'size' => $this->size,
            'chest_width' => $this->chest_width,
            'body_length' => $this->body_length,
            'sleeve_length' => $this->sleeve_length,
            'shoulder_width' => $this->shoulder_width,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
