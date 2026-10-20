<?php

namespace App\Modules\Finance\Ledger\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceAccountSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this['id'],
            'account_name' => $this['account_name'],
            'account_number' => $this['account_number'],
            'type' => $this['type'],
            'bank_name' => $this['bank_name'],
            'opening_balance' => (float) $this['opening_balance'],
            'cash_in' => (float) $this['cash_in'],
            'cash_out' => (float) $this['cash_out'],
            'closing_balance' => (float) $this['closing_balance'],
            'current_balance' => (float) $this['current_balance'],
            'is_active' => $this['is_active'],
        ];
    }
}
