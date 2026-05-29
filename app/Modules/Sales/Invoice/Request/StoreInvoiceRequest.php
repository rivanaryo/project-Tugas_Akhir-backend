<?php

namespace App\Modules\Sales\Invoice\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'invoice_number' => 'required|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:m_customer,id',
            'sales_order_id' => 'required|exists:sales_order,id',
            'status' => 'required|in:pending,approved,paid,cancelled',
            'subtotal' => 'prohibited',
            'tax_total' => 'prohibited',
            'grand_total' => 'prohibited',
        ];
    }
}
