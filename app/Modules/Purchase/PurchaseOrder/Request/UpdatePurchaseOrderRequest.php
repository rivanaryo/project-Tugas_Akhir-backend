<?php

namespace App\Modules\Purchase\PurchaseOrder\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
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
        $id = (string) ($this->route('purchaseOrder') ?? $this->route('id') ?? $this->id ?? '');

        return [
            'po_number' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('purchase_order', 'po_number')->ignore($id),
            ],
            'vendor_id' => ['sometimes', 'required', 'uuid', 'exists:m_vendor,id'],
            'status' => ['sometimes', 'required', 'string', 'in:pending,completed'],
            'po_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
