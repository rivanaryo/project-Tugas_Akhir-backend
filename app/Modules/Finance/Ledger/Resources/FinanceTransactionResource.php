<?php

namespace App\Modules\Finance\Ledger\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account->id,
                'account_name' => $this->account->account_name,
            ]),
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'direction' => $this->direction,
            'amount' => (float) $this->amount,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'status' => $this->status,
            'transfer_group_id' => $this->transfer_group_id,
            'reversal_of_id' => $this->reversal_of_id,
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->createdBy->id,
                'email' => $this->createdBy->email,
            ]),
            'voided_at' => $this->voided_at,
            'voided_by' => $this->whenLoaded('voidedBy', fn () => [
                'id' => $this->voidedBy->id,
                'email' => $this->voidedBy->email,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
