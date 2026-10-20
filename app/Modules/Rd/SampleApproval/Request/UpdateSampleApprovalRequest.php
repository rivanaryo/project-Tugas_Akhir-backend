<?php

namespace App\Modules\Rd\SampleApproval\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSampleApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sampleApproval = $this->route('sampleApproval') ?? $this->route('sample_approval');

        return [
            'sample_code' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('sample_approvals', 'sample_code')->ignore($sampleApproval?->id),
            ],
            'product_id' => 'sometimes|required|uuid|exists:m_products,id',
            'customer_id' => 'sometimes|required|uuid|exists:m_customer,id',
            'sales_order_id' => 'nullable|uuid|exists:sales_order,id',
            'submitted_date' => 'sometimes|required|date_format:Y-m-d',
            'status' => 'sometimes|required|in:pending,approved,rejected,revision',
            'notes' => 'nullable|string',
            'revision_count' => 'nullable|integer|min:0',
        ];
    }
}
