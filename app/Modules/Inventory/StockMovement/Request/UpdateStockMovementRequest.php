<?php

namespace App\Modules\Inventory\StockMovement\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStockMovementRequest extends FormRequest
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
            'm_products_id' => 'sometimes|exists:m_products,id',
            'm_warehouses_id' => 'sometimes|exists:m_warehouses,id',
            'type' => 'sometimes|in:in,out',
            'quantity' => 'sometimes|numeric',
            'reference_number' => 'sometimes|string',
            'description' => 'sometimes|string',
            'referenceable_id' => 'sometimes|exists:models,id',
            'referenceable_type' => 'sometimes|string',
        ];
    }
}
