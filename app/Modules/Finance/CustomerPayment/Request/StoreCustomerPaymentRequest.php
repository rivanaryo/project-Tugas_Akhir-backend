<?php

namespace App\Modules\Finance\CustomerPayment\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_number' => 'required|string|unique:customer_payments,payment_number',
            'sales_order_id' => 'required|exists:sales_order,id',
            'customer_id' => 'required|exists:m_customer,id',
            'type' => 'required|in:dp,pelunasan',
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'required|exists:m_accounts,id',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:paid,pending',
        ];
    }
}
