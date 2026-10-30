<?php

namespace App\Modules\Sales\SalesQuotation\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesQuotationRequest extends FormRequest
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
            'quotation_number' => 'required|unique:sales_quotation',
            'customer_id' => 'required|exists:m_customer,id',
            'quotation_date' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired,cancelled',
            'notes' => 'nullable|string',
            'subtotal' => 'prohibited',
            'total_tax' => 'prohibited',
            'grand_total' => 'prohibited',
        ];
    }
}
