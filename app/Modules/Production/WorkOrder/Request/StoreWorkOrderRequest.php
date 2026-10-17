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
            'sales_order_id' => 'required|exists:sales_order,id',
            'product_id' => 'required|exists:m_products,id',
            'tech_pack_id' => 'nullable|exists:tech_packs,id',
            'quantity' => 'required|integer|min:1',
            'stage' => 'required|in:cutting,printing,sewing,qc,ready',
            'status' => 'required|in:draft,in-progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
