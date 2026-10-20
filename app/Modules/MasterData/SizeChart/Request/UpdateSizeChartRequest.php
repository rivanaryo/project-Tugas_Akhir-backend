<?php

namespace App\Modules\MasterData\SizeChart\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSizeChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('product_id') && ! $this->has('size')) {
            $this->merge(['size' => $this->route('sizeChart')?->size]);
        }
    }

    public function rules(): array
    {
        $sizeChart = $this->route('sizeChart');
        $productId = $this->input('product_id', $sizeChart?->product_id);

        return [
            'product_id' => 'sometimes|required|exists:m_products,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'size' => [
                'sometimes',
                'required',
                Rule::in(['S', 'M', 'L', 'XL', 'XXL']),
                Rule::unique('size_charts', 'size')
                    ->where('product_id', $productId)
                    ->ignore($sizeChart?->id),
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
