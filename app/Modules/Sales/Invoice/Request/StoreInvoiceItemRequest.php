<?php

namespace App\Modules\Sales\Invoice\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceItemRequest extends FormRequest
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
            'sales_order_item_id' => 'required|exists:sales_order_items,id',
            'qty_invoice' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
        ];
    }
}
