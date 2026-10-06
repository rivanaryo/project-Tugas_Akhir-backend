<?php

namespace App\Modules\Inventory\GoodReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreGoodReceiptRequest extends FormRequest
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
            'gr_number' => 'required|string|max:255|unique:good_receipt,gr_number',
            'purchase_order_id' => 'required|uuid|exists:purchase_order,id',
            'vendor_id' => 'required|uuid|exists:m_vendor,id',
            'receipt_date' => 'required|date_format:Y-m-d',
            'status' => 'required|string|in:draft,approved,delivered,cancelled',
            'items' => 'nullable|array',
            'items.*.purchase_order_item_id' => 'required_with:items|uuid|exists:purchase_order_items,id',
            'items.*.qty_received' => 'required_with:items|numeric|min:1',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'gr_number.required' => 'GR number is required.',
            'purchase_order_id.required' => 'Purchase order ID is required.',
            'vendor_id.required' => 'Vendor ID is required.',
            'receipt_date.required' => 'Receipt date is required.',
            'status.required' => 'Status is required.',
        ];
    }
}
