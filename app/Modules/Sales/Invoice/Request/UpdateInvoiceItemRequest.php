<?php

namespace App\Modules\Sales\Invoice\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
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
            'invoice_id' => 'sometimes|exists:invoices,id',
            'sales_order_item_id' => 'sometimes|exists:sales_order_items,id',
            'qty_invoice' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'tax_rate' => 'sometimes|numeric|min:0',
        ];
    }
}
