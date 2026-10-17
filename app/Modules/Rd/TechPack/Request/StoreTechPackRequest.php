<?php

namespace App\Modules\Rd\TechPack\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTechPackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'fabric' => 'nullable|string|max:255',
            'gsm' => 'nullable|integer|min:0',
            'thread' => 'nullable|string|max:255',
            'zipper' => 'nullable|string|max:255',
            'accessories' => 'nullable|array',
            'status' => ['required', Rule::in(['draft', 'review', 'approved'])],
            'product_id' => 'nullable|exists:m_products,id',
            'size_charts' => 'nullable|array',
            'size_charts.*.size' => 'required_with:size_charts|string|max:10',
            'size_charts.*.body_length' => 'nullable|numeric|min:0',
            'size_charts.*.chest_width' => 'nullable|numeric|min:0',
            'size_charts.*.sleeve_length' => 'nullable|numeric|min:0',
            'size_charts.*.shoulder_width' => 'nullable|numeric|min:0',
            'bom_items' => ['sometimes', 'array'],
            'bom_items.*.material_product_id' => [
                'required',
                'uuid',
                'exists:m_products,id',
            ],
            'bom_items.*.usage' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'bom_items.*.unit_id' => [
                'required',
                'uuid',
                'exists:units,id',
            ],
            'bom_items.*.cost_per_unit' => [
                'required',
                'numeric',
                'min:0',
            ],
            'costing' => ['sometimes', 'array'],
            'costing.printing_cost' => [
                'required_with:costing',
                'numeric',
                'min:0',
            ],
            'costing.sewing_cost' => [
                'required_with:costing',
                'numeric',
                'min:0',
            ],
            'costing.additional_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'costing.margin_type' => [
                'required_with:costing',
                Rule::in(['nominal', 'percentage']),
            ],
            'costing.margin' => [
                'required_with:costing',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('costing.margin_type') === 'percentage' && $value > 100) {
                        $fail('Margin persentase tidak boleh lebih dari 100.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tech pack wajib diisi.',
            'name.string' => 'Nama tech pack harus berupa teks.',
            'name.max' => 'Nama tech pack maksimal 255 karakter.',
            'status.required' => 'Status tech pack wajib diisi.',
            'status.in' => 'Status tech pack harus draft, review, atau approved.',
            'bom_items.array' => 'BOM items harus berupa array.',
            'bom_items.*.material_product_id.required' => 'Material wajib dipilih.',
            'bom_items.*.material_product_id.uuid' => 'Material tidak valid.',
            'bom_items.*.material_product_id.exists' => 'Material tidak ditemukan.',
            'bom_items.*.usage.required' => 'Penggunaan material wajib diisi.',
            'bom_items.*.usage.numeric' => 'Penggunaan material harus berupa angka.',
            'bom_items.*.usage.gt' => 'Penggunaan material harus lebih besar dari 0.',
            'bom_items.*.unit_id.required' => 'Satuan material wajib dipilih.',
            'bom_items.*.unit_id.uuid' => 'Satuan tidak valid.',
            'bom_items.*.unit_id.exists' => 'Satuan tidak ditemukan.',
            'bom_items.*.cost_per_unit.required' => 'Harga satuan material wajib diisi.',
            'bom_items.*.cost_per_unit.numeric' => 'Harga satuan material harus berupa angka.',
            'bom_items.*.cost_per_unit.min' => 'Harga satuan material tidak boleh negatif.',
            'costing.array' => 'Costing harus berupa array.',
            'costing.printing_cost.required_with' => 'Biaya printing wajib diisi.',
            'costing.printing_cost.numeric' => 'Biaya printing harus berupa angka.',
            'costing.printing_cost.min' => 'Biaya printing tidak boleh negatif.',
            'costing.sewing_cost.required_with' => 'Biaya sewing wajib diisi.',
            'costing.sewing_cost.numeric' => 'Biaya sewing harus berupa angka.',
            'costing.sewing_cost.min' => 'Biaya sewing tidak boleh negatif.',
            'costing.additional_cost.numeric' => 'Biaya tambahan harus berupa angka.',
            'costing.additional_cost.min' => 'Biaya tambahan tidak boleh negatif.',
            'costing.margin_type.required_with' => 'Tipe margin wajib diisi.',
            'costing.margin_type.in' => 'Tipe margin harus nominal atau percentage.',
            'costing.margin.required_with' => 'Margin wajib diisi.',
            'costing.margin.numeric' => 'Margin harus berupa angka.',
            'costing.margin.min' => 'Margin tidak boleh negatif.',
        ];
    }
}
