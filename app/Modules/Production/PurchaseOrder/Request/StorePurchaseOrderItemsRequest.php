<?php

namespace App\Modules\Production\PurchaseOrder\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StorePurchaseOrderItemsRequest extends FormRequest
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
            'purchase_order_id' => 'required|uuid|exists:purchase_order,id',
            'pricelist_id' => 'required|uuid|exists:m_pricelists,id',
            'quantity' => 'required|integer',
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'unit_price' => ['nullable', 'numeric', 'decimal:0,2'],
            'tax_amount' => ['nullable', 'numeric', 'decimal:0,2'],
            'total_price' => ['nullable', 'numeric', 'decimal:0,2'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'purchase_order_id.required' => 'Purchase order ID is required.',
            'pricelist_id.required' => 'Pricelist ID is required.',
            'quantity.required' => 'Quantity is required.',
        ];
    }
}
