<?php

namespace App\Modules\Rd\Sample\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSampleStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,approved,rejected,revision',
            'notes' => 'nullable|string',
        ];
    }
}
