<?php

namespace App\Modules\Sales\SalesQuotation\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quotation_number' => 'sometimes|string|unique:sales_quotation',
            'customer_id' => 'sometimes|string|exists:m_customer,id',
            'quotation_date' => 'sometimes|date',
            'valid_until' => 'sometimes|date|after_or_equal:quotation_date',
            'status' => 'sometimes|in:draft,sent,accepted,rejected,expired,cancelled',
            'notes' => 'sometimes|nullable|string',
            'subtotal' => 'prohibited',
            'total_tax' => 'prohibited',
            'grand_total' => 'prohibited',
        ];
    }
}
