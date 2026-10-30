<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinishedGoodsReceiptRequest extends FormRequest
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
            'fgr_number' => 'required|string|max:255|unique:finished_goods_receipts,fgr_number',
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'receipt_date' => 'required|date_format:Y-m-d',
            'status' => 'required|in:draft,received,cancelled',
            'notes' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.work_order_id' => 'required_with:items|uuid|exists:work_orders,id',
            'items.*.qty_good' => 'required_with:items|integer|min:0',
            'items.*.qty_reject' => 'nullable|integer|min:0',
        ];
    }
}
