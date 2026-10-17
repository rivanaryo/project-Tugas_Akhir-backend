<?php

namespace App\Modules\MasterData\ManagementAccount\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
            'account_number' => 'required|string|max:255|unique:m_accounts,account_number,' . $this->route('account'),
            'type' => 'required|in:bank,cash',
            'balance' => 'sometimes|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'account_name.required' => 'The account name is required.',
            'account_number.required' => 'The account number is required.',
        ];
    }
}
