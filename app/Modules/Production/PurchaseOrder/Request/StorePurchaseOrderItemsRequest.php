<?php

namespace App\Modules\Production\PurchaseOrder\Request;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StorePurchaseOrderItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => 'required|uuid|exists:purchase_order,id',
            'product_id' => 'required|uuid|exists:m_products,id',
            'unit_id' => 'required|uuid|exists:units,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'tax_amount' => ['nullable', 'numeric', 'decimal:0,2'],
            'total_price' => ['nullable', 'numeric', 'decimal:0,2'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'purchase_order_id.required' => 'Purchase order ID is required.',
            'product_id.required' => 'Product is required.',
            'unit_id.required' => 'Unit is required.',
            'quantity.required' => 'Quantity is required.',
            'unit_price.required' => 'Unit price is required.',
        ];
    }
}
