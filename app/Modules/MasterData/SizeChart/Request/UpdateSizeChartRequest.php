<?php

namespace App\Modules\MasterData\SizeChart\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSizeChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|required|exists:m_products,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'size' => 'sometimes|required|in:S,M,L,XL,XXL',
            'chest_width' => 'nullable|numeric|min:0',
            'body_length' => 'nullable|numeric|min:0',
            'sleeve_length' => 'nullable|numeric|min:0',
            'shoulder_width' => 'nullable|numeric|min:0',
        ];
    }
}
