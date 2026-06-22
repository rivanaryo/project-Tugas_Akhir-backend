<?php

namespace App\Modules\Purchase\PurchaseOrder\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StorePurchaseOrderRequest extends FormRequest
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
            'po_number' => 'required|string|unique:purchase_order',
            'vendor_id' => 'required|uuid|exists:m_vendor,id',
            'status' => 'required|string|in:draft,approved,delivered,cancelled',
            'po_date' => 'required|date',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'po_number.required' => 'PO Number is required.',
            'vendor_id.required' => 'Vendor is required.',
            'status.required' => 'Status is required.',
            'po_date.required' => 'PO Date is required.',
        ];
    }
}
