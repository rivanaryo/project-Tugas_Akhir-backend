<?php

namespace App\Modules\Production\WorkOrder\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wo_number' => 'required|string|unique:work_orders,wo_number',
            'sales_order_id' => 'required|uuid|exists:sales_order,id',
            'product_id' => 'required|uuid|exists:m_products,id',
            'tech_pack_id' => 'nullable|uuid|exists:tech_packs,id',
            'quantity' => 'required|integer|min:1',
            'stage' => 'required|in:cutting,printing,sewing,qc,ready',
            'status' => 'required|in:draft,in-progress,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high',
            'start_date' => 'required|date_format:Y-m-d',
            'due_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }
}
