<?php

namespace App\Modules\Production\WorkOrder\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wo_number' => 'sometimes|required|string|unique:work_orders,wo_number,' . $this->route('workOrder'),
            'sales_order_id' => 'sometimes|required|exists:sales_order,id',
            'product_id' => 'sometimes|required|exists:m_products,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'stage' => 'sometimes|required|in:cutting,printing,sewing,qc,ready',
            'status' => 'sometimes|required|in:draft,in-progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
