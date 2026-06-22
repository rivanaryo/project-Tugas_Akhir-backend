<?php

namespace App\Modules\Inventory\StockMovement\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
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
            'm_products_id'      => 'required|uuid|exists:m_products,id',
            'm_warehouses_id'    => 'required|uuid|exists:warehouses,id',
            'type'               => 'required|in:in,out',
            'quantity'           => 'required|integer|min:1',
            'reference_number'   => 'required|string|max:50',
            'description'        => 'nullable|string|max:255',
            'referenceable_id'   => 'required|uuid',
            'referenceable_type' => 'required|string',
        ];
    }
}
