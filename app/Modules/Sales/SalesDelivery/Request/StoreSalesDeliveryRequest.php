<?php

namespace App\Modules\Sales\SalesDelivery\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesDeliveryRequest extends FormRequest
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
            'delivery_number' => 'required|unique:sales_delivery',
            'customer_id' => 'required|exists:m_customer,id',
            'sales_order_id' => 'required|exists:sales_order,id',
            'delivery_date' => 'nullable|date',
            'status' => 'required|in:draft,approved,delivered,cancelled',
        ];
    }
}
