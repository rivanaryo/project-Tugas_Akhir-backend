<?php

namespace App\Modules\Inventory\StockOpname\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockOpnameRequest extends FormRequest
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
            'opname_number' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('stock_opnames', 'opname_number')->ignore($this->route('stockOpname'))],
            'warehouse_id' => 'sometimes|uuid|exists:warehouses,id',
            'opname_date' => 'sometimes|date',
            'status' => 'sometimes|string|in:Draft,Completed,Cancelled',
            'notes' => 'sometimes|nullable|string',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required_with:items|uuid|exists:m_products,id',
            'items.*.system_qty' => 'nullable|integer|min:0',
            'items.*.physical_qty' => 'required_with:items|integer|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ];
    }
}
