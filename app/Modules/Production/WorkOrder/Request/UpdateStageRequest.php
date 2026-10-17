<?php

namespace App\Modules\Production\WorkOrder\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage' => 'required|in:cutting,printing,sewing,qc,ready',
        ];
    }
}
