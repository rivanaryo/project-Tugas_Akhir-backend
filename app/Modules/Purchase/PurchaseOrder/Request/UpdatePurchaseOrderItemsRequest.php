<?php

namespace App\Modules\Purchase\PurchaseOrder\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderItemsRequest extends FormRequest
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
            'purchase_order_id' => ['sometimes', 'required', 'uuid', 'exists:purchase_order,id'],
            'pricelist_id' => ['sometimes', 'required', 'uuid', 'exists:m_pricelists,id'],
            'quantity' => ['sometimes', 'required', 'integer'],
            'unit_price' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
            'total_price' => ['sometimes', 'nullable', 'numeric', 'decimal:0,2'],
        ];
    }
}
