<?php

namespace App\Modules\MasterData\ManagementAccount\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:m_accounts,account_number',
            'bank_name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'account_name.required' => 'The account name is required.',
            'account_number.required' => 'The account number is required.',
            'account_number.unique' => 'Account number sudah digunakan.',
        ];
    }
}
