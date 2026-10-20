<?php

namespace App\Modules\Rd\SampleApproval\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSampleApprovalStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected,revision',
            'notes' => 'nullable|string',
        ];
    }
}
