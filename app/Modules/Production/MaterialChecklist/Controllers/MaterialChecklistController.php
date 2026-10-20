<?php

namespace App\Modules\Production\MaterialChecklist\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\MaterialChecklist\Services\MaterialChecklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialChecklistController extends Controller
{
    public function __construct(private readonly MaterialChecklistService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'work_order_id' => 'nullable|uuid|exists:work_orders,id',
            'status' => 'nullable|in:sufficient,shortage,pending',
            'product_id' => 'nullable|uuid|exists:m_products,id',
            'material_id' => 'nullable|uuid|exists:m_products,id',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material checklist berhasil diambil',
            'data' => $this->service->calculate($filters),
        ]);
    }
}
