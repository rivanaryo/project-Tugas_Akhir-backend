<?php

namespace App\Modules\Finance\CustomerPayment\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'sales_order_id' => $this->sales_order_id,
            'sales_order' => $this->whenLoaded('salesOrder', fn () => [
                'id' => $this->salesOrder->id,
                'order_number' => $this->salesOrder->order_number,
            ]),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'customer_name' => $this->customer->customer_name,
            ]),
            'type' => $this->type,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account->id,
                'account_name' => $this->account->account_name,
            ]),
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
