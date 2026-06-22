<?php

namespace App\Modules\MasterData\DataProduk\Request\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'product_name' => ['sometimes', 'bail', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'bail', 'required', 'uuid', 'exists:categories,id'],
            'unit_id' => ['sometimes', 'bail', 'required', 'uuid', 'exists:units,id'],
            'warehouse_id' => ['sometimes', 'nullable', 'uuid', 'exists:warehouses,id'],
            'stock_level' => ['sometimes', 'integer', 'min:0'],
            'min_stock_level' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_name.required' => 'The product name is required.',
            'product_name.string' => 'The product name must be a string.',
            'product_name.max' => 'The product name may not be greater than 255 characters.',
            'category_id.required' => 'The category ID is required.',
            'category_id.uuid' => 'The category ID must be a valid UUID.',
            'category_id.exists' => 'The selected category ID is invalid.',
            'unit_id.required' => 'The unit ID is required.',
            'unit_id.uuid' => 'The unit ID must be a valid UUID.',
            'unit_id.exists' => 'The selected unit ID is invalid.',
        ];
    }

}
