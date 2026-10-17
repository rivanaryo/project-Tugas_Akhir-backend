<?php

namespace App\Modules\Rd\Sample\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sample_number' => 'required|string|unique:samples,sample_number',
            'product_id' => 'required|exists:m_products,id',
            'customer_id' => 'required|exists:m_customer,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'submitted_date' => 'nullable|date',
            'status' => 'required|in:pending,approved,rejected,revision',
            'notes' => 'nullable|string',
        ];
    }
}
