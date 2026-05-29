<?php

namespace App\Modules\Sales\SalesDelivery\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesDeliveryRequest extends FormRequest
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
            'delivery_number' => 'sometimes|unique:sales_delivery',
            'customer_id' => 'sometimes|exists:m_customer,id',
            'sales_order_id' => 'sometimes|exists:sales_order,id',
            'delivery_date' => 'nullable|date',
            'status' => 'sometimes|in:0,1,2',    
            

        ];
    }
}
