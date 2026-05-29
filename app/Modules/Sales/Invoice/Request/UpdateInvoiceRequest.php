<?php

namespace App\Modules\Sales\Invoice\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $this->route('id'),
            'invoice_date' => 'sometimes|date',
            'customer_id' => 'sometimes|exists:m_customer,id',
            'sales_order_id' => 'sometimes|exists:sales_order,id',
            'status' => 'sometimes|in:pending,approved,paid,cancelled',
            'subtotal' => 'sometimes|numeric',
            'tax_total' => 'sometimes|numeric',
            'grand_total' => 'sometimes|numeric',
        ];
    }
}
