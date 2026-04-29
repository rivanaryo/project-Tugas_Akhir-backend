<?php

namespace App\Modules\MasterData\DataProduk\Request\Units;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
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
            'unit_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_code'),
            ],
            'unit_name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_code.required' => 'The unit code is required.',
            'unit_code.string' => 'The unit code must be a string.',
            'unit_code.unique' => 'The unit code has already been taken.',
            'unit_name.required' => 'The unit name is required.',
            'unit_name.string' => 'The unit name must be a string.',
        ];
    }
}
