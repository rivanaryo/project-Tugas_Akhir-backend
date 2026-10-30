<?php

namespace App\Modules\Production\FinishedGoodsReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFinishedGoodsReceiptRequest extends FormRequest
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
            'fgr_number' => 'sometimes|string|max:255|unique:finished_goods_receipts,fgr_number',
            'warehouse_id' => 'sometimes|uuid|exists:warehouses,id',
            'receipt_date' => 'sometimes|date_format:Y-m-d',
            'status' => 'sometimes|in:draft,received,cancelled',
            'notes' => 'sometimes|nullable|string',
        ];
    }
}
