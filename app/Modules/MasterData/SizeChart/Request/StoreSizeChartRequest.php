<?php

namespace App\Modules\MasterData\SizeChart\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSizeChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:m_products,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'size' => [
                'required',
                Rule::in(['S', 'M', 'L', 'XL', 'XXL']),
                Rule::unique('size_charts', 'size')->where('product_id', $this->input('product_id')),
            ],
            'chest_width' => 'nullable|numeric|min:0',
            'body_length' => 'nullable|numeric|min:0',
            'sleeve_length' => 'nullable|numeric|min:0',
            'shoulder_width' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'size.unique' => 'Size tersebut sudah terdaftar untuk produk yang dipilih.',
        ];
    }
}
