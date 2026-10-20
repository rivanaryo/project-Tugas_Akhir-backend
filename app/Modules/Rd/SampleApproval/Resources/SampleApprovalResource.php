<?php

namespace App\Modules\Rd\SampleApproval\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SampleApprovalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sample_code' => $this->sample_code,
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
            'sales_order_id' => $this->sales_order_id,
            'sales_order' => $this->whenLoaded('salesOrder', fn () => $this->salesOrder ? [
                'id' => $this->salesOrder->id,
                'order_number' => $this->salesOrder->order_number,
            ] : null),
            'submitted_date' => $this->submitted_date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'revision_count' => $this->revision_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
