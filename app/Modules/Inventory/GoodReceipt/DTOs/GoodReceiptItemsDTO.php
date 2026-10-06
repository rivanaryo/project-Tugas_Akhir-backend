<?php

namespace App\Modules\Inventory\GoodReceipt\DTOs;

use App\Modules\Inventory\GoodReceipt\Request\StoreGoodReceiptItemsRequest;
use App\Modules\Inventory\GoodReceipt\Request\UpdateGoodReceiptItemsRequest;

class GoodReceiptItemsDTO
{
    public function __construct(
        public readonly ?string $good_receipt_id = null,
        public readonly ?string $purchase_order_item_id = null,
        public readonly ?int $qty_received = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'good_receipt_id' => $this->good_receipt_id,
            'purchase_order_item_id' => $this->purchase_order_item_id,
            'qty_received' => $this->qty_received,
        ];
    }

    public static function fromRequest(StoreGoodReceiptItemsRequest $request): self
    {
        return new self(
            $request->good_receipt_id,
            $request->purchase_order_item_id,
            (int) $request->qty_received,
        );
    }

    public static function fromUpdateRequest(UpdateGoodReceiptItemsRequest $request): self
    {
        return new self(
            $request->good_receipt_id,
            $request->purchase_order_item_id,
            (int) $request->qty_received,
        );
    }

}
