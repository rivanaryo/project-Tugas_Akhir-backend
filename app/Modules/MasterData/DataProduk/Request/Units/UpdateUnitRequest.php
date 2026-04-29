<?php

namespace App\Modules\MasterData\DataProduk\Request\Units;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'unit_code')->ignore($this->currentUnitId()),
            ],
            'unit_name' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function currentUnitId(): ?string
    {
        $unit = $this->route('unit')
            ?? $this->route('units')
            ?? $this->route('id');

        if (is_object($unit) && method_exists($unit, 'getKey')) {
            return (string) $unit->getKey();
        }

        return $unit !== null ? (string) $unit : null;
    }

    public function messages(): array
    {
        return [
            'unit_code.unique' => 'The unit code has already been taken.',
        ];
    }

}
