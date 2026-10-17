<?php

namespace App\Modules\Production\GoodReceipt\DTOs;

use App\Modules\Production\GoodReceipt\Request\StoreGoodReceiptRequest;
use App\Modules\Production\GoodReceipt\Request\UpdateGoodReceiptRequest;

class GoodReceiptDTO
{
    public function __construct(
        public readonly ?string $gr_number = null,
        public readonly ?string $purchase_order_id = null,
        public readonly ?string $vendor_id = null,
        public readonly ?string $warehouse_id = null,
        public readonly ?string $receipt_date = null,
        public readonly ?string $status = null,
        public readonly array $items = [],
    ) {
    }
    
    public static function fromRequest(StoreGoodReceiptRequest $request): self
    {
        return new self(
            $request->gr_number,
            $request->purchase_order_id,
            $request->vendor_id,
            $request->warehouse_id,
            $request->receipt_date,
            $request->status,
            $request->items ?? [],
        );
    }

    public static function fromUpdateRequest(UpdateGoodReceiptRequest $request): self
    {
        return new self(
            $request->gr_number,
            $request->purchase_order_id,
            $request->vendor_id,
            $request->warehouse_id,
            $request->receipt_date,
            $request->status,
            $request->items ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'gr_number' => $this->gr_number,
            'purchase_order_id' => $this->purchase_order_id,
            'vendor_id' => $this->vendor_id,
            'warehouse_id' => $this->warehouse_id,
            'receipt_date' => $this->receipt_date,
            'status' => $this->status,
            'items' => $this->items,
        ];
    }
    


}
