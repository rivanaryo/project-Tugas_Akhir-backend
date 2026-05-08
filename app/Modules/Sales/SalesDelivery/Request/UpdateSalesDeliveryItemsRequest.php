<?php

namespace App\Modules\Sales\SalesDelivery\Request;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesDeliveryItemsRequest extends FormRequest
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
            'sales_delivery_id' => 'required|exists:sales_delivery,id',
            'sales_order_item_id' => 'required|exists:sales_order_items,id',
            'qty_delivered' => 'required|integer|min:1',
        ];
    }
}
