<?php

namespace App\Modules\Rd\SampleApproval\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreSampleApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sample_code' => 'required|string|unique:sample_approvals,sample_code',
            'product_id' => 'required|uuid|exists:m_products,id',
            'customer_id' => 'required|uuid|exists:m_customer,id',
            'sales_order_id' => 'nullable|uuid|exists:sales_order,id',
            'submitted_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:pending,approved,rejected,revision',
            'notes' => 'nullable|string',
            'revision_count' => 'nullable|integer|min:0',
        ];
    }
}
