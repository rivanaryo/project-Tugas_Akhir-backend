<?php

namespace App\Modules\Production\PurchaseOrder\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => ['sometimes', 'required', 'uuid', 'exists:purchase_order,id'],
            'product_id' => ['sometimes', 'required', 'uuid', 'exists:m_products,id'],
            'unit_id' => ['sometimes', 'required', 'uuid', 'exists:units,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'required', 'numeric', 'min:0', 'decimal:0,2'],
            'tax_rate' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'tax_amount' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
            'total_price' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
        ];
    }
}
