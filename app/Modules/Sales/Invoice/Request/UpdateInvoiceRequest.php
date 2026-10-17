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
            'type' => 'sometimes|in:dp,pelunasan',
            'status' => 'sometimes|in:pending,approved,paid,cancelled',
            'items' => 'nullable|array',
            'items.*.sales_order_item_id' => 'required_with:items|exists:sales_order_items,id',
            'items.*.qty_invoice' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0',
            'subtotal' => 'prohibited',
            'tax_total' => 'prohibited',
            'grand_total' => 'prohibited',
        ];
    }
}
