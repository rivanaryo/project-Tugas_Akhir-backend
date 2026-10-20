<?php

namespace App\Modules\Finance\Ledger\Request;

use Illuminate\Foundation\Http\FormRequest;

class FinanceAccountSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['nullable', 'uuid', 'exists:m_accounts,id'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
