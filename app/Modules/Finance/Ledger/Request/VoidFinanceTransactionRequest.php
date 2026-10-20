<?php

namespace App\Modules\Finance\Ledger\Request;

use Illuminate\Foundation\Http\FormRequest;

class VoidFinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
