<?php

namespace App\Modules\Sales\SalesOrder\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderRequest extends FormRequest
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
            'order_number' => 'required|unique:sales_order',
            'customer_id' => 'required|exists:m_customer,id',
            'order_date' => 'nullable|date',
            'status' => 'required|in:draft,approved,dp_paid,in_production,delivered,completed,cancelled',
            'subtotal' => 'prohibited',
            'total_tax' => 'prohibited',
            'grand_total' => 'prohibited',
        ];
    }
}
