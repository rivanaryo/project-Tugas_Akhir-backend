<?php

namespace App\Modules\Admin\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($this->currentAdminId())],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'role' => ['sometimes', 'required', 'string', 'in:admin,user'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email admin sudah digunakan.',
            'role.in' => 'Role admin harus salah satu dari: admin atau user.',
        ];
    }

    private function currentAdminId(): int|string|null
    {
        $admin = $this->route('admin')
            ?? $this->route('admins')
            ?? $this->route('id');

        if (is_object($admin) && method_exists($admin, 'getKey')) {
            return $admin->getKey();
        }

        return $admin;
    }
}