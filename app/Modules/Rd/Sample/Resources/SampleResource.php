<?php

namespace App\Modules\Rd\Sample\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SampleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sample_number' => $this->sample_number,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'product_name' => $this->product->product_name,
            ]),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'customer_name' => $this->customer->customer_name,
            ]),
            'tech_pack_id' => $this->tech_pack_id,
            'tech_pack' => $this->whenLoaded('techPack', fn () => [
                'id' => $this->techPack->id,
                'name' => $this->techPack->name,
            ]),
            'submitted_date' => $this->submitted_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'revision_count' => $this->revision_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
