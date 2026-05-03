<?php

namespace App\Modules\MasterData\ManagementPricelist\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePricelistRequest extends FormRequest
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
            'vendor_id'  => 'sometimes|uuid|exists:m_vendor,id',
            'product_id' => 'sometimes|uuid|exists:m_products,id',
            'price'      => 'sometimes|numeric|min:0',
            'currency'   => 'sometimes|string|size:3',
            'is_active'  => 'sometimes|boolean',
        ];
    }
}
