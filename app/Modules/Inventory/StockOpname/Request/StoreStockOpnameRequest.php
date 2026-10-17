<?php

namespace App\Modules\Inventory\StockOpname\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockOpnameRequest extends FormRequest
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
            'opname_number' => 'required|string|max:50|unique:stock_opnames,opname_number',
            'warehouse_id' => 'required|uuid|exists:warehouses,id',
            'opname_date' => 'required|date',
            'status' => 'nullable|string|in:Draft,Completed,Cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:m_products,id',
            'items.*.system_qty' => 'nullable|integer|min:0',
            'items.*.physical_qty' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ];
    }
}
