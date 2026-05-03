<?php

namespace App\Modules\MasterData\ManagementPricelist\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePricelistRequest extends FormRequest
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
            'vendor_id'  => 'required|uuid|exists:m_vendor,id',
            'product_id' => 'required|uuid|exists:m_products,id',
            'price'      => 'required|numeric|min:0',
            'currency'   => 'required|string|size:3',
            'is_active'  => 'sometimes|boolean',
        ];
    }
}
