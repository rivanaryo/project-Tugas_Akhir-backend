<?php

namespace App\Modules\Purchase\GoodReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoodReceiptItemsRequest extends FormRequest
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
            'good_receipt_id' => 'required|string|max:255',
            'purchase_order_item_id' => 'required|string|max:255',
            'qty_received' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'good_receipt_id.required' => 'Good receipt ID is required.',
            'purchase_order_item_id.required' => 'Purchase order item ID is required.',
            'qty_received.required' => 'Quantity received is required.',
            'qty_received.numeric' => 'Quantity received must be a numeric.',
            'qty_received.min' => 'Quantity harus lebih dari atau sama dengan 0',
        ];
    }
}
