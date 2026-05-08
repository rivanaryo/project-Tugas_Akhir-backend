<?php

namespace App\Modules\Purchase\VendorPayment\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorPaymentRequest extends FormRequest
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
            'payment_number' => 'required|unique:vendor_payments,payment_number,' . $this->route('vendorPayment'),
            'vendor_id' => 'required|exists:m_vendor,id',
            'vendor_invoice_number' => 'nullable|string',
            'payment_type' => 'required|in:cash,transfer,giro,downpayment',
            'payment_date' => 'required|date',
            'total_paid' => 'required|numeric|min:0',
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'tax_amount' => 'prohibited',
            'account_id' => 'required|exists:m_accounts,id',
        ];
    }
}
