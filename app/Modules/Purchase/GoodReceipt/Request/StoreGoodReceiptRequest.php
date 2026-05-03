<?php

namespace App\Modules\Purchase\GoodReceipt\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreGoodReceiptRequest extends FormRequest
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
            'gr_number' => 'required|string|max:255',
            'purchase_order_id' => 'required|string|max:255',
            'vendor_id' => 'required|string|max:255',
            'receipt_date' => 'required|date_format:Y-m-d',
            'status' => 'required|string|max:255',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'gr_number.required' => 'GR number is required.',
            'purchase_order_id.required' => 'Purchase order ID is required.',
            'vendor_id.required' => 'Vendor ID is required.',
            'receipt_date.required' => 'Receipt date is required.',
            'status.required' => 'Status is required.',
        ];
    }
}
