<?php

namespace App\Modules\Inventory\GoodReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGoodReceiptRequest extends FormRequest
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
        $id = $this->route('goodReceipt');

        return [
            'gr_number' => 'sometimes|required|string|max:255|unique:good_receipt,gr_number,' . $id,
            'purchase_order_id' => 'sometimes|required|uuid|exists:purchase_order,id',
            'vendor_id' => 'sometimes|required|uuid|exists:m_vendor,id',
            'receipt_date' => 'sometimes|required|date_format:Y-m-d',
            'status' => 'sometimes|required|string|in:draft,approved,delivered,cancelled',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|uuid|exists:good_receipt_items,id',
            'items.*.purchase_order_item_id' => 'required_with:items|uuid|exists:purchase_order_items,id',
            'items.*.qty_received' => 'required_with:items|numeric|min:1',
        ];
    }
}
