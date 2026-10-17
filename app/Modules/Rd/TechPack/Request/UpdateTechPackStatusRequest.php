<?php

namespace App\Modules\Rd\TechPack\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechPackStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:draft,review,approved',
        ];
    }
}
