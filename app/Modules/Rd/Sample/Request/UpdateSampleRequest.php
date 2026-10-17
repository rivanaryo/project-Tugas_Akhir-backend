<?php

namespace App\Modules\Rd\Sample\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sample_number' => 'sometimes|required|string|unique:samples,sample_number,' . $this->route('sample'),
            'product_id' => 'sometimes|required|exists:m_products,id',
            'customer_id' => 'sometimes|required|exists:m_customer,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'submitted_date' => 'nullable|date',
            'status' => 'sometimes|required|in:pending,approved,rejected,revision',
            'notes' => 'nullable|string',
        ];
    }
}
