<?php

namespace App\Modules\Finance\VendorPayment\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreVendorPaymentRequest extends FormRequest
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
            'payment_number' => 'required|unique:vendor_payments,payment_number',
            'vendor_id' => 'required|exists:m_vendor,id',
            'vendor_invoice_number' => 'nullable|string',
            'payment_type' => 'required|in:cash,transfer,giro,downpayment',
            'payment_date' => 'required|date',
            'total_paid' => 'required|numeric|min:0',
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'tax_amount' => 'prohibited',
            'account_id' => 'required|exists:m_accounts,id',
            'status' => 'nullable|in:paid,unpaid',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'payment_number.required' => 'Payment number is required.',
            'vendor_id.required' => 'Vendor is required.',
            'payment_type.required' => 'Payment type is required.',
            'payment_date.required' => 'Payment date is required.',
            'total_paid.required' => 'Total paid is required.',
            'account_id.required' => 'Account is required.',
        ];
    }
}
