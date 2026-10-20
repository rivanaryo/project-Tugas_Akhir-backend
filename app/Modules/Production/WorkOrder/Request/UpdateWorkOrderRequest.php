<?php

namespace App\Modules\Production\WorkOrder\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $workOrder = $this->currentWorkOrder();

        if ($this->hasAny(['start_date', 'due_date'])) {
            $this->merge([
                'start_date' => $this->input('start_date', $workOrder?->start_date?->format('Y-m-d')),
                'due_date' => $this->input('due_date', $workOrder?->due_date?->format('Y-m-d')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'wo_number' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('work_orders', 'wo_number')->ignore($this->currentWorkOrder()?->id),
            ],
            'sales_order_id' => 'sometimes|required|uuid|exists:sales_order,id',
            'product_id' => 'sometimes|required|uuid|exists:m_products,id',
            'tech_pack_id' => 'nullable|uuid|exists:tech_packs,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'stage' => 'sometimes|required|in:cutting,printing,sewing,qc,ready',
            'status' => 'sometimes|required|in:draft,in-progress,completed,cancelled',
            'priority' => 'sometimes|required|in:low,medium,high',
            'start_date' => 'sometimes|required|date_format:Y-m-d',
            'due_date' => 'sometimes|required|date_format:Y-m-d|after_or_equal:start_date',
        ];
    }

    private function currentWorkOrder(): ?object
    {
        return $this->route('workOrder') ?? $this->route('work_order');
    }
}
