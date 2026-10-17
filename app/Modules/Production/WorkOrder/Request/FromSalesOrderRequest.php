<?php

namespace App\Modules\Production\WorkOrder\Request;

use Illuminate\Foundation\Http\FormRequest;

class FromSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'required|exists:sales_order,id',
        ];
    }
}
