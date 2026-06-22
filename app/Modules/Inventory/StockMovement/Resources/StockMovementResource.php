<?php

namespace App\Modules\Inventory\StockMovement\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $documentType = $this->getDocumentType($this->referenceable_type);
        
        return [
            'id'                 => $this->id,
            'type'               => $this->type,
            'quantity'           => $this->quantity,
            'stock_before'       => $this->stock_before,
            'stock_after'        => $this->stock_after,
            'reference_number'   => $this->reference_number,
            'description'        => $this->description,
            'created_at'         => $this->created_at->isoFormat('D MMMM YYYY, HH:mm'),
            'product' => [
                'id'   => $this->product->id,
                'name' => $this->product->product_name,
                'unit' => $this->product->unit,
            ],
            'document_type'      => $documentType,
            'reference_details'  => null, 
        ];
    }

    private function getDocumentType(?string $referenceableType): string
    {
        if (!$referenceableType) {
            return 'Unknown';
        }

        $mapping = [
            'App\\Modules\\Purchase\\PurchaseOrder\\Models\\PurchaseOrder' => 'Purchase Order',
            'App\\Modules\\Sales\\SalesOrder\\Models\\SalesOrder' => 'Sales Order',
            'App\\Modules\\Purchase\\GoodReceipt\\Models\\GoodReceipt' => 'Good Receipt',
            'App\\Modules\\Sales\\SalesDelivery\\Models\\SalesDelivery' => 'Sales Delivery',
        ];

        return $mapping[$referenceableType] ?? class_basename($referenceableType);
    }
}